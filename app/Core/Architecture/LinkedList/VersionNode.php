<?php

namespace App\Core\Architecture\LinkedList;

class VersionNode
{
    public string $hash;

    public ?VersionNode $next = null;

    public ?VersionNode $prev = null;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $versionId,
        public array $payload,
        public string $author,
        public ?string $prevHash = null,
        public ?string $timestamp = null
    ) {
        $this->timestamp = $timestamp ?? now()->toIso8601String();
        $this->hash = $this->calculateHash();
    }

    /**
     * Calculate cryptographic SHA-256 hash for this node.
     */
    public function calculateHash(): string
    {
        $raw = sprintf(
            '%s|%s|%s|%s|%s',
            $this->versionId,
            json_encode($this->payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $this->author,
            $this->prevHash ?? 'GENESIS',
            $this->timestamp
        );

        return hash('sha256', $raw);
    }

    /**
     * Verify whether this node's internal state matches its calculated hash.
     */
    public function verifyNodeIntegrity(): bool
    {
        return hash_equals($this->calculateHash(), $this->hash);
    }
}
