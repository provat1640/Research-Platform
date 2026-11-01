<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IntegrationHealth
{
    /**
     * @return array<string, mixed>
     */
    public function report(AiGateway $ai): array
    {
        return [
            'ai' => $this->probeAi($ai),
            'supabase' => $this->probeSupabase(),
            'reverb' => $this->reverbStatus(),
            'environment' => [
                'active_directory' => base_path(),
                'public_storage_path' => public_path('storage'),
                'storage_linked' => file_exists(public_path('storage')),
                'php_version' => PHP_VERSION,
                'database_driver' => (string) config('database.default'),
                'ai_provider' => $ai->provider(),
                'ai_model' => $ai->model(),
                'ai_free_access' => (bool) config('services.ai.free_access', true),
                'ai_encryption' => 'TLS in-transit & AES-256-CBC at-rest',
            ],
        ];
    }

    /**
     * @return array{status: string, label: string, detail: string, model?: string, available_models?: string, remote_model_count?: string}
     */
    private function probeAi(AiGateway $ai): array
    {
        if (! $ai->configured()) {
            return ['status' => 'not_configured', 'label' => 'Not configured', 'detail' => 'Add an AI endpoint and model.'];
        }

        try {
            $response = $ai->models();
            $model = $ai->model();

            return [
                'status' => 'ready',
                'label' => 'Ready',
                'detail' => sprintf('%s · %s', $ai->provider(), $model),
                'model' => $model,
                'available_models' => implode(',', $ai->availableModels()),
                'remote_model_count' => (string) count($response['data'] ?? []),
            ];
        } catch (\Throwable) {
            return ['status' => 'unreachable', 'label' => 'Offline', 'detail' => 'Start the configured AI service to enable assistance.'];
        }
    }

    /**
     * @return array{status: string, label: string, detail: string}
     */
    private function probeSupabase(): array
    {
        $url = config('services.supabase.url');
        $key = config('services.supabase.key');

        if (blank($url) || blank($key)) {
            return ['status' => 'not_configured', 'label' => 'Not configured', 'detail' => 'Optional hosted sync is not connected.'];
        }

        try {
            $response = Http::connectTimeout(2)->timeout(4)->withHeaders(['apikey' => $key])->get(rtrim($url, '/').'/rest/v1/');

            return $response->successful() || $response->status() === 404
                ? ['status' => 'ready', 'label' => 'Ready', 'detail' => 'Supabase REST endpoint is reachable.']
                : ['status' => 'unreachable', 'label' => 'Needs attention', 'detail' => 'Supabase returned an unavailable response.'];
        } catch (\Throwable) {
            return ['status' => 'unreachable', 'label' => 'Offline', 'detail' => 'Check the Supabase URL and network connection.'];
        }
    }

    /**
     * @return array{status: string, label: string, detail: string}
     */
    private function reverbStatus(): array
    {
        $configured = filled(config('broadcasting.connections.reverb.key'))
            && filled(config('broadcasting.connections.reverb.options.host'));

        return $configured
            ? ['status' => 'configured', 'label' => 'Configured', 'detail' => 'Realtime settings are present.']
            : ['status' => 'not_configured', 'label' => 'Not configured', 'detail' => 'Add Reverb connection settings for live collaboration.'];
    }
}
