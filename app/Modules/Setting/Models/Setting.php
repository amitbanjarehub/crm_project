<?php

namespace App\Modules\Setting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'group_name',
        'setting_key',
        'setting_value',
        'type',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    private const CACHE_KEY =
        'crm.settings.all';

    /**
     * CRM ke initial/default values.
     */
    public static function defaults(): array
    {
        return [
            /*
             * General Company Information
             */
            'company_name' =>
                'PRO CRM',

            'company_email' =>
                '',

            'company_phone' =>
                '',

            'company_website' =>
                '',

            'company_address' =>
                '',

            /*
             * Regional Settings
             */
            'timezone' =>
                'Asia/Kolkata',

            'date_format' =>
                'd-m-Y',

            'time_format' =>
                'h:i A',

            'currency_code' =>
                'INR',

            'currency_symbol' =>
                '₹',

            /*
             * Branding
             */
            'company_logo' =>
                '',

            'favicon' =>
                '',

            'primary_color' =>
                '#2563EB',

            'secondary_color' =>
                '#0F172A',

            'show_company_logo' =>
                '1',

            'sidebar_subtitle' =>
                'Admin Panel',

            'footer_text' =>
                'Powered by PRO CRM',

            /*
             * Login Page
             */
            'login_heading' =>
                'Welcome Back, Admin',

            'login_description' =>
                'Login to manage your leads, clients, projects, tasks and CRM settings from one secure dashboard.',
        ];
    }

    /**
     * Database settings + default values.
     */
    public static function allValues(): Collection
    {
        /*
         * Cache me Collection object nahi,
         * sirf plain PHP array store karenge.
         */
        $cachedValues = Cache::get(
            self::CACHE_KEY
        );

        /*
         * Old/corrupted cache me Collection ya
         * __PHP_Incomplete_Class ho sakta hai.
         *
         * Array nahi mila to cache rebuild hoga.
         */
        if (!is_array($cachedValues)) {
            Cache::forget(
                self::CACHE_KEY
            );

            $storedSettings = self::query()
                ->pluck(
                    'setting_value',
                    'setting_key'
                )
                ->all();

            /*
             * Database values defaults ko override karengi.
             */
            $cachedValues = array_replace(
                self::defaults(),
                $storedSettings
            );

            Cache::forever(
                self::CACHE_KEY,
                $cachedValues
            );
        }

        /*
         * Method ka declared return type Collection hai.
         */
        return collect(
            $cachedValues
        );
    }

    /**
     * Single setting value.
     */
    public static function getValue(
        string $key,
        mixed $default = null
    ): mixed {
        return self::allValues()->get(
            $key,
            $default
        );
    }

    /**
     * Boolean setting value.
     */
    public static function getBoolean(
        string $key,
        bool $default = false
    ): bool {
        $value = self::getValue(
            $key,
            $default ? '1' : '0'
        );

        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * Multiple settings save/update karo.
     */
    public static function saveValues(
        array $values,
        array $definitions
    ): void {
        DB::transaction(
            function () use ($values, $definitions) {
                foreach (
                    $values as $key => $value
                ) {
                    $definition =
                        $definitions[$key]
                        ?? [];

                    self::query()->updateOrCreate(
                        [
                            'setting_key' =>
                                $key,
                        ],
                        [
                            'group_name' =>
                                $definition[
                                    'group'
                                ] ?? 'general',

                            'setting_value' =>
                                $value,

                            'type' =>
                                $definition[
                                    'type'
                                ] ?? 'text',

                            'is_public' =>
                                $definition[
                                    'public'
                                ] ?? false,
                        ]
                    );
                }
            }
        );

        self::clearCache();
    }

    public static function clearCache(): void
    {
        Cache::forget(
            self::CACHE_KEY
        );
    }
}