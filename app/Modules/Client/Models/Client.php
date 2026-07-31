<?php

namespace App\Modules\Client\Models;

use App\Modules\Lead\Models\Lead;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Project\Models\Project;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'lead_id',
        'name',
        'phone',
        'email',
        'company',
        'status',
        'assigned_to',
        'created_by',
        'notes',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'on_hold' => 'On Hold',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(
            Project::class,
            'client_id'
        );
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function statuses(): array
    {
        return self::STATUSES;
    }
}