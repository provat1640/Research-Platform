<?php

namespace App\Core\Architecture\BinaryTree;

class MerkleBinaryTree
{
    private ?BinaryTreeNode $root = null;

    private int $nodeCount = 0;

    /**
     * Insert a key-value node into the binary search tree.
     */
    public function insert(string $key, mixed $value): void
    {
        $this->root = $this->insertRecursive($this->root, $key, $value);
        $this->nodeCount++;
    }

    /**
     * Search for a node by key with O(log n) average complexity.
     */
    public function search(string $key): ?BinaryTreeNode
    {
        return $this->searchRecursive($this->root, $key);
    }

    /**
     * Check if a key exists in the binary tree.
     */
    public function has(string $key): bool
    {
        return $this->search($key) !== null;
    }

    /**
     * Compute and return the cryptographic Merkle root hash of the binary tree.
     */
    public function computeMerkleRoot(): ?string
    {
        return $this->computeSubtreeHash($this->root);
    }

    /**
     * Verify that a given key contains the expected value in the tree.
     */
    public function verifyKey(string $key, mixed $expectedValue): bool
    {
        $node = $this->search($key);
        if ($node === null) {
            return false;
        }

        return $node->value === $expectedValue;
    }

    /**
     * Return all elements in sorted order via in-order traversal.
     *
     * @return array<int, array{key: string, value: mixed, hash: ?string}>
     */
    public function inOrderTraversal(): array
    {
        $result = [];
        $this->inOrderRecursive($this->root, $result);

        return $result;
    }

    public function count(): int
    {
        return $this->nodeCount;
    }

    public function getRoot(): ?BinaryTreeNode
    {
        return $this->root;
    }

    /**
     * Build a balanced binary tree from an associative array of key => value elements.
     *
     * @param  array<string, mixed>  $elements
     */
    public static function buildFromElements(array $elements): self
    {
        $tree = new self;
        ksort($elements);

        $keys = array_keys($elements);
        $tree->buildBalancedRecursive($tree, $elements, $keys, 0, count($keys) - 1);

        return $tree;
    }

    /**
     * @param  array<string, mixed>  $elements
     * @param  array<int, string>  $keys
     */
    private function buildBalancedRecursive(
        self $tree,
        array $elements,
        array $keys,
        int $start,
        int $end
    ): void {
        if ($start > $end) {
            return;
        }

        $mid = (int) floor(($start + $end) / 2);
        $key = $keys[$mid];
        $tree->insert($key, $elements[$key]);

        $this->buildBalancedRecursive($tree, $elements, $keys, $start, $mid - 1);
        $this->buildBalancedRecursive($tree, $elements, $keys, $mid + 1, $end);
    }

    private function insertRecursive(?BinaryTreeNode $current, string $key, mixed $value): BinaryTreeNode
    {
        if ($current === null) {
            return new BinaryTreeNode($key, $value);
        }

        if (strcmp($key, $current->key) < 0) {
            $current->left = $this->insertRecursive($current->left, $key, $value);
        } elseif (strcmp($key, $current->key) > 0) {
            $current->right = $this->insertRecursive($current->right, $key, $value);
        } else {
            // Overwrite existing key value and update leaf hash
            $current->value = $value;
            $current->hash = $current->calculateLeafHash();
            $this->nodeCount--; // Prevent double-counting updated keys
        }

        return $current;
    }

    private function searchRecursive(?BinaryTreeNode $current, string $key): ?BinaryTreeNode
    {
        if ($current === null) {
            return null;
        }

        $cmp = strcmp($key, $current->key);
        if ($cmp === 0) {
            return $current;
        }

        if ($cmp < 0) {
            return $this->searchRecursive($current->left, $key);
        }

        return $this->searchRecursive($current->right, $key);
    }

    private function computeSubtreeHash(?BinaryTreeNode $node): ?string
    {
        if ($node === null) {
            return null;
        }

        $leftHash = $this->computeSubtreeHash($node->left) ?? 'NULL_LEFT';
        $rightHash = $this->computeSubtreeHash($node->right) ?? 'NULL_RIGHT';

        $node->hash = hash('sha256', sprintf('BRANCH:%s:%s:%s', $node->calculateLeafHash(), $leftHash, $rightHash));

        return $node->hash;
    }

    /**
     * @param  array<int, array{key: string, value: mixed, hash: ?string}>  $result
     */
    private function inOrderRecursive(?BinaryTreeNode $node, array &$result): void
    {
        if ($node === null) {
            return;
        }

        $this->inOrderRecursive($node->left, $result);
        $result[] = [
            'key' => $node->key,
            'value' => $node->value,
            'hash' => $node->hash,
        ];
        $this->inOrderRecursive($node->right, $result);
    }
}
