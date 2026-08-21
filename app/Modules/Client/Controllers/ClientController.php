<?php

namespace App\Modules\Client\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Client\Models\Client;
use App\Modules\User\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Support\LeadReturnUrl;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $allowedPerPage = [10, 25, 50, 100];

        $perPage = (int) $request->query('per_page', 10);

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }

        $loggedInUser = $request->user();
        $canViewAll = $this->canViewAllClients($loggedInUser);

        $search = trim($request->query('search', ''));
        $status = trim($request->query('status', ''));
        $assignedTo = (int) $request->query('assigned_to', 0);

        $query = Client::query()
            ->with([
                'assignedUser:id,name,email',
                'creator:id,name,email',
                'lead:id,name,status,converted_at',
            ])
            ->latest();

        if (!$canViewAll) {
            $query->where('assigned_to', $loggedInUser->id);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhere('company', 'LIKE', '%' . $search . '%');

                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                }
            });
        }

        if (
            $status !== ''
            && array_key_exists($status, Client::statuses())
        ) {
            $query->where('status', $status);
        }

        if ($canViewAll && $assignedTo > 0) {
            $query->where('assigned_to', $assignedTo);
        }

        $clients = $query
            ->paginate($perPage)
            ->withQueryString();

        $users = $canViewAll
            ? $this->getAssignableUsers()
            : collect();

        return view('client::index', [
            'clients' => $clients,
            'users' => $users,
            'perPage' => $perPage,
            'search' => $search,
            'status' => $status,
            'assignedTo' => $assignedTo,
            'canViewAll' => $canViewAll,
            'statuses' => Client::statuses(),
            'pageTitle' => 'Client Management',
        ]);
    }

    public function create(Request $request)
    {
        $canAssign = $this->canAssignClients(
            $request->user()
        );

        return view('client::create', [
            'users' => $canAssign
                ? $this->getAssignableUsers()
                : collect(),
            'canAssign' => $canAssign,
            'statuses' => Client::statuses(),
            'pageTitle' => 'Add Client',
        ]);
    }

    public function store(Request $request)
    {
        $canAssign = $this->canAssignClients(
            $request->user()
        );

        $validated = $request->validate(
            $this->validationRules($canAssign)
        );

        Client::create([
            'lead_id' => null,
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'company' => $validated['company'] ?? null,
            'status' => $validated['status'],
            'assigned_to' => $canAssign
                ? ($validated['assigned_to'] ?? null)
                : $request->user()->id,
            'created_by' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('client.index')
            ->with('success', 'Client added successfully.');
    }

    public function show(
        Request $request,
        Client $client
    ) {
        $this->ensureCanAccessClient(
            $request->user(),
            $client
        );

        $returnUrl = LeadReturnUrl::resolve(
            $request,
            route('client.index')
        );

        $client->load([
            'assignedUser:id,name,email',
            'creator:id,name,email',
            'lead:id,name,phone,email,company,status,assigned_to,converted_at,converted_by',
            'lead.convertedBy:id,name,email',
        ]);

        return view('client::show', [
            'client' => $client,
            'statuses' => Client::statuses(),
            'returnUrl' =>
                $returnUrl,
            'pageTitle' => 'Client Details',
        ]);
    }

    public function edit(
        Request $request,
        Client $client
    ) {
        $this->ensureCanAccessClient(
            $request->user(),
            $client
        );

        $canAssign = $this->canAssignClients(
            $request->user()
        );

        return view('client::edit', [
            'client' => $client,
            'users' => $canAssign
                ? $this->getAssignableUsers($client)
                : collect(),
            'canAssign' => $canAssign,
            'statuses' => Client::statuses(),
            'pageTitle' => 'Edit Client',
        ]);
    }

    public function update(
        Request $request,
        Client $client
    ) {
        $this->ensureCanAccessClient(
            $request->user(),
            $client
        );

        $canAssign = $this->canAssignClients(
            $request->user()
        );

        $validated = $request->validate(
            $this->validationRules($canAssign)
        );

        $client->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'company' => $validated['company'] ?? null,
            'status' => $validated['status'],
            'assigned_to' => $canAssign
                ? ($validated['assigned_to'] ?? null)
                : $client->assigned_to,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('client.show', $client->id)
            ->with('success', 'Client updated successfully.');
    }

    // public function destroy(
    //     Request $request,
    //     Client $client
    // ) {
    //     $this->ensureCanAccessClient(
    //         $request->user(),
    //         $client
    //     );

    //     /*
    //      * Converted client delete hone par Lead converted
    //      * aur Client missing reh sakta hai. Isliye block.
    //      */
    //     if ($client->lead_id) {
    //         return redirect()
    //             ->route('client.index')
    //             ->with(
    //                 'error',
    //                 'Converted client cannot be deleted. Mark it inactive instead.'
    //             );
    //     }

    //     $client->delete();

    //     return redirect()
    //         ->route('client.index')
    //         ->with('success', 'Client deleted successfully.');
    // }

    public function destroy(
        Request $request,
        Client $client
    ) {
        $this->ensureCanAccessClient(
            $request->user(),
            $client
        );

        if ($client->projects()->exists()) {
            return redirect()
                ->route('client.index')
                ->with(
                    'error',
                    'This client has project records and cannot be deleted.'
                );
        }

        $wasConvertedClient = !empty($client->lead_id);

        /*
         * Converted client sirf Super Admin delete kar sakta hai.
         */
        if (
            $wasConvertedClient
            && !$request->user()->isSuperAdmin()
        ) {
            return redirect()
                ->route('client.index')
                ->with(
                    'error',
                    'Only Super Admin can delete a converted client.'
                );
        }

        DB::transaction(function () use ($client, $wasConvertedClient) {
            /*
             * Converted client delete hone se pehle
             * original Lead ko restore karo.
             */
            if ($wasConvertedClient) {
                $lead = $client->lead;

                if ($lead) {
                    $lead->update([
                        'status' => 'qualified',
                        'converted_at' => null,
                        'converted_by' => null,
                    ]);
                }
            }

            $client->delete();
        });

        return redirect()
            ->route('client.index')
            ->with(
                'success',
                $wasConvertedClient
                ? 'Converted client deleted and original lead restored successfully.'
                : 'Client deleted successfully.'
            );
    }

    private function validationRules(
        bool $canAssign
    ): array {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'email' => ['nullable', 'email', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],

            'status' => [
                'required',
                Rule::in(array_keys(Client::statuses())),
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];

        if ($canAssign) {
            $rules['assigned_to'] = [
                'nullable',
                'integer',
                'exists:users,id',
            ];
        }

        return $rules;
    }

    private function canViewAllClients(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission('clients.view_all');
    }

    private function canAssignClients(
        User $user
    ): bool {
        return $this->canViewAllClients($user)
            && (
                $user->isSuperAdmin()
                || $user->hasPermission('clients.assign')
            );
    }

    private function ensureCanAccessClient(
        User $user,
        Client $client
    ): void {
        if ($this->canViewAllClients($user)) {
            return;
        }

        if ((int) $client->assigned_to !== (int) $user->id) {
            abort(
                403,
                'You are not authorized to access this client.'
            );
        }
    }

    private function getAssignableUsers(
        ?Client $client = null
    ) {
        return User::query()
            ->where(function ($query) use ($client) {
                $query->where('is_active', true);

                if ($client && $client->assigned_to) {
                    $query->orWhere(
                        'id',
                        $client->assigned_to
                    );
                }
            })
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
                'is_active',
            ]);
    }
}