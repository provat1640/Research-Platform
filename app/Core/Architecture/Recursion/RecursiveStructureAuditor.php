<?php

namespace App\Core\Architecture\Recursion;

class RecursiveStructureAuditor
{
    public const DEFAULT_MAX_DEPTH = 20;

    /**
     * Recursively audits a hierarchical tree of nodes (e.g., outline, sections, tasks).
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @param  array<string, bool>  $visitedIds
     * @return array{valid: bool, node_count: int, max_observed_depth: int, errors: array<int, string>}
     */
    public function auditTree(
        array $nodes,
        int $currentDepth = 1,
        int $maxDepth = self::DEFAULT_MAX_DEPTH,
        array &$visitedIds = []
    ): array {
        $nodeCount = 0;
        $maxObservedDepth = $currentDepth;
        $errors = [];

        if ($currentDepth > $maxDepth) {
            return [
                'valid' => false,
                'node_count' => 0,
                'max_observed_depth' => $currentDepth,
                'errors' => ["Recursion depth exceeded maximum limit of {$maxDepth} levels."],
            ];
        }

        foreach ($nodes as $index => $node) {
            $nodeCount++;

            $nodeId = $node['id'] ?? $node['key'] ?? null;
            if ($nodeId !== null) {
                $nodeIdStr = (string) $nodeId;
                if (isset($visitedIds[$nodeIdStr])) {
                    $errors[] = "Duplicate node identifier [{$nodeIdStr}] detected in recursive tree.";
                } else {
                    $visitedIds[$nodeIdStr] = true;
                }
            }

            // Inspect nested children (sections, children, subsections, or subtasks)
            $children = $node['children'] ?? $node['sections'] ?? $node['subsections'] ?? null;
            if (is_array($children) && ! empty($children)) {
                $childAudit = $this->auditTree($children, $currentDepth + 1, $maxDepth, $visitedIds);
                $nodeCount += $childAudit['node_count'];
                $maxObservedDepth = max($maxObservedDepth, $childAudit['max_observed_depth']);
                $errors = array_merge($errors, $childAudit['errors']);
            }
        }

        return [
            'valid' => empty($errors),
            'node_count' => $nodeCount,
            'max_observed_depth' => $maxObservedDepth,
            'errors' => $errors,
        ];
    }

    /**
     * Recursively compute a deterministic cryptographic hash for a node and all of its descendants.
     *
     * @param  array<string, mixed>  $node
     */
    public function computeRecursiveHash(array $node): string
    {
        $childrenHashes = [];
        $children = $node['children'] ?? $node['sections'] ?? $node['subsections'] ?? [];

        if (is_array($children)) {
            foreach ($children as $child) {
                if (is_array($child)) {
                    $childrenHashes[] = $this->computeRecursiveHash($child);
                }
            }
        }

        $baseData = [
            'id' => $node['id'] ?? $node['key'] ?? '',
            'title' => $node['title'] ?? $node['name'] ?? '',
            'content' => $node['content'] ?? $node['body'] ?? '',
            'children_hashes' => $childrenHashes,
        ];

        return hash('sha256', json_encode($baseData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Recursively flatten a nested hierarchy into a linear array with breadcrumb paths.
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @return array<int, array<string, mixed>>
     */
    public function flattenRecursive(array $nodes, string $parentPath = ''): array
    {
        $flattened = [];

        foreach ($nodes as $node) {
            $currentId = (string) ($node['id'] ?? $node['key'] ?? uniqid('node_'));
            $title = (string) ($node['title'] ?? $node['name'] ?? $currentId);
            $path = $parentPath === '' ? $title : $parentPath.' / '.$title;

            $item = $node;
            unset($item['children'], $item['sections'], $item['subsections']);
            $item['tree_path'] = $path;
            $flattened[] = $item;

            $children = $node['children'] ?? $node['sections'] ?? $node['subsections'] ?? [];
            if (is_array($children) && ! empty($children)) {
                $flattened = array_merge($flattened, $this->flattenRecursive($children, $path));
            }
        }

        return $flattened;
    }

    /**
     * Recursively search for the first node satisfying a given predicate.
     *
     * @param  array<int, array<string, mixed>>  $nodes
     * @param  callable(array<string, mixed>): bool  $predicate
     * @return array<string, mixed>|null
     */
    public function findRecursive(array $nodes, callable $predicate): ?array
    {
        foreach ($nodes as $node) {
            if ($predicate($node)) {
                return $node;
            }

            $children = $node['children'] ?? $node['sections'] ?? $node['subsections'] ?? [];
            if (is_array($children) && ! empty($children)) {
                $found = $this->findRecursive($children, $predicate);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
