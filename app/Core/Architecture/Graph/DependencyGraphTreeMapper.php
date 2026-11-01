<?php

namespace App\Core\Architecture\Graph;

use RuntimeException;

class DependencyGraphTreeMapper
{
    /** @var array<string, GraphNode> */
    private array $nodes = [];

    /**
     * Add or register a node into the graph.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function addNode(string $id, string $label, array $metadata = []): GraphNode
    {
        if (! isset($this->nodes[$id])) {
            $this->nodes[$id] = new GraphNode($id, $label, $metadata);
        } else {
            $this->nodes[$id]->label = $label;
            $this->nodes[$id]->metadata = array_merge($this->nodes[$id]->metadata, $metadata);
        }

        return $this->nodes[$id];
    }

    /**
     * Add a directed dependency edge ($fromId depends on $toId).
     */
    public function addDependency(string $fromId, string $toId): void
    {
        if (! isset($this->nodes[$fromId])) {
            $this->addNode($fromId, $fromId);
        }
        if (! isset($this->nodes[$toId])) {
            $this->addNode($toId, $toId);
        }

        $this->nodes[$fromId]->addDependency($toId);
    }

    /**
     * Map a hierarchical tree (outline/sections) into the graph topology.
     *
     * @param  array<int, array<string, mixed>>  $treeNodes
     */
    public function mapTreeToGraph(array $treeNodes, ?string $parentId = null): void
    {
        foreach ($treeNodes as $node) {
            $id = (string) ($node['id'] ?? $node['key'] ?? uniqid('tree_node_'));
            $label = (string) ($node['title'] ?? $node['name'] ?? $id);

            $this->addNode($id, $label, [
                'type' => $node['type'] ?? 'tree_node',
                'parent_id' => $parentId,
            ]);

            // If this node has a parent, link the structural dependency
            if ($parentId !== null) {
                $this->addDependency($id, $parentId);
            }

            // Map declared cross-references or explicit dependencies if present
            $explicitDeps = $node['depends_on'] ?? $node['dependencies'] ?? [];
            if (is_array($explicitDeps)) {
                foreach ($explicitDeps as $depId) {
                    $this->addDependency($id, (string) $depId);
                }
            }

            // Recursively map subtree children
            $children = $node['children'] ?? $node['sections'] ?? $node['subsections'] ?? [];
            if (is_array($children) && ! empty($children)) {
                $this->mapTreeToGraph($children, $id);
            }
        }
    }

    public function getNode(string $id): ?GraphNode
    {
        return $this->nodes[$id] ?? null;
    }

    /**
     * @return array<string, GraphNode>
     */
    public function getAllNodes(): array
    {
        return $this->nodes;
    }

    public function nodeCount(): int
    {
        return count($this->nodes);
    }

    /**
     * Check if the dependency graph contains any circular references (cycles).
     */
    public function hasCycles(): bool
    {
        return $this->findCycle() !== null;
    }

    /**
     * Detect and return the cycle path if one exists using 3-color DFS.
     *
     * @return array<int, string>|null
     */
    public function findCycle(): ?array
    {
        $visited = []; // 0 = unvisited, 1 = visiting (in stack), 2 = visited
        $parentTracker = [];

        foreach (array_keys($this->nodes) as $nodeId) {
            $visited[$nodeId] = 0;
        }

        foreach (array_keys($this->nodes) as $nodeId) {
            if ($visited[$nodeId] === 0) {
                $cycle = $this->dfsCycle($nodeId, $visited, $parentTracker);
                if ($cycle !== null) {
                    return $cycle;
                }
            }
        }

        return null;
    }

    /**
     * Produce a topological sort of the graph nodes (safe execution / resolution order).
     *
     * @return array<int, string>
     *
     * @throws RuntimeException If the graph contains a circular cycle.
     */
    public function topologicalSort(): array
    {
        if ($this->hasCycles()) {
            throw new RuntimeException('Cannot perform topological sort: cyclic dependency detected.');
        }

        $visited = [];
        $order = [];

        foreach (array_keys($this->nodes) as $nodeId) {
            $visited[$nodeId] = false;
        }

        foreach (array_keys($this->nodes) as $nodeId) {
            if (! $visited[$nodeId]) {
                $this->dfsTopological($nodeId, $visited, $order);
            }
        }

        return $order;
    }

    /**
     * Check if a path exists from $fromId to $toId.
     */
    public function isReachable(string $fromId, string $toId): bool
    {
        if (! isset($this->nodes[$fromId]) || ! isset($this->nodes[$toId])) {
            return false;
        }

        if ($fromId === $toId) {
            return true;
        }

        $visited = [];
        $queue = [$fromId];
        $visited[$fromId] = true;

        while (! empty($queue)) {
            $currentId = array_shift($queue);
            $currentNode = $this->nodes[$currentId] ?? null;

            if ($currentNode === null) {
                continue;
            }

            foreach ($currentNode->dependencies as $neighborId) {
                if ($neighborId === $toId) {
                    return true;
                }

                if (! isset($visited[$neighborId])) {
                    $visited[$neighborId] = true;
                    $queue[] = $neighborId;
                }
            }
        }

        return false;
    }

    /**
     * @param  array<string, int>  $visited
     * @param  array<string, string>  $parentTracker
     * @return array<int, string>|null
     */
    private function dfsCycle(string $nodeId, array &$visited, array &$parentTracker): ?array
    {
        $visited[$nodeId] = 1; // Mark as visiting

        $node = $this->nodes[$nodeId] ?? null;
        if ($node !== null) {
            foreach ($node->dependencies as $depId) {
                if (! isset($visited[$depId]) || $visited[$depId] === 0) {
                    $parentTracker[$depId] = $nodeId;
                    $cycle = $this->dfsCycle($depId, $visited, $parentTracker);
                    if ($cycle !== null) {
                        return $cycle;
                    }
                } elseif ($visited[$depId] === 1) {
                    // Cycle found: reconstruct path
                    $cyclePath = [$depId, $nodeId];
                    $curr = $nodeId;
                    while (isset($parentTracker[$curr]) && $parentTracker[$curr] !== $depId) {
                        $curr = $parentTracker[$curr];
                        $cyclePath[] = $curr;
                    }
                    $cyclePath[] = $depId;

                    return array_reverse($cyclePath);
                }
            }
        }

        $visited[$nodeId] = 2; // Mark as completely visited

        return null;
    }

    /**
     * @param  array<string, bool>  $visited
     * @param  array<int, string>  $order
     */
    private function dfsTopological(string $nodeId, array &$visited, array &$order): void
    {
        $visited[$nodeId] = true;

        $node = $this->nodes[$nodeId] ?? null;
        if ($node !== null) {
            foreach ($node->dependencies as $depId) {
                if (! ($visited[$depId] ?? false)) {
                    $this->dfsTopological($depId, $visited, $order);
                }
            }
        }

        $order[] = $nodeId;
    }
}
