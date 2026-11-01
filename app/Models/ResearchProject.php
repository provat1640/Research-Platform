<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResearchProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'title',
        'discipline',
        'description',
        'status',
        'progress',
        'last_activity_at',
        'research_question',
        'methodology',
        'expected_outcome',
        'ethics_status',
        'paper_layout',
        'paper_margin',
        'paper_size',
        'font_family',
        'line_spacing',
        'active_theme',
        'active_editor_id',
        'active_editor_section',
        'active_editor_at',
    ];

    protected function casts(): array
    {
        return [
            'progress' => 'integer',
            'last_activity_at' => 'datetime',
            'active_editor_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function activeEditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'active_editor_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')->withPivot('role')->withTimestamps();
    }

    public function documentVersions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(ProjectFeedback::class);
    }

    public function papers(): HasMany
    {
        return $this->hasMany(Paper::class);
    }

    public function citations(): HasMany
    {
        return $this->hasMany(Citation::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProjectMedia::class);
    }
}
