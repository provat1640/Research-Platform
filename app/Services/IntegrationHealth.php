<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IntegrationHealth
{
    /**
     * @return array<string, array<string, string>>
     */
    public function report(): array
    {
        return [
            'ai' => $this->probeAi(),
            'supabase' => $this->probeSupabase(),
            'reverb' => $this->reverbStatus(),
        ];
    }

    /**
     * @return array{status: string, label: string, detail: string}
     */
    private function probeAi(): array
    {
        $baseUrl = config('services.ai.base_url');
        $model = config('services.ai.model');

        if (blank($baseUrl) || blank($model)) {
            return ['status' => 'not_configured', 'label' => 'Not configured', 'detail' => 'Add an AI endpoint and model.'];
        }

        try {
            $response = Http::connectTimeout(2)->timeout(4)->acceptJson()->get(rtrim($baseUrl, '/').'/models');

            return $response->successful()
                ? ['status' => 'ready', 'label' => 'Ready', 'detail' => 'AI endpoint is reachable.']
                : ['status' => 'unreachable', 'label' => 'Needs attention', 'detail' => 'The AI endpoint returned an unavailable response.'];
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
