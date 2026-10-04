<?php

namespace App\Http\Controllers;

use App\Models\ProjectMedia;
use App\Models\ResearchProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectMediaController extends Controller
{
    public function index(ResearchProject $project): JsonResponse
    {
        $media = $project->media()->with('uploadedBy:id,name')->latest()->get();

        return response()->json(['data' => $media]);
    }

    public function store(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'image_url' => ['nullable', 'string', 'max:2000'],
            'image_file' => ['nullable', 'image', 'max:10240'],
        ]);

        $filePath = null;
        $mimeType = null;
        $sizeBytes = null;

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $mimeType = $file->getClientMimeType();
            $sizeBytes = $file->getSize();
            $path = $file->store('media', 'public');
            $filePath = Storage::url($path);
        } elseif (! empty($validated['image_url'])) {
            $filePath = $validated['image_url'];
            $mimeType = 'image/jpeg';
        } else {
            return response()->json(['message' => 'Please provide an image file or an image URL.'], 422);
        }

        $figureNumber = ((int) $project->media()->max('figure_number')) + 1;

        $media = $project->media()->create([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? '',
            'file_path' => $filePath,
            'mime_type' => $mimeType,
            'size_bytes' => $sizeBytes,
            'figure_number' => $figureNumber,
            'uploaded_by_id' => $request->user()?->id,
        ])->load('uploadedBy:id,name');

        return response()->json(['data' => $media], 201);
    }

    public function destroy(ResearchProject $project, ProjectMedia $media): JsonResponse
    {
        abort_unless($media->research_project_id === $project->id, 404);

        $media->delete();

        return response()->json(['message' => 'Media removed successfully']);
    }
}
