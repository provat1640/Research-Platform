<?php

namespace App\Http\Controllers;

use App\Models\Citation;
use App\Models\ResearchProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CitationController extends Controller
{
    public function index(ResearchProject $project): JsonResponse
    {
        $citations = $project->citations()->with('addedBy:id,name')->latest()->get();

        return response()->json(['data' => $citations]);
    }

    public function export(Request $request, ResearchProject $project): JsonResponse|Response
    {
        $citations = $project->citations()->orderBy('id')->get();

        $bibtexEntries = $citations->map(function (Citation $c): string {
            $key = $c->key ?: 'ref'.$c->id;
            $fields = [];

            if ($c->authors) {
                $fields[] = '  author = {'.addcslashes($c->authors, '{}').'}';
            }
            if ($c->title) {
                $fields[] = '  title = {'.addcslashes($c->title, '{}').'}';
            }
            if ($c->source) {
                $fields[] = '  journal = {'.addcslashes($c->source, '{}').'}';
            }
            if ($c->year) {
                $fields[] = '  year = {'.$c->year.'}';
            }
            if ($c->doi) {
                $fields[] = '  doi = {'.$c->doi.'}';
            }
            if ($c->url) {
                $fields[] = '  url = {'.$c->url.'}';
            }

            return "@article{{$key},\n".implode(",\n", $fields)."\n}";
        })->implode("\n\n");

        if ($request->query('download') || $request->query('format') === 'bibtex_file') {
            $filename = Str::slug($project->title).'-references.bib';

            return response($bibtexEntries, 200, [
                'Content-Type' => 'application/x-bibtex; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        return response()->json([
            'count' => $citations->count(),
            'bibtex' => $bibtexEntries,
            'citations' => $citations,
        ]);
    }

    public function store(Request $request, ResearchProject $project): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'authors' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:20'],
            'source' => ['nullable', 'string', 'max:255'],
            'doi' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500'],
            'key' => ['nullable', 'string', 'max:50'],
        ]);

        $count = $project->citations()->count() + 1;
        $authorFirst = explode(',', $validated['authors'])[0] ?? 'Source';
        $authorLastName = trim(explode(' ', trim($authorFirst))[0] ?? 'Ref');
        $year = $validated['year'] ?: date('Y');

        $key = ! empty($validated['key'])
            ? Str::slug($validated['key'])
            : strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $authorLastName).$year);

        $inTextTag = "[{$count}]";

        $citation = $project->citations()->create([
            ...$validated,
            'key' => $key,
            'year' => $year,
            'in_text_tag' => $inTextTag,
            'added_by_id' => $request->user()?->id,
        ])->load('addedBy:id,name');

        return response()->json(['data' => $citation], 201);
    }

    public function destroy(ResearchProject $project, Citation $citation): JsonResponse
    {
        abort_unless($citation->research_project_id === $project->id, 404);

        $citation->delete();

        return response()->json(['message' => 'Citation removed successfully']);
    }
}
