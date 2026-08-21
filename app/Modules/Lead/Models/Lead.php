<?php

namespace App\Modules\Lead\Models;

use App\Modules\Client\Models\Client;
use App\Modules\FollowUp\Models\FollowUp;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lead extends Model
{
    protected $table = 'leads';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'company',
        'source',
        'status',
        'priority',
        'assigned_to',
        'created_by',
        'next_follow_up_at',
        'notes',
        'converted_at',
        'converted_by',
        'status_kanban_position',
        'priority_kanban_position',
        'kanban_version',
    ];

    protected $casts = [
        'next_follow_up_at' => 'datetime',
        'converted_at' => 'datetime',

        'status_kanban_position' =>
            'decimal:6',

        'priority_kanban_position' =>
            'decimal:6',

        'kanban_version' =>
            'integer',
    ];

    /*
     * Lead Sources abhi static rahengi.
     *
     * Status aur Priority database ke
     * LeadStatus aur LeadPriority models
     * se dynamically aayengi.
     */
    public const SOURCES = [
        'website' => 'Website',
        'whatsapp' => 'WhatsApp',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'google' => 'Google',
        'referral' => 'Referral',
        'walk_in' => 'Walk-in',
        'phone_call' => 'Phone Call',
        'other' => 'Other',
    ];

    /*
    |--------------------------------------------------------------------------
    | User Relations
    |--------------------------------------------------------------------------
    */

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function convertedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'converted_by'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Lead Related Relations
    |--------------------------------------------------------------------------
    */

    public function followUps(): HasMany
    {
        return $this->hasMany(
            FollowUp::class,
            'lead_id'
        );
    }

    public function client(): HasOne
    {
        return $this->hasOne(
            Client::class,
            'lead_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic Status and Priority Relations
    |--------------------------------------------------------------------------
    */

    /**
     * Lead ke status slug ko lead_statuses
     * table ke record se connect karega.
     */
    public function statusDefinition(): BelongsTo
    {
        return $this->belongsTo(
            LeadStatus::class,
            'status',
            'slug'
        );
    }

    /**
     * Lead ke priority slug ko lead_priorities
     * table ke record se connect karega.
     */
    public function priorityDefinition(): BelongsTo
    {
        return $this->belongsTo(
            LeadPriority::class,
            'priority',
            'slug'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic Status Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Active aur inactive sabhi configured
     * status options return karega.
     *
     * Format:
     * [
     *     'new' => 'New',
     *     'qualified' => 'Qualified',
     * ]
     */
    public static function statuses(): array
    {
        return LeadStatus::options();
    }

    /**
     * Lead create/edit/status-update forms ke liye
     * active statuses return karega.
     *
     * Converted status manually selectable nahi hoga.
     */
    public static function editableStatuses(): array
    {
        return LeadStatus::editableOptions();
    }

    /**
     * Database me configured default Lead status.
     */
    public static function defaultStatus(): string
    {
        return LeadStatus::defaultSlug();
    }

    /**
     * Converted workflow status ka current slug.
     */
    public static function convertedStatusSlug(): ?string
    {
        return LeadStatus::systemSlug(
            'converted'
        );
    }

    /**
     * Lost workflow status ka current slug.
     */
    public static function lostStatusSlug(): ?string
    {
        return LeadStatus::systemSlug(
            'lost'
        );
    }

    /**
     * New workflow status ka current slug.
     */
    public static function newStatusSlug(): ?string
    {
        return LeadStatus::systemSlug(
            'new'
        );
    }

    /**
     * Qualified workflow status ka current slug.
     */
    public static function qualifiedStatusSlug(): ?string
    {
        return LeadStatus::systemSlug(
            'qualified'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dynamic Priority Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Active aur inactive sabhi configured
     * priorities return karega.
     */
    public static function priorities(): array
    {
        return LeadPriority::options();
    }

    /**
     * Create/edit/import forms ke liye sirf
     * active priorities return karega.
     */
    public static function activePriorities(): array
    {
        return LeadPriority::options(
            true
        );
    }

    /**
     * Database me configured default priority.
     */
    public static function defaultPriority(): string
    {
        return LeadPriority::defaultSlug();
    }

    /*
    |--------------------------------------------------------------------------
    | Lead Source Methods
    |--------------------------------------------------------------------------
    */

    public static function sources(): array
    {
        return self::SOURCES;
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check karega ki current Lead ka status
     * closed status hai ya nahi.
     *
     * Converted, Lost ya koi custom closed
     * status is method se detect hoga.
     */
    public function isClosed(): bool
    {
        /*
         * Relation already loaded hai to additional
         * database query nahi chalegi.
         */
        if (
            $this->relationLoaded(
                'statusDefinition'
            )
        ) {
            return (bool) $this
                ->statusDefinition
                    ?->is_closed;
        }

        return LeadStatus::query()
            ->where(
                'slug',
                $this->status
            )
            ->where(
                'is_closed',
                true
            )
            ->exists();
    }

    /**
     * Check karega ki Lead client me
     * convert ho chuki hai ya nahi.
     *
     * Converted status database ke system_key
     * ke through resolve hoga.
     */
    public function isConverted(): bool
    {
        /*
         * Status relation already loaded hai.
         */
        if (
            $this->relationLoaded(
                'statusDefinition'
            )
            && $this
                ->statusDefinition
                    ?->system_key === 'converted'
        ) {
            return true;
        }

        /*
         * Database se configured converted
         * status slug resolve karo.
         */
        $convertedStatus =
            self::convertedStatusSlug();

        if (
            $convertedStatus
            && $this->status
            === $convertedStatus
        ) {
            return true;
        }

        /*
         * Client relation already loaded hai.
         */
        if (
            $this->relationLoaded(
                'client'
            )
        ) {
            return $this->client !== null;
        }

        /*
         * Last safety check:
         * related Client database me exist karta hai.
         */
        return $this
            ->client()
            ->exists();
    }
}