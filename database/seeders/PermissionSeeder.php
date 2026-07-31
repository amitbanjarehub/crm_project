<?php

namespace Database\Seeders;

use App\Modules\Permission\Models\Permission;
use App\Modules\Role\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // Dashboard
            [
                'name' => 'View Dashboard',
                'slug' => 'dashboard.view',
                'group' => 'Dashboard',
            ],

            // Users
            [
                'name' => 'View Users',
                'slug' => 'users.view',
                'group' => 'User Management',
            ],
            [
                'name' => 'Create Users',
                'slug' => 'users.create',
                'group' => 'User Management',
            ],
            [
                'name' => 'Edit Users',
                'slug' => 'users.edit',
                'group' => 'User Management',
            ],
            [
                'name' => 'Delete Users',
                'slug' => 'users.delete',
                'group' => 'User Management',
            ],
            [
                'name' => 'Activate or Deactivate Users',
                'slug' => 'users.toggle_status',
                'group' => 'User Management',
            ],



            // Roles
            [
                'name' => 'View Roles',
                'slug' => 'roles.view',
                'group' => 'Role Management',
            ],
            [
                'name' => 'Create Roles',
                'slug' => 'roles.create',
                'group' => 'Role Management',
            ],
            [
                'name' => 'Manage Role Permissions',
                'slug' => 'roles.manage_permissions',
                'group' => 'Role Management',
            ],

            // Permissions
            [
                'name' => 'View Permissions',
                'slug' => 'permissions.view',
                'group' => 'Permission Management',
            ],

            // Leads
            [
                'name' => 'View Leads',
                'slug' => 'leads.view',
                'group' => 'Lead Management',
            ],
            [
                'name' => 'View All Leads',
                'slug' => 'leads.view_all',
                'group' => 'Lead Management',
            ],
            [
                'name' => 'Create Leads',
                'slug' => 'leads.create',
                'group' => 'Lead Management',
            ],
            [
                'name' => 'Edit Leads',
                'slug' => 'leads.edit',
                'group' => 'Lead Management',
            ],
            [
                'name' => 'Assign Leads',
                'slug' => 'leads.assign',
                'group' => 'Lead Management',
            ],
            [
                'name' => 'Delete Leads',
                'slug' => 'leads.delete',
                'group' => 'Lead Management',
            ],
            [
                'name' => 'Convert Leads To Clients',
                'slug' => 'leads.convert',
                'group' => 'Lead Management',
            ],

            [
                'name' => 'Import Leads',
                'slug' => 'leads.import',
                'group' => 'Lead Management',
            ],
            [
                'name' => 'Export Leads',
                'slug' => 'leads.export',
                'group' => 'Lead Management',
            ],

            // Follow-ups
            [
                'name' => 'View Follow-ups',
                'slug' => 'follow_ups.view',
                'group' => 'Follow-up Management',
            ],
            [
                'name' => 'View All Follow-ups',
                'slug' => 'follow_ups.view_all',
                'group' => 'Follow-up Management',
            ],
            [
                'name' => 'Create Follow-ups',
                'slug' => 'follow_ups.create',
                'group' => 'Follow-up Management',
            ],
            [
                'name' => 'Edit Follow-ups',
                'slug' => 'follow_ups.edit',
                'group' => 'Follow-up Management',
            ],
            [
                'name' => 'Delete Follow-ups',
                'slug' => 'follow_ups.delete',
                'group' => 'Follow-up Management',
            ],

            [
                'name' => 'Import Follow-ups',
                'slug' => 'follow_ups.import',
                'group' => 'Follow-up Management',
            ],
            [
                'name' => 'Export Follow-ups',
                'slug' => 'follow_ups.export',
                'group' => 'Follow-up Management',
            ],

            // Clients

            [
                'name' => 'View Clients',
                'slug' => 'clients.view',
                'group' => 'Client Management',
            ],
            [
                'name' => 'View All Clients',
                'slug' => 'clients.view_all',
                'group' => 'Client Management',
            ],
            [
                'name' => 'Create Clients',
                'slug' => 'clients.create',
                'group' => 'Client Management',
            ],
            [
                'name' => 'Edit Clients',
                'slug' => 'clients.edit',
                'group' => 'Client Management',
            ],
            [
                'name' => 'Assign Clients',
                'slug' => 'clients.assign',
                'group' => 'Client Management',
            ],
            [
                'name' => 'Delete Clients',
                'slug' => 'clients.delete',
                'group' => 'Client Management',
            ],

            [
                'name' => 'Import Clients',
                'slug' => 'clients.import',
                'group' => 'Client Management',
            ],
            [
                'name' => 'Export Clients',
                'slug' => 'clients.export',
                'group' => 'Client Management',
            ],

            // Projects
            [
                'name' => 'View Projects',
                'slug' => 'projects.view',
                'group' => 'Project Management',
            ],
            [
                'name' => 'View All Projects',
                'slug' => 'projects.view_all',
                'group' => 'Project Management',
            ],
            [
                'name' => 'Create Projects',
                'slug' => 'projects.create',
                'group' => 'Project Management',
            ],
            [
                'name' => 'Edit Projects',
                'slug' => 'projects.edit',
                'group' => 'Project Management',
            ],
            [
                'name' => 'Delete Projects',
                'slug' => 'projects.delete',
                'group' => 'Project Management',
            ],
            [
                'name' => 'Assign Project Manager',
                'slug' => 'projects.assign_manager',
                'group' => 'Project Management',
            ],
            [
                'name' => 'Manage Project Members',
                'slug' => 'projects.manage_members',
                'group' => 'Project Management',
            ],
            [
                'name' => 'Complete Projects',
                'slug' => 'projects.complete',
                'group' => 'Project Management',
            ],

            // Project Services
            [
                'name' => 'View Project Services',
                'slug' => 'project_services.view',
                'group' => 'Project Services',
            ],
            [
                'name' => 'Create Project Services',
                'slug' => 'project_services.create',
                'group' => 'Project Services',
            ],
            [
                'name' => 'Edit Project Services',
                'slug' => 'project_services.edit',
                'group' => 'Project Services',
            ],
            [
                'name' => 'Delete Project Services',
                'slug' => 'project_services.delete',
                'group' => 'Project Services',
            ],
            [
                'name' => 'Assign Project Services',
                'slug' => 'project_services.assign',
                'group' => 'Project Services',
            ],

            // Tasks
            [
                'name' => 'View Tasks',
                'slug' => 'tasks.view',
                'group' => 'Task Management',
            ],
            [
                'name' => 'View All Tasks',
                'slug' => 'tasks.view_all',
                'group' => 'Task Management',
            ],
            [
                'name' => 'Create Tasks',
                'slug' => 'tasks.create',
                'group' => 'Task Management',
            ],
            [
                'name' => 'Edit Tasks',
                'slug' => 'tasks.edit',
                'group' => 'Task Management',
            ],
            [
                'name' => 'Delete Tasks',
                'slug' => 'tasks.delete',
                'group' => 'Task Management',
            ],
            [
                'name' => 'Assign Tasks',
                'slug' => 'tasks.assign',
                'group' => 'Task Management',
            ],
            [
                'name' => 'Update Task Status',
                'slug' => 'tasks.update_status',
                'group' => 'Task Management',
            ],
            [
                'name' => 'Review Tasks',
                'slug' => 'tasks.review',
                'group' => 'Task Management',
            ],
            [
                'name' => 'Complete Tasks',
                'slug' => 'tasks.complete',
                'group' => 'Task Management',
            ],

            [
                'name' => 'Manage Task Dependencies',
                'slug' => 'tasks.manage_dependencies',
                'group' => 'Task Management',
            ],

            [
                'name' => 'Import Tasks',
                'slug' => 'tasks.import',
                'group' => 'Task Management',
            ],
            [
                'name' => 'Export Tasks',
                'slug' => 'tasks.export',
                'group' => 'Task Management',
            ],

            // Task Comments
            [
                'name' => 'Create Task Comments',
                'slug' => 'task_comments.create',
                'group' => 'Task Collaboration',
            ],
            [
                'name' => 'Edit Task Comments',
                'slug' => 'task_comments.edit',
                'group' => 'Task Collaboration',
            ],
            [
                'name' => 'Delete Task Comments',
                'slug' => 'task_comments.delete',
                'group' => 'Task Collaboration',
            ],

            // Task Attachments
            [
                'name' => 'Upload Task Attachments',
                'slug' => 'task_attachments.upload',
                'group' => 'Task Collaboration',
            ],
            [
                'name' => 'Download Task Attachments',
                'slug' => 'task_attachments.download',
                'group' => 'Task Collaboration',
            ],
            [
                'name' => 'Delete Task Attachments',
                'slug' => 'task_attachments.delete',
                'group' => 'Task Collaboration',
            ],

            // Reports and Analytics
            [
                'name' => 'View Executive Dashboard Report',
                'slug' => 'reports.executive.view',
                'group' => 'Reports and Analytics',
            ],
            [
                'name' => 'View All Executive Dashboard Data',
                'slug' => 'reports.executive.view_all',
                'group' => 'Reports and Analytics',
            ],
            [
                'name' => 'View Project Reports',
                'slug' => 'reports.projects.view',
                'group' => 'Reports and Analytics',
            ],
            [
                'name' => 'View All Project Reports',
                'slug' => 'reports.projects.view_all',
                'group' => 'Reports and Analytics',
            ],

            // Lead Reports
            [
                'name' => 'View Lead Reports',
                'slug' => 'reports.leads.view',
                'group' => 'Reports and Analytics',
            ],
            [
                'name' => 'View All Lead Report Data',
                'slug' => 'reports.leads.view_all',
                'group' => 'Reports and Analytics',
            ],

            [
                'name' => 'View Follow-up Reports',
                'slug' => 'reports.followups.view',
                'group' => 'Reports and Analytics',
            ],
            [
                'name' => 'View All Follow-up Reports',
                'slug' => 'reports.followups.view_all',
                'group' => 'Reports and Analytics',
            ],

            // Time Tracking
            [
                'name' => 'Use Time Tracking',
                'slug' => 'time_tracking.use',
                'group' => 'Time Tracking',
            ],
            [
                'name' => 'View Own Time Entries',
                'slug' => 'time_tracking.view_own',
                'group' => 'Time Tracking',
            ],
            [
                'name' => 'View Team Time Report',
                'slug' => 'time_tracking.view_team',
                'group' => 'Time Tracking',
            ],
            [
                'name' => 'View All Time Reports',
                'slug' => 'time_tracking.view_all',
                'group' => 'Time Tracking',
            ],


            // Settings
            [
                'name' => 'View Settings',
                'slug' => 'settings.view',
                'group' => 'Settings',
            ],
            [
                'name' => 'Update Settings',
                'slug' => 'settings.update',
                'group' => 'Settings',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                [
                    'slug' => $permission['slug'],
                ],
                [
                    'name' => $permission['name'],
                    'group' => $permission['group'],
                ]
            );
        }

        /*
         * Admin role ko automatically sari permissions assign hongi.
         */
        // $adminRole = Role::all()->first(
        //     fn(Role $role) => $role->isAdminRole()
        // );

        // if ($adminRole) {
        //     $adminRole->permissions()->sync(
        //         Permission::pluck('id')->all()
        //     );
        // }

        /*
         * Admin aur Super Admin jaise sabhi admin roles ko
         * automatically sari permissions assign hongi.
         */
        $allPermissionIds = Permission::pluck('id')->all();

        Role::all()
            ->filter(
                fn(Role $role) => $role->isAdminRole()
            )
            ->each(function (Role $role) use ($allPermissionIds) {
                $role->permissions()->sync($allPermissionIds);
            });
    }
}