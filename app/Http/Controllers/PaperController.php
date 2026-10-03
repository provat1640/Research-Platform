<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use App\Models\ResearchProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaperController extends Controller
{
    public function index(ResearchProject $project): JsonResponse
    {
        return response()->json(['data' => $project->papers()->latest()->get()]);
    }

    public function store(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'abstract' => ['required', 'string', 'min:100'],
        ]);

        $duplicate = $this->findDuplicate($validated['abstract']);

        if ($duplicate !== null) {
            return response()->json([
                'status' => 'rejected',
                'message' => 'This abstract overlaps an existing institutional manuscript.',
                'conflicting_record_id' => $duplicate->id,
            ], 422);
        }

        $owner = $request->user() ?? $project->owner;
        $paper = $project->papers()->create([
            ...$validated,
            'owner_id' => $owner->id,
        ]);

        return response()->json(['data' => $paper], 201);
    }

    private function findDuplicate(string $abstract): ?Paper
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            return Paper::query()
                ->whereRaw('MATCH(title, abstract) AGAINST(? IN NATURAL LANGUAGE MODE)', [$abstract])
                ->first();
        }

        return Paper::query()->where('abstract', $abstract)->first();
    }
}
