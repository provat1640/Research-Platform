<?php

namespace App\Models;

use Database\Factories\ProjectMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMedia extends Model
{
    /** @use HasFactory<ProjectMediaFactory> */
    use HasFactory;

    protected $table = 'project_media';

    protected $fillable = [
        'research_project_id',
        'uploaded_by_id',
        'title',
        'caption',
        'file_path',
        'mime_type',
        'size_bytes',
        'figure_number',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ResearchProject::class, 'research_project_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
