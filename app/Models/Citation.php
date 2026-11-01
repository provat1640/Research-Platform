<?php

namespace App\Models;

use Database\Factories\CitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Citation extends Model
{
    /** @use HasFactory<CitationFactory> */
    use HasFactory;

    protected $fillable = [
        'research_project_id',
        'added_by_id',
        'key',
        'title',
        'authors',
        'year',
        'source',
        'doi',
        'url',
        'in_text_tag',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ResearchProject::class, 'research_project_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by_id');
    }
}
