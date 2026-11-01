<?php

namespace App\Core\Architecture;

use InvalidArgumentException;

class HashIntegrityProtector
{
    /**
     * Compute a deterministic SHA-256 hash of string or array data.
     *
     * @param  string|array<string, mixed>  $data
     */
    public function hash(string|array $data): string
    {
        $serialized = is_array($data) ? $this->canonicalJson($data) : $data;

        return hash('sha256', $serialized);
    }

    /**
     * Compute an HMAC-SHA256 signature using an application secret.
     *
     * @param  string|array<string, mixed>  $data
     */
    public function hmac(string|array $data, string $secret): string
    {
        $serialized = is_array($data) ? $this->canonicalJson($data) : $data;

        return hash_hmac('sha256', $serialized, $secret);
    }

    /**
     * Verify that a given hash matches the payload.
     *
     * @param  string|array<string, mixed>  $data
     */
    public function verify(string|array $data, string $expectedHash): bool
    {
        return hash_equals($this->hash($data), $expectedHash);
    }

    /**
     * Seal a payload with a cryptographic checksum and timestamp.
     *
     * @param  array<string, mixed>  $payload
     * @return array{payload: array<string, mixed>, hash: string, signature: string, sealed_at: string}
     */
    public function seal(array $payload, string $secret): array
    {
        $dataHash = $this->hash($payload);
        $signature = $this->hmac($payload, $secret);

        return [
            'payload' => $payload,
            'hash' => $dataHash,
            'signature' => $signature,
            'sealed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Verify a sealed envelope's authenticity and tamper-resistance.
     *
     * @param  array{payload?: array<string, mixed>, hash?: string, signature?: string}  $envelope
     */
    public function verifySeal(array $envelope, string $secret): bool
    {
        if (! isset($envelope['payload'], $envelope['hash'], $envelope['signature'])) {
            return false;
        }

        $calculatedHash = $this->hash($envelope['payload']);
        $calculatedSignature = $this->hmac($envelope['payload'], $secret);

        return hash_equals($calculatedHash, $envelope['hash'])
            && hash_equals($calculatedSignature, $envelope['signature']);
    }

    /**
     * Normalize array keys recursively to produce deterministic JSON.
     *
     * @param  array<string, mixed>  $data
     */
    public function canonicalJson(array $data): string
    {
        $sorted = $this->recursiveKsort($data);
        $json = json_encode($sorted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new InvalidArgumentException('Unable to encode data to canonical JSON.');
        }

        return $json;
    }

    /**
     * @param  array<string, mixed>  $array
     * @return array<string, mixed>
     */
    private function recursiveKsort(array $array): array
    {
        ksort($array);

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->recursiveKsort($value);
            }
        }

        return $array;
    }
}
