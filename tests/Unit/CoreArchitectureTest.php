<?php

namespace Tests\Unit;

use App\Core\Architecture\BinaryTree\MerkleBinaryTree;
use App\Core\Architecture\Graph\DependencyGraphTreeMapper;
use App\Core\Architecture\HashIntegrityProtector;
use App\Core\Architecture\LinkedList\VersionLedgerLinkedList;
use App\Core\Architecture\Recursion\RecursiveStructureAuditor;
use App\Core\Architecture\Search\LinearAuditSearcher;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CoreArchitectureTest extends TestCase
{
    public function test_hash_integrity_protector_calculates_and_verifies_tamper_evident_seals(): void
    {
        $hasher = new HashIntegrityProtector;
        $payload = ['title' => 'Empirical Analysis', 'version' => 1, 'sections' => ['intro', 'methods']];

        $sealed = $hasher->seal($payload, 'test-secret');

        $this->assertNotEmpty($sealed['hash']);
        $this->assertNotEmpty($sealed['signature']);
        $this->assertTrue($hasher->verifySeal($sealed, 'test-secret'));

        // Tamper with payload
        $tampered = $sealed;
        $tampered['payload']['title'] = 'Tampered Title';
        $this->assertFalse($hasher->verifySeal($tampered, 'test-secret'));

        // Wrong secret verification fails
        $this->assertFalse($hasher->verifySeal($sealed, 'wrong-secret'));
    }

    public function test_version_ledger_linked_list_chains_nodes_and_detects_tampering(): void
    {
        $ledger = new VersionLedgerLinkedList;

        $v1 = $ledger->append('v1', ['text' => 'First draft'], 'Dr. Alice', '2026-11-01T10:00:00Z');
        $v2 = $ledger->append('v2', ['text' => 'Second draft'], 'Dr. Bob', '2026-11-01T11:00:00Z');
        $v3 = $ledger->append('v3', ['text' => 'Final review'], 'Prof. Charlie', '2026-11-01T12:00:00Z');

        $this->assertSame(3, $ledger->count());
        $this->assertNull($v1->prevHash);
        $this->assertSame($v1->hash, $v2->prevHash);
        $this->assertSame($v2->hash, $v3->prevHash);

        $integrity = $ledger->verifyChainIntegrity();
        $this->assertTrue($integrity['valid']);

        // Tamper with intermediate node payload
        $v2->payload['text'] = 'Tampered unauthorized text';
        $tamperedCheck = $ledger->verifyChainIntegrity();

        $this->assertFalse($tamperedCheck['valid']);
        $this->assertSame('v2', $tamperedCheck['broken_at_version']);
    }

    public function test_merkle_binary_tree_indexes_elements_and_computes_deterministic_root(): void
    {
        $elements = [
            'sec-1' => 'Introduction to Neural Systems',
            'sec-2' => 'Methodological Grounding',
            'sec-3' => 'Empirical Benchmark Results',
            'sec-4' => 'Discussion and Limitations',
        ];

        $tree = MerkleBinaryTree::buildFromElements($elements);

        $this->assertSame(4, $tree->count());
        $this->assertTrue($tree->has('sec-2'));
        $this->assertFalse($tree->has('sec-99'));

        $node = $tree->search('sec-3');
        $this->assertNotNull($node);
        $this->assertSame('Empirical Benchmark Results', $node->value);

        $root1 = $tree->computeMerkleRoot();
        $this->assertNotNull($root1);

        // Building identical tree produces identical deterministic Merkle Root
        $treeDuplicate = MerkleBinaryTree::buildFromElements($elements);
        $root2 = $treeDuplicate->computeMerkleRoot();
        $this->assertSame($root1, $root2);

        // Modifying one element changes Merkle root
        $elements['sec-2'] = 'Altered methodology content';
        $treeAltered = MerkleBinaryTree::buildFromElements($elements);
        $root3 = $treeAltered->computeMerkleRoot();
        $this->assertNotSame($root1, $root3);
    }

    public function test_recursive_structure_auditor_detects_depth_limits_and_duplicate_nodes(): void
    {
        $auditor = new RecursiveStructureAuditor;

        $validOutline = [
            ['id' => 'ch_1', 'title' => 'Introduction', 'children' => [
                ['id' => 'sec_1_1', 'title' => 'Context'],
                ['id' => 'sec_1_2', 'title' => 'Questions'],
            ]],
            ['id' => 'ch_2', 'title' => 'Methodology'],
        ];

        $audit = $auditor->auditTree($validOutline);
        $this->assertTrue($audit['valid']);
        $this->assertSame(4, $audit['node_count']);
        $this->assertSame(2, $audit['max_observed_depth']);

        // Test recursion depth guard
        $deepTree = [['id' => 'd1', 'title' => 'Level 1', 'children' => [
            ['id' => 'd2', 'title' => 'Level 2', 'children' => [
                ['id' => 'd3', 'title' => 'Level 3'],
            ]],
        ]]];

        $shallowAudit = $auditor->auditTree($deepTree, 1, 2);
        $this->assertFalse($shallowAudit['valid']);
        $this->assertStringContainsString('Recursion depth exceeded', $shallowAudit['errors'][0]);

        // Test duplicate identifier detection
        $duplicateTree = [
            ['id' => 'dup_1', 'title' => 'Node A'],
            ['id' => 'dup_1', 'title' => 'Node B with duplicate ID'],
        ];

        $dupAudit = $auditor->auditTree($duplicateTree);
        $this->assertFalse($dupAudit['valid']);
        $this->assertStringContainsString('Duplicate node identifier', $dupAudit['errors'][0]);
    }

    public function test_graph_tree_mapper_maps_hierarchy_and_protects_against_cycles(): void
    {
        $mapper = new DependencyGraphTreeMapper;

        $tree = [
            ['id' => 'intro', 'title' => 'Introduction', 'children' => [
                ['id' => 'methods', 'title' => 'Methods'],
            ]],
            ['id' => 'results', 'title' => 'Results'],
        ];

        $mapper->mapTreeToGraph($tree);
        $mapper->addDependency('results', 'methods'); // Results depends on Methods

        $this->assertSame(3, $mapper->nodeCount());
        $this->assertFalse($mapper->hasCycles());
        $this->assertTrue($mapper->isReachable('results', 'methods'));

        $order = $mapper->topologicalSort();
        $this->assertContains('methods', $order);
        $this->assertContains('results', $order);

        // Inject circular dependency: methods depends on results
        $mapper->addDependency('methods', 'results');

        $this->assertTrue($mapper->hasCycles());
        $cycle = $mapper->findCycle();
        $this->assertNotNull($cycle);
        $this->assertContains('methods', $cycle);
        $this->assertContains('results', $cycle);

        $this->expectException(RuntimeException::class);
        $mapper->topologicalSort();
    }

    public function test_linear_audit_searcher_scans_collections_and_detects_sequence_breaks(): void
    {
        $searcher = new LinearAuditSearcher;

        $events = [
            ['seq' => 1, 'action' => 'login', 'user' => 'alice'],
            ['seq' => 2, 'action' => 'edit_draft', 'user' => 'bob'],
            ['seq' => 3, 'action' => 'export_bibtex', 'user' => 'alice'],
            ['seq' => 4, 'action' => 'login', 'user' => 'charlie'],
        ];

        $firstAlice = $searcher->searchByProperty($events, 'user', 'alice');
        $this->assertSame('login', $firstAlice['action']);

        $allLogins = $searcher->findAll($events, fn ($e): bool => $e['action'] === 'login');
        $this->assertCount(2, $allLogins);

        $matches = $searcher->searchPattern($events, 'action', '/^edit_/');
        $this->assertCount(1, $matches);
        $this->assertSame('edit_draft', $matches[0]['action']);

        // Sequential integrity scan
        $validSequence = $searcher->sequentialIntegrityScan($events, function ($item, $index, $prev): array {
            if ($prev !== null && $item['seq'] !== $prev['seq'] + 1) {
                return ['valid' => false, 'reason' => 'Broken sequence counter.'];
            }

            return ['valid' => true];
        });
        $this->assertTrue($validSequence['passed']);

        // Broken sequence scan
        $brokenEvents = [
            ['seq' => 1, 'action' => 'init'],
            ['seq' => 2, 'action' => 'step'],
            ['seq' => 99, 'action' => 'gap'], // Gap
        ];

        $brokenScan = $searcher->sequentialIntegrityScan($brokenEvents, function ($item, $index, $prev): array {
            if ($prev !== null && $item['seq'] !== $prev['seq'] + 1) {
                return ['valid' => false, 'reason' => "Gap detected at index {$index}."];
            }

            return ['valid' => true];
        });

        $this->assertFalse($brokenScan['passed']);
        $this->assertSame(2, $brokenScan['failed_index']);
    }
}
