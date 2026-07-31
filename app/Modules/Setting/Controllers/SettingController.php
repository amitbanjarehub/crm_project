<?php

namespace App\Modules\Setting\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Setting\Models\Setting;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function index()
    {
        return view(
            'setting::index',
            [
                'settings' =>
                    Setting::allValues(),

                'timezones' =>
                    DateTimeZone::listIdentifiers(),

                'dateFormats' =>
                    $this->dateFormats(),

                'timeFormats' =>
                    $this->timeFormats(),

                'pageTitle' =>
                    'CRM Settings',
            ]
        );
    }

    public function update(
        Request $request
    ) {
        $validated = $request->validate([
            /*
             * General
             */
            'company_name' => [
                'required',
                'string',
                'max:150',
            ],

            'company_email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'company_phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'company_website' => [
                'nullable',
                'url',
                'max:255',
            ],

            'company_address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            /*
             * Regional
             */
            'timezone' => [
                'required',

                Rule::in(
                    DateTimeZone::listIdentifiers()
                ),
            ],

            'date_format' => [
                'required',

                Rule::in(
                    array_keys(
                        $this->dateFormats()
                    )
                ),
            ],

            'time_format' => [
                'required',

                Rule::in(
                    array_keys(
                        $this->timeFormats()
                    )
                ),
            ],

            'currency_code' => [
                'required',
                'string',
                'size:3',
            ],

            'currency_symbol' => [
                'required',
                'string',
                'max:10',
            ],

            /*
             * Branding
             */
            'company_logo' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
            ],

            'favicon' => [
                'nullable',
                'file',
                'mimes:png,jpg,jpeg,webp,ico',
                'max:1024',
            ],

            'primary_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'secondary_color' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],

            'show_company_logo' => [
                'nullable',
                'boolean',
            ],

            'sidebar_subtitle' => [
                'nullable',
                'string',
                'max:100',
            ],

            'footer_text' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * Login Page
             */
            'login_heading' => [
                'required',
                'string',
                'max:150',
            ],

            'login_description' => [
                'required',
                'string',
                'max:1000',
            ],

            'remove_company_logo' => [
                'nullable',
                'boolean',
            ],

            'remove_favicon' => [
                'nullable',
                'boolean',
            ],
        ]);

        $currentSettings =
            Setting::allValues();

        /*
         * Existing file paths preserve karo.
         */
        $companyLogo =
            $currentSettings->get(
                'company_logo',
                ''
            );

        $favicon =
            $currentSettings->get(
                'favicon',
                ''
            );

        /*
         * Company logo remove.
         */
        if (
            $request->boolean(
                'remove_company_logo'
            )
        ) {
            $this->deletePublicFile(
                $companyLogo
            );

            $companyLogo = '';
        }

        /*
         * New company logo upload.
         */
        if (
            $request->hasFile(
                'company_logo'
            )
        ) {
            $this->deletePublicFile(
                $companyLogo
            );

            $companyLogo =
                $request
                    ->file(
                        'company_logo'
                    )
                    ->store(
                        'settings',
                        'public'
                    );
        }

        /*
         * Favicon remove.
         */
        if (
            $request->boolean(
                'remove_favicon'
            )
        ) {
            $this->deletePublicFile(
                $favicon
            );

            $favicon = '';
        }

        /*
         * New favicon upload.
         */
        if (
            $request->hasFile(
                'favicon'
            )
        ) {
            $this->deletePublicFile(
                $favicon
            );

            $favicon =
                $request
                    ->file('favicon')
                    ->store(
                        'settings',
                        'public'
                    );
        }

        $values = [
            /*
             * General
             */
            'company_name' =>
                trim(
                    $validated[
                        'company_name'
                    ]
                ),

            'company_email' =>
                trim(
                    $validated[
                        'company_email'
                    ] ?? ''
                ),

            'company_phone' =>
                trim(
                    $validated[
                        'company_phone'
                    ] ?? ''
                ),

            'company_website' =>
                trim(
                    $validated[
                        'company_website'
                    ] ?? ''
                ),

            'company_address' =>
                trim(
                    $validated[
                        'company_address'
                    ] ?? ''
                ),

            /*
             * Regional
             */
            'timezone' =>
                $validated['timezone'],

            'date_format' =>
                $validated[
                    'date_format'
                ],

            'time_format' =>
                $validated[
                    'time_format'
                ],

            'currency_code' =>
                strtoupper(
                    $validated[
                        'currency_code'
                    ]
                ),

            'currency_symbol' =>
                trim(
                    $validated[
                        'currency_symbol'
                    ]
                ),

            /*
             * Branding
             */
            'company_logo' =>
                $companyLogo,

            'favicon' =>
                $favicon,

            'primary_color' =>
                strtoupper(
                    $validated[
                        'primary_color'
                    ]
                ),

            'secondary_color' =>
                strtoupper(
                    $validated[
                        'secondary_color'
                    ]
                ),

            'show_company_logo' =>
                $request->boolean(
                    'show_company_logo'
                )
                    ? '1'
                    : '0',

            'sidebar_subtitle' =>
                trim(
                    $validated[
                        'sidebar_subtitle'
                    ] ?? ''
                ),

            'footer_text' =>
                trim(
                    $validated[
                        'footer_text'
                    ] ?? ''
                ),

            /*
             * Login Page
             */
            'login_heading' =>
                trim(
                    $validated[
                        'login_heading'
                    ]
                ),

            'login_description' =>
                trim(
                    $validated[
                        'login_description'
                    ]
                ),
        ];

        Setting::saveValues(
            $values,
            $this->settingDefinitions()
        );

        return redirect()
            ->route('setting.index')
            ->with(
                'success',
                'CRM settings updated successfully.'
            );
    }

    private function settingDefinitions(): array
    {
        return [
            'company_name' => [
                'group' => 'general',
                'type' => 'text',
                'public' => true,
            ],

            'company_email' => [
                'group' => 'general',
                'type' => 'email',
                'public' => true,
            ],

            'company_phone' => [
                'group' => 'general',
                'type' => 'text',
                'public' => true,
            ],

            'company_website' => [
                'group' => 'general',
                'type' => 'url',
                'public' => true,
            ],

            'company_address' => [
                'group' => 'general',
                'type' => 'textarea',
                'public' => true,
            ],

            'timezone' => [
                'group' => 'regional',
                'type' => 'select',
            ],

            'date_format' => [
                'group' => 'regional',
                'type' => 'select',
            ],

            'time_format' => [
                'group' => 'regional',
                'type' => 'select',
            ],

            'currency_code' => [
                'group' => 'regional',
                'type' => 'text',
            ],

            'currency_symbol' => [
                'group' => 'regional',
                'type' => 'text',
            ],

            'company_logo' => [
                'group' => 'branding',
                'type' => 'file',
                'public' => true,
            ],

            'favicon' => [
                'group' => 'branding',
                'type' => 'file',
                'public' => true,
            ],

            'primary_color' => [
                'group' => 'branding',
                'type' => 'color',
                'public' => true,
            ],

            'secondary_color' => [
                'group' => 'branding',
                'type' => 'color',
                'public' => true,
            ],

            'show_company_logo' => [
                'group' => 'branding',
                'type' => 'boolean',
                'public' => true,
            ],

            'sidebar_subtitle' => [
                'group' => 'branding',
                'type' => 'text',
                'public' => true,
            ],

            'footer_text' => [
                'group' => 'branding',
                'type' => 'text',
                'public' => true,
            ],

            'login_heading' => [
                'group' => 'login',
                'type' => 'text',
                'public' => true,
            ],

            'login_description' => [
                'group' => 'login',
                'type' => 'textarea',
                'public' => true,
            ],
        ];
    }

    private function dateFormats(): array
    {
        return [
            'd-m-Y' =>
                'DD-MM-YYYY (30-07-2026)',

            'd/m/Y' =>
                'DD/MM/YYYY (30/07/2026)',

            'm-d-Y' =>
                'MM-DD-YYYY (07-30-2026)',

            'm/d/Y' =>
                'MM/DD/YYYY (07/30/2026)',

            'Y-m-d' =>
                'YYYY-MM-DD (2026-07-30)',

            'd M Y' =>
                '30 Jul 2026',
        ];
    }

    private function timeFormats(): array
    {
        return [
            'h:i A' =>
                '12 Hour (03:30 PM)',

            'H:i' =>
                '24 Hour (15:30)',
        ];
    }

    private function deletePublicFile(
        ?string $path
    ): void {
        if (!$path) {
            return;
        }

        Storage::disk('public')
            ->delete($path);
    }
}