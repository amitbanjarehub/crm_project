<?php

namespace App\Providers;

use App\Modules\Setting\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /*
         * Module Blade view namespaces load karo.
         */
        foreach (
            glob(
                app_path(
                    'Modules/*/views'
                )
            ) as $viewPath
        ) {
            $moduleName = strtolower(
                basename(
                    dirname($viewPath)
                )
            );

            $this->loadViewsFrom(
                $viewPath,
                $moduleName
            );
        }

        /*
         * Migration run hone se pehle bhi app
         * crash nahi honi chahiye.
         */
        $crmSettings = collect(
            Setting::defaults()
        );

        try {
            if (
                Schema::hasTable(
                    'settings'
                )
            ) {
                $crmSettings =
                    Setting::allValues();
            }
        } catch (Throwable) {
            /*
             * Database unavailable ya migration
             * pending ho to default settings use hongi.
             */
        }

        /*
         * Company name Laravel app config me apply.
         */
        config([
            'app.name' =>
                $crmSettings->get(
                    'company_name',
                    'PRO CRM'
                ),

            'app.timezone' =>
                $crmSettings->get(
                    'timezone',
                    config('app.timezone')
                ),
        ]);

        /*
         * PHP date/time functions ke liye timezone.
         */
        date_default_timezone_set(
            $crmSettings->get(
                'timezone',
                config('app.timezone')
            )
        );

        /*
         * Har Blade view me $crmSettings available.
         */
        View::share(
            'crmSettings',
            $crmSettings
        );
    }
}