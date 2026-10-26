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

    public function model(): string
    {
        return (string) config('services.ai.model');
    }

    public function provider(): string
    {
        return filled(config('services.groq.key')) && config('services.ai.base_url') === config('services.groq.base_url')
            ? 'Groq'
            : 'OpenAI-compatible AI';
    }

    /**
     * @return array<int, string>
     */
    public function availableModels(): array
    {
        return array_keys(config('services.groq.free_models', []));
    }

    /**
     * @return array<string, mixed>
     */
    public function models(): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('The AI gateway is not configured.');
        }

        return $this->request()
            ->get('/models')
            ->throw()
            ->json();
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array<string, mixed>
     */
    public function chat(array $messages): array
    {
        if (! $this->configured()) {
            throw new RuntimeException('The AI gateway is not configured. Set GROQ_API_KEY and GROQ_MODEL, or configure AI_BASE_URL and AI_MODEL.');
        }

        return $this->request()
            ->post('/chat/completions', [
                'model' => $this->model(),
                'messages' => $messages,
                'temperature' => (float) config('services.ai.temperature', 0.2),
            ])
            ->throw()
            ->json();
    }

    protected function request(): PendingRequest
    {
        $request = Http::baseUrl(rtrim((string) config('services.ai.base_url'), '/'))
            ->acceptJson()
            ->connectTimeout((int) config('services.ai.connect_timeout', 5))
            ->timeout((int) config('services.ai.timeout', 30));

        return filled(config('services.ai.key'))
            ? $request->withToken((string) config('services.ai.key'))
            : $request;
    }
}
