<?php

namespace App\Modules\Setting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Lead\Models\Lead;
use App\Modules\Lead\Models\LeadPriority;
use App\Modules\Lead\Models\LeadStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LeadOptionController extends Controller
{
    public function index()
    {
        return view(
            'setting::lead-options',
            [
                'leadStatuses' =>
                    LeadStatus::query()
                        ->withCount('leads')
                        ->ordered()
                        ->get(),

                'leadPriorities' =>
                    LeadPriority::query()
                        ->withCount('leads')
                        ->ordered()
                        ->get(),

                'pageTitle' =>
                    'Lead Status and Priority Settings',
            ]
        );
    }

    public function storeStatus(
        Request $request
    ) {
        $validated = $request->validate(
            $this->statusRules()
        );

        $makeDefault =
            $request->boolean(
                'is_default'
            )
            || !LeadStatus::query()
                ->where(
                    'is_default',
                    true
                )
                ->exists();

        $isClosed = $request->boolean(
            'is_closed'
        );

        if (
            $makeDefault
            && $isClosed
        ) {
            return back()
                ->withErrors([
                    'is_default' =>
                        'Closed status ko default nahi bana sakte.',
                ])
                ->withInput();
        }

        DB::transaction(
            function () use (
                $validated,
                $request,
                $makeDefault,
                $isClosed
            ) {
                if ($makeDefault) {
                    LeadStatus::query()
                        ->update([
                            'is_default' =>
                                false,
                        ]);
                }

                LeadStatus::create([
                    'name' =>
                        trim(
                            $validated['name']
                        ),

                    'slug' =>
                        strtolower(
                            trim(
                                $validated['slug']
                            )
                        ),

                    'system_key' =>
                        null,

                    'color' =>
                        strtoupper(
                            $validated['color']
                        ),

                    'is_default' =>
                        $makeDefault,

                    'is_active' =>
                        $makeDefault
                        || $request->boolean(
                            'is_active'
                        ),

                    'is_closed' =>
                        $isClosed,

                    'is_system' =>
                        false,

                    'sort_order' =>
                        $validated[
                            'sort_order'
                        ],
                ]);
            }
        );

        return redirect()
            ->route(
                'setting.lead-options.index'
            )
            ->with(
                'success',
                'Lead status added successfully.'
            );
    }

    public function updateStatus(
        Request $request,
        LeadStatus $leadStatus
    ) {
        $validated = $request->validate(
            $this->statusRules(
                $leadStatus
            )
        );

        $newSlug = $leadStatus->is_system
            ? $leadStatus->slug
            : strtolower(
                trim(
                    $validated['slug']
                )
            );

        $makeDefault =
            $leadStatus->is_default
            || $request->boolean(
                'is_default'
            );

        $isActive = $leadStatus->is_system
            ? true
            : (
                $makeDefault
                || $request->boolean(
                    'is_active'
                )
            );

        $isClosed = $leadStatus->is_system
            ? $leadStatus->is_closed
            : $request->boolean(
                'is_closed'
            );

        if (
            $makeDefault
            && $isClosed
        ) {
            return back()->withErrors([
                'is_default' =>
                    'Closed status ko default nahi bana sakte.',
            ]);
        }

        DB::transaction(
            function () use (
                $validated,
                $leadStatus,
                $newSlug,
                $makeDefault,
                $isActive,
                $isClosed
            ) {
                if ($makeDefault) {
                    LeadStatus::query()
                        ->whereKeyNot(
                            $leadStatus->id
                        )
                        ->update([
                            'is_default' =>
                                false,
                        ]);
                }

                if (
                    !$leadStatus->is_system
                    && $newSlug
                        !== $leadStatus->slug
                ) {
                    Lead::query()
                        ->where(
                            'status',
                            $leadStatus->slug
                        )
                        ->update([
                            'status' =>
                                $newSlug,
                        ]);
                }

                $leadStatus->update([
                    'name' =>
                        trim(
                            $validated['name']
                        ),

                    'slug' =>
                        $newSlug,

                    'color' =>
                        strtoupper(
                            $validated['color']
                        ),

                    'is_default' =>
                        $makeDefault,

                    'is_active' =>
                        $isActive,

                    'is_closed' =>
                        $isClosed,

                    'sort_order' =>
                        $validated[
                            'sort_order'
                        ],
                ]);
            }
        );

        return redirect()
            ->route(
                'setting.lead-options.index'
            )
            ->with(
                'success',
                'Lead status updated successfully.'
            );
    }

    public function destroyStatus(
        LeadStatus $leadStatus
    ) {
        if ($leadStatus->is_system) {
            return back()->with(
                'error',
                'Core system status delete nahi kiya ja sakta.'
            );
        }

        if ($leadStatus->is_default) {
            return back()->with(
                'error',
                'Default status delete karne se pehle kisi doosre status ko default banayein.'
            );
        }

        if ($leadStatus->leads()->exists()) {
            return back()->with(
                'error',
                'Ye status existing Leads me use ho raha hai. Pehle status deactivate ya Leads ka status change karein.'
            );
        }

        $leadStatus->delete();

        return redirect()
            ->route(
                'setting.lead-options.index'
            )
            ->with(
                'success',
                'Lead status deleted successfully.'
            );
    }

    public function storePriority(
        Request $request
    ) {
        $validated = $request->validate(
            $this->priorityRules()
        );

        $makeDefault =
            $request->boolean(
                'is_default'
            )
            || !LeadPriority::query()
                ->where(
                    'is_default',
                    true
                )
                ->exists();

        DB::transaction(
            function () use (
                $validated,
                $request,
                $makeDefault
            ) {
                if ($makeDefault) {
                    LeadPriority::query()
                        ->update([
                            'is_default' =>
                                false,
                        ]);
                }

                LeadPriority::create([
                    'name' =>
                        trim(
                            $validated['name']
                        ),

                    'slug' =>
                        strtolower(
                            trim(
                                $validated['slug']
                            )
                        ),

                    'color' =>
                        strtoupper(
                            $validated['color']
                        ),

                    'is_default' =>
                        $makeDefault,

                    'is_active' =>
                        $makeDefault
                        || $request->boolean(
                            'is_active'
                        ),

                    'is_system' =>
                        false,

                    'sort_order' =>
                        $validated[
                            'sort_order'
                        ],
                ]);
            }
        );

        return redirect()
            ->route(
                'setting.lead-options.index'
            )
            ->with(
                'success',
                'Lead priority added successfully.'
            );
    }

    public function updatePriority(
        Request $request,
        LeadPriority $leadPriority
    ) {
        $validated = $request->validate(
            $this->priorityRules(
                $leadPriority
            )
        );

        $newSlug = $leadPriority->is_system
            ? $leadPriority->slug
            : strtolower(
                trim(
                    $validated['slug']
                )
            );

        $makeDefault =
            $leadPriority->is_default
            || $request->boolean(
                'is_default'
            );

        $isActive =
            $leadPriority->is_system
            || $makeDefault
            || $request->boolean(
                'is_active'
            );

        DB::transaction(
            function () use (
                $validated,
                $leadPriority,
                $newSlug,
                $makeDefault,
                $isActive
            ) {
                if ($makeDefault) {
                    LeadPriority::query()
                        ->whereKeyNot(
                            $leadPriority->id
                        )
                        ->update([
                            'is_default' =>
                                false,
                        ]);
                }

                if (
                    !$leadPriority->is_system
                    && $newSlug
                        !== $leadPriority->slug
                ) {
                    Lead::query()
                        ->where(
                            'priority',
                            $leadPriority->slug
                        )
                        ->update([
                            'priority' =>
                                $newSlug,
                        ]);
                }

                $leadPriority->update([
                    'name' =>
                        trim(
                            $validated['name']
                        ),

                    'slug' =>
                        $newSlug,

                    'color' =>
                        strtoupper(
                            $validated['color']
                        ),

                    'is_default' =>
                        $makeDefault,

                    'is_active' =>
                        $isActive,

                    'sort_order' =>
                        $validated[
                            'sort_order'
                        ],
                ]);
            }
        );

        return redirect()
            ->route(
                'setting.lead-options.index'
            )
            ->with(
                'success',
                'Lead priority updated successfully.'
            );
    }

    public function destroyPriority(
        LeadPriority $leadPriority
    ) {
        if ($leadPriority->is_system) {
            return back()->with(
                'error',
                'Core system priority delete nahi ki ja sakti.'
            );
        }

        if ($leadPriority->is_default) {
            return back()->with(
                'error',
                'Default priority delete karne se pehle kisi doosri priority ko default banayein.'
            );
        }

        if ($leadPriority->leads()->exists()) {
            return back()->with(
                'error',
                'Ye priority existing Leads me use ho rahi hai. Pehle Leads ki priority change karein.'
            );
        }

        $leadPriority->delete();

        return redirect()
            ->route(
                'setting.lead-options.index'
            )
            ->with(
                'success',
                'Lead priority deleted successfully.'
            );
    }

    private function statusRules(
        ?LeadStatus $leadStatus = null
    ): array {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9]+(?:_[a-z0-9]+)*$/',

                Rule::unique(
                    'lead_statuses',
                    'slug'
                )->ignore(
                    $leadStatus?->id
                ),
            ],

            'color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],

            'is_default' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'is_closed' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    private function priorityRules(
        ?LeadPriority $leadPriority = null
    ): array {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'slug' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9]+(?:_[a-z0-9]+)*$/',

                Rule::unique(
                    'lead_priorities',
                    'slug'
                )->ignore(
                    $leadPriority?->id
                ),
            ],

            'color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],

            'is_default' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}