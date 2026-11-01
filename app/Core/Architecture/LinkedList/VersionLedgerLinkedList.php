<?php

namespace App\Core\Architecture\LinkedList;

use Generator;

class VersionLedgerLinkedList
{
    private ?VersionNode $head = null;

    private ?VersionNode $tail = null;

    private int $count = 0;

    /**
     * Append a new version node to the ledger with a cryptographic hash link.
     *
     * @param  array<string, mixed>  $payload
     */
    public function append(
        string $versionId,
        array $payload,
        string $author,
        ?string $timestamp = null
    ): VersionNode {
        $prevHash = $this->tail !== null ? $this->tail->hash : null;
        $node = new VersionNode($versionId, $payload, $author, $prevHash, $timestamp);

        if ($this->head === null) {
            $this->head = $node;
            $this->tail = $node;
        } else {
            $node->prev = $this->tail;
            $this->tail->next = $node;
            $this->tail = $node;
        }

        $this->count++;

        return $node;
    }

    public function getHead(): ?VersionNode
    {
        return $this->head;
    }

    public function getTail(): ?VersionNode
    {
        return $this->tail;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function isEmpty(): bool
    {
        return $this->count === 0;
    }

    /**
     * Verify the entire chain's integrity from genesis node to tail.
     *
     * @return array{valid: bool, broken_at_version: ?string, message: string}
     */
    public function verifyChainIntegrity(): array
    {
        if ($this->head === null) {
            return ['valid' => true, 'broken_at_version' => null, 'message' => 'Empty ledger is valid.'];
        }

        $current = $this->head;
        $expectedPrevHash = null;

        while ($current !== null) {
            if (! $current->verifyNodeIntegrity()) {
                return [
                    'valid' => false,
                    'broken_at_version' => $current->versionId,
                    'message' => "Node tamper detected: content hash mismatch at version {$current->versionId}.",
                ];
            }

            if ($current->prevHash !== $expectedPrevHash) {
                return [
                    'valid' => false,
                    'broken_at_version' => $current->versionId,
                    'message' => "Link tamper detected: prevHash mismatch at version {$current->versionId}.",
                ];
            }

            $expectedPrevHash = $current->hash;
            $current = $current->next;
        }

        return ['valid' => true, 'broken_at_version' => null, 'message' => 'Ledger chain integrity verified.'];
    }

    /**
     * Find a version node by its ID.
     */
    public function findByVersionId(string $versionId): ?VersionNode
    {
        $current = $this->head;

        while ($current !== null) {
            if ($current->versionId === $versionId) {
                return $current;
            }
            $current = $current->next;
        }

        return null;
    }

    /**
     * Forward traversal generator from head to tail.
     *
     * @return Generator<int, VersionNode>
     */
    public function traverseForward(): Generator
    {
        $current = $this->head;
        while ($current !== null) {
            yield $current;
            $current = $current->next;
        }
    }

    /**
     * Reverse traversal generator from tail to head.
     *
     * @return Generator<int, VersionNode>
     */
    public function traverseBackward(): Generator
    {
        $current = $this->tail;
        while ($current !== null) {
            yield $current;
            $current = $current->prev;
        }
    }

    /**
     * Convert the linked list into an array representation.
     *
     * @return array<int, array{version_id: string, author: string, hash: string, prev_hash: ?string, timestamp: ?string, payload: array<string, mixed>}>
     */
    public function toArray(): array
    {
        $records = [];
        foreach ($this->traverseForward() as $node) {
            $records[] = [
                'version_id' => $node->versionId,
                'author' => $node->author,
                'hash' => $node->hash,
                'prev_hash' => $node->prevHash,
                'timestamp' => $node->timestamp,
                'payload' => $node->payload,
            ];
        }

        return $records;
    }
}
