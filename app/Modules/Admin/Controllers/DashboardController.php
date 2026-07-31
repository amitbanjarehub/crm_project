<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Client\Models\Client;
use App\Modules\Lead\Models\Lead;
use App\Modules\Lead\Support\AuthorizesLeadAccess;
use App\Modules\Role\Models\Role;
use App\Modules\User\Models\User;
use App\Modules\Project\Models\Project;
use App\Modules\Task\Models\Task;

class DashboardController extends Controller
{
    use AuthorizesLeadAccess;

    public function index()
    {
        $loggedInUser = auth()->user();

        $cards = [];
        $quickActions = [];

        $taskSummary = [
            'due_today' => 0,
            'overdue' => 0,
            'in_review' => 0,
            'completed_today' => 0,
        ];

        $dashboardTasks = collect();

        /*
         * Reminder section ke default values.
         */
        $showReminderSection = false;
        $canViewAllLeads = false;

        $reminderSummary = [
            'today' => 0,
            'overdue' => 0,
            'next_seven_days' => 0,
            'high_priority' => 0,
        ];

        $dueLeads = collect();
        $highPriorityLeads = collect();

        /*
         * User management card.
         */
        if ($loggedInUser->hasPermission('users.view')) {
            $cards[] = [
                'title' => 'Total Users',
                'value' => User::count(),
                'note' => 'Registered CRM users',
                'icon' => '👥',
                'color' => 'blue',
            ];

            $quickActions[] = [
                'title' => 'Manage Users',
                'description' => 'Create and manage CRM users',
                'icon' => '👥',
                'route' => route('user.index'),
            ];
        }

        /*
         * Lead card role-based rahega.
         * Admin ko total leads, normal user ko assigned leads.
         */
        if ($loggedInUser->hasPermission('leads.view')) {
            $canViewAllLeads = $this->canViewAllLeads(
                $loggedInUser
            );

            $leadCount = $this->accessibleLeadQuery(
                $loggedInUser
            )->count();

            $cards[] = [
                'title' => 'Total Leads',
                'value' => $leadCount,
                'note' => $canViewAllLeads
                    ? 'All available business leads'
                    : 'Leads assigned to you',
                'icon' => '📌',
                'color' => 'orange',
            ];

            $quickActions[] = [
                'title' => 'Manage Leads',
                'description' => 'Track business leads',
                'icon' => '📌',
                'route' => route('lead.index'),
            ];
        }

        /*
         * Client card role-based rahega.
         */
        if ($loggedInUser->hasPermission('clients.view')) {
            $clientCount = $this->accessibleClientQuery(
                $loggedInUser
            )->count();

            $cards[] = [
                'title' => 'Total Clients',
                'value' => $clientCount,
                'note' => $this->canViewAllClients($loggedInUser)
                    ? 'All available client records'
                    : 'Clients assigned to you',
                'icon' => '🏢',
                'color' => 'purple',
            ];

            $quickActions[] = [
                'title' => 'Manage Clients',
                'description' => 'View client records',
                'icon' => '🏢',
                'route' => route('client.index'),
            ];
        }

        /*
         * Role card.
         */
        if ($loggedInUser->hasPermission('roles.view')) {
            $cards[] = [
                'title' => 'Roles',
                'value' => Role::count(),
                'note' => 'Available CRM roles',
                'icon' => '🛡️',
                'color' => 'green',
            ];
        }

        /*
         * Follow-up quick action.
         */
        if ($loggedInUser->hasPermission('follow_ups.view')) {
            $quickActions[] = [
                'title' => 'Manage Follow-ups',
                'description' => 'View follow-up history and schedules',
                'icon' => '📞',
                'route' => route('followup.index'),
            ];
        }

        /*
         * Reminder section tabhi dikhega jab user ko
         * Lead aur Follow-up dono dekhne ki permission ho.
         */
        $showReminderSection =
            $loggedInUser->hasPermission('leads.view')
            && $loggedInUser->hasPermission('follow_ups.view');

        if ($showReminderSection) {
            $now = now();

            $todayStart = $now->copy()->startOfDay();
            $todayEnd = $now->copy()->endOfDay();

            /*
             * Tomorrow se agale saat din.
             * Today count me overlap nahi hoga.
             */
            $tomorrowStart = $now
                ->copy()
                ->addDay()
                ->startOfDay();

            $nextSevenDaysEnd = $now
                ->copy()
                ->addDays(7)
                ->endOfDay();

            /*
             * Aaj ke scheduled follow-ups.
             */
            $reminderSummary['today'] = $this
                ->pendingLeadQuery($loggedInUser)
                ->whereBetween(
                    'next_follow_up_at',
                    [
                        $todayStart,
                        $todayEnd,
                    ]
                )
                ->count();

            /*
             * Aaj se pehle ke pending follow-ups.
             * Today's past-time records today count me hi rahenge.
             */
            $reminderSummary['overdue'] = $this
                ->pendingLeadQuery($loggedInUser)
                ->whereNotNull('next_follow_up_at')
                ->where(
                    'next_follow_up_at',
                    '<',
                    $todayStart
                )
                ->count();

            /*
             * Kal se next seven days.
             */
            $reminderSummary['next_seven_days'] = $this
                ->pendingLeadQuery($loggedInUser)
                ->whereBetween(
                    'next_follow_up_at',
                    [
                        $tomorrowStart,
                        $nextSevenDaysEnd,
                    ]
                )
                ->count();

            /*
             * High aur urgent active leads.
             * Follow-up schedule na ho tab bhi pending count me aayengi.
             */
            $reminderSummary['high_priority'] = $this
                ->pendingLeadQuery($loggedInUser)
                ->whereIn(
                    'priority',
                    [
                        'high',
                        'urgent',
                    ]
                )
                ->count();

            /*
             * Dashboard table:
             * Overdue + Today records.
             */
            $dueLeads = $this
                ->pendingLeadQuery($loggedInUser)
                ->with([
                    'assignedUser:id,name,email',
                ])
                ->whereNotNull('next_follow_up_at')
                ->where(
                    'next_follow_up_at',
                    '<=',
                    $todayEnd
                )
                ->orderBy('next_follow_up_at')
                ->limit(10)
                ->get();

            /*
             * Top high-priority pending leads.
             *
             * Priority:
             * 1. Urgent first
             * 2. No schedule first
             * 3. Oldest schedule first
             */
            $highPriorityLeads = $this
                ->pendingLeadQuery($loggedInUser)
                ->with([
                    'assignedUser:id,name,email',
                ])
                ->whereIn(
                    'priority',
                    [
                        'high',
                        'urgent',
                    ]
                )
                ->orderByRaw(
                    "CASE
                        WHEN priority = 'urgent' THEN 0
                        ELSE 1
                    END"
                )
                ->orderByRaw(
                    "CASE
                        WHEN next_follow_up_at IS NULL THEN 0
                        ELSE 1
                    END"
                )
                ->orderBy('next_follow_up_at')
                ->limit(8)
                ->get();
        }

        /*
         * Project Task reminders.
         *
         * Normal user ko assigned tasks dikhenge.
         * Super Admin / tasks.view_all user ko sab tasks dikhenge.
         */
        if ($loggedInUser->hasPermission('tasks.view')) {
            $taskQuery = Task::query();

            /*
             * Normal user sirf apne assigned tasks dekhega.
             */
            if (
                !$loggedInUser->isSuperAdmin()
                && !$loggedInUser->hasPermission('tasks.view_all')
            ) {
                $taskQuery->where(
                    'assigned_to',
                    $loggedInUser->id
                );
            }

            $taskTodayStart = now()->startOfDay();
            $taskTodayEnd = now()->endOfDay();

            /*
             * Aaj due hone wali open tasks.
             */
            $taskSummary['due_today'] = (clone $taskQuery)
                ->whereBetween(
                    'due_at',
                    [
                        $taskTodayStart,
                        $taskTodayEnd,
                    ]
                )
                ->whereNotIn(
                    'status',
                    [
                        'completed',
                        'cancelled',
                    ]
                )
                ->count();

            /*
             * Aaj se pehle ki pending tasks.
             *
             * Isse Due Today aur Overdue overlap nahi honge.
             */
            $taskSummary['overdue'] = (clone $taskQuery)
                ->whereNotNull('due_at')
                ->where(
                    'due_at',
                    '<',
                    $taskTodayStart
                )
                ->whereNotIn(
                    'status',
                    [
                        'completed',
                        'cancelled',
                    ]
                )
                ->count();

            /*
             * Review ke liye pending tasks.
             */
            $taskSummary['in_review'] = (clone $taskQuery)
                ->where('status', 'in_review')
                ->count();

            /*
             * Aaj completed tasks.
             */
            $taskSummary['completed_today'] = (clone $taskQuery)
                ->whereBetween(
                    'completed_at',
                    [
                        $taskTodayStart,
                        $taskTodayEnd,
                    ]
                )
                ->count();

            /*
             * Dashboard par top 10 pending tasks.
             *
             * Order:
             * 1. Overdue/nearest due task
             * 2. No deadline wali task last me
             */
            $dashboardTasks = (clone $taskQuery)
                ->with([
                    'project:id,project_code,name',
                    'projectService:id,name',
                    'assignedUser:id,name,email',
                ])
                ->whereNotIn(
                    'status',
                    [
                        'completed',
                        'cancelled',
                    ]
                )
                ->orderByRaw(
                    'CASE
                WHEN due_at IS NULL THEN 1
                ELSE 0
            END'
                )
                ->orderBy('due_at')
                ->limit(10)
                ->get();

            /*
             * Dashboard quick action.
             */
            $quickActions[] = [
                'title' => 'My Tasks',
                'description' => 'View assigned project tasks',
                'icon' => '✅',
                'route' => route('task.my'),
            ];
        }

        return view('admin::dashboard', [
            'cards' => $cards,
            'quickActions' => $quickActions,

            'showReminderSection' => $showReminderSection,
            'canViewAllLeads' => $canViewAllLeads,
            'reminderSummary' => $reminderSummary,
            'dueLeads' => $dueLeads,
            'highPriorityLeads' => $highPriorityLeads,

            'leadStatuses' => Lead::statuses(),
            'leadPriorities' => Lead::priorities(),

            'taskSummary' => $taskSummary,
            'dashboardTasks' => $dashboardTasks,

            'pageTitle' => 'Dashboard',

        ]);
    }

    /**
     * User ko accessible Lead query.
     */
    private function accessibleLeadQuery(
        User $user
    ) {
        $query = Lead::query();

        if (!$this->canViewAllLeads($user)) {
            $query->where(
                'assigned_to',
                $user->id
            );
        }

        return $query;
    }

    /**
     * Active/pending leads.
     *
     * Converted aur Lost leads reminders me nahi aayengi.
     */
    private function pendingLeadQuery(
        User $user
    ) {
        return $this
            ->accessibleLeadQuery($user)
            ->whereNotIn(
                'status',
                [
                    'converted',
                    'lost',
                ]
            );
    }

    /**
     * Client view-all check.
     */
    private function canViewAllClients(
        User $user
    ): bool {
        return $user->isSuperAdmin()
            || $user->hasPermission('clients.view_all');
    }

    /**
     * User ko accessible Client query.
     */
    private function accessibleClientQuery(
        User $user
    ) {
        $query = Client::query();

        if (!$this->canViewAllClients($user)) {
            $query->where(
                'assigned_to',
                $user->id
            );
        }

        return $query;
    }
}