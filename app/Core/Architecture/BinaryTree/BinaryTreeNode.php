<?php

namespace App\Core\Architecture\BinaryTree;

class BinaryTreeNode
{
    public ?string $hash = null;

    public ?BinaryTreeNode $left = null;

    public ?BinaryTreeNode $right = null;

    public function __construct(
        public string $key,
        public mixed $value
    ) {
        $this->hash = $this->calculateLeafHash();
    }

    public function calculateLeafHash(): string
    {
        $serialized = is_array($this->value)
            ? json_encode($this->value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : (string) $this->value;

        return hash('sha256', sprintf('LEAF:%s:%s', $this->key, $serialized));
    }
}
