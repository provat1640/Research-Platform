<?php

namespace App\Core\Architecture\Graph;

class GraphNode
{
    /**
     * @param  array<string, mixed>  $metadata
     * @param  array<int, string>  $dependencies  Target node IDs that this node depends on
     */
    public function __construct(
        public string $id,
        public string $label,
        public array $metadata = [],
        public array $dependencies = []
    ) {}

    public function addDependency(string $targetId): void
    {
        if (! in_array($targetId, $this->dependencies, true) && $targetId !== $this->id) {
            $this->dependencies[] = $targetId;
        }
    }
}
