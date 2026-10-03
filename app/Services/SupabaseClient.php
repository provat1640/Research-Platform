<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SupabaseClient
{
    public function configured(): bool
    {
        return filled(config('services.supabase.url')) && filled(config('services.supabase.key'));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function select(string $table, array $filters = []): array
    {
        if (! $this->configured()) {
            return [];
        }

        return $this->request()
            ->get('/rest/v1/'.$table, $filters)
            ->throw()
            ->json();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    public function insert(string $table, array $payload): array
    {
        if (! $this->configured()) {
            return [];
        }

        return $this->request()
            ->withHeaders(['Prefer' => 'return=representation'])
            ->post('/rest/v1/'.$table, $payload)
            ->throw()
            ->json();
    }

    protected function request(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.supabase.url'), '/'))
            ->acceptJson()
            ->withHeaders([
                'apikey' => (string) config('services.supabase.key'),
                'Authorization' => 'Bearer '.config('services.supabase.key'),
            ])
            ->timeout((int) config('services.supabase.timeout', 10));
    }
}
