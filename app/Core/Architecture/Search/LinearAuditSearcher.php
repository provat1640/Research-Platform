<?php

namespace App\Core\Architecture\Search;

class LinearAuditSearcher
{
    /**
     * Perform a sequential linear search to find the first element satisfying a predicate.
     *
     * @param  iterable<mixed>  $collection
     * @param  callable(mixed, int|string): bool  $predicate
     */
    public function findFirst(iterable $collection, callable $predicate): mixed
    {
        foreach ($collection as $key => $item) {
            if ($predicate($item, $key)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Perform a sequential linear search to find all elements satisfying a predicate.
     *
     * @param  iterable<mixed>  $collection
     * @param  callable(mixed, int|string): bool  $predicate
     * @return array<int, mixed>
     */
    public function findAll(iterable $collection, callable $predicate): array
    {
        $matches = [];
        foreach ($collection as $key => $item) {
            if ($predicate($item, $key)) {
                $matches[] = $item;
            }
        }

        return $matches;
    }

    /**
     * Search an unindexed collection sequentially by matching an object property or array key.
     *
     * @param  iterable<mixed>  $collection
     */
    public function searchByProperty(iterable $collection, string $property, mixed $expectedValue): mixed
    {
        return $this->findFirst($collection, function ($item) use ($property, $expectedValue): bool {
            if (is_array($item)) {
                return isset($item[$property]) && $item[$property] === $expectedValue;
            }
            if (is_object($item)) {
                return isset($item->{$property}) && $item->{$property} === $expectedValue;
            }

            return false;
        });
    }

    /**
     * Linearly scan an unindexed stream for string regex pattern matches across a property.
     *
     * @param  iterable<mixed>  $collection
     * @return array<int, mixed>
     */
    public function searchPattern(iterable $collection, string $property, string $pattern): array
    {
        return $this->findAll($collection, function ($item) use ($property, $pattern): bool {
            $value = null;
            if (is_array($item)) {
                $value = $item[$property] ?? null;
            } elseif (is_object($item)) {
                $value = $item->{$property} ?? null;
            }

            if (! is_string($value)) {
                return false;
            }

            return (bool) preg_match($pattern, $value);
        });
    }

    /**
     * Sequentially verify an ordered sequence of events or records.
     * Returns immediately upon the first failure with the failing index.
     *
     * @param  array<int, mixed>  $records
     * @param  callable(mixed, int, mixed|null): array{valid: bool, reason?: string}  $validator
     * @return array{passed: bool, failed_index: ?int, reason: ?string, scanned_count: int}
     */
    public function sequentialIntegrityScan(array $records, callable $validator): array
    {
        $scannedCount = 0;
        $previous = null;

        foreach ($records as $index => $record) {
            $scannedCount++;
            $result = $validator($record, $index, $previous);

            if (! ($result['valid'] ?? false)) {
                return [
                    'passed' => false,
                    'failed_index' => $index,
                    'reason' => $result['reason'] ?? "Integrity validation failed at sequence index {$index}.",
                    'scanned_count' => $scannedCount,
                ];
            }

            $previous = $record;
        }

        return [
            'passed' => true,
            'failed_index' => null,
            'reason' => null,
            'scanned_count' => $scannedCount,
        ];
    }

    /**
     * Return the sequential index of the first matching element, or -1 if not found.
     *
     * @param  array<int, mixed>  $items
     * @param  callable(mixed, int): bool  $predicate
     */
    public function indexOf(array $items, callable $predicate): int
    {
        foreach ($items as $index => $item) {
            if ($predicate($item, $index)) {
                return $index;
            }
        }

        return -1;
    }
}
