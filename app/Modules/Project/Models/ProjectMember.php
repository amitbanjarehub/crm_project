<?php

namespace App\Modules\Project\Models;

use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    protected $table = 'project_members';

    protected $fillable = [
        'project_id',
        'user_id',
        'member_role',
        'added_by',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'added_by'
        );
    }
}