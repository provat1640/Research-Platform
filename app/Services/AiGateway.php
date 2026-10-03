<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiGateway
{
    public function configured(): bool
    {
        return filled(config('services.ai.base_url')) && filled(config('services.ai.model'));
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    public function chat(array $messages): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('The AI gateway is not configured. Set AI_BASE_URL and AI_MODEL.');
        }

        return $this->request()
            ->post('/chat/completions', [
                'model' => config('services.ai.model'),
                'messages' => $messages,
                'temperature' => (float) config('services.ai.temperature', 0.2),
            ])
            ->throw()
            ->json();
    }

    protected function request(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.ai.base_url'), '/'))
            ->acceptJson()
            ->withToken((string) config('services.ai.key'))
            ->timeout((int) config('services.ai.timeout', 30));
    }
}
