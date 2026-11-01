<?php

namespace App\Core\Architecture;

use App\Core\Architecture\BinaryTree\MerkleBinaryTree;
use App\Core\Architecture\Graph\DependencyGraphTreeMapper;
use App\Core\Architecture\LinkedList\VersionLedgerLinkedList;
use App\Core\Architecture\Recursion\RecursiveStructureAuditor;
use App\Core\Architecture\Search\LinearAuditSearcher;
use App\Models\ResearchProject;

class ArchitectureSecurityVault
{
    public function __construct(
        public HashIntegrityProtector $hasher,
        public RecursiveStructureAuditor $recursiveAuditor,
        public LinearAuditSearcher $linearSearcher
    ) {}

    /**
     * Build and verify a tamper-evident Linked List ledger from project document versions.
     */
    public function buildVersionLedger(ResearchProject $project): VersionLedgerLinkedList
    {
        $ledger = new VersionLedgerLinkedList;
        $versions = $project->documentVersions()
            ->orderBy('version_number')
            ->get();

        foreach ($versions as $version) {
            $ledger->append(
                (string) $version->id,
                is_array($version->content) ? $version->content : ['text' => (string) $version->content],
                $version->author?->name ?? 'system',
                $version->created_at?->toIso8601String()
            );
        }

        return $ledger;
    }

    /**
     * Build a balanced Binary Tree and calculate its Merkle root hash.
     *
     * @param  array<string, mixed>  $elements
     */
    public function buildMerkleTree(array $elements): MerkleBinaryTree
    {
        $tree = MerkleBinaryTree::buildFromElements($elements);
        $tree->computeMerkleRoot();

        return $tree;
    }

    /**
     * Recursively audit an outline or section tree for recursion safety and integrity.
     *
     * @param  array<int, array<string, mixed>>  $outline
     * @return array{valid: bool, node_count: int, max_observed_depth: int, errors: array<int, string>}
     */
    public function auditOutlineRecursively(array $outline, int $maxDepth = 20): array
    {
        return $this->recursiveAuditor->auditTree($outline, 1, $maxDepth);
    }

    /**
     * Map a hierarchical outline into a dependency graph and ensure no circular cycles exist.
     *
     * @param  array<int, array<string, mixed>>  $treeNodes
     * @param  array<int, array{from: string, to: string}>  $extraDependencies
     */
    public function mapTreeToDependencyGraph(array $treeNodes, array $extraDependencies = []): DependencyGraphTreeMapper
    {
        $mapper = new DependencyGraphTreeMapper;
        $mapper->mapTreeToGraph($treeNodes);

        foreach ($extraDependencies as $edge) {
            $mapper->addDependency($edge['from'], $edge['to']);
        }

        return $mapper;
    }

    /**
     * Perform an audit across project components combining all 6 foundational CS structures.
     *
     * @return array<string, mixed>
     */
    public function auditProjectArchitecture(ResearchProject $project): array
    {
        $project->loadMissing(['documentVersions', 'citations', 'tasks', 'papers']);

        // 1. Linked List: Verify revision history chain
        $ledger = $this->buildVersionLedger($project);
        $chainStatus = $ledger->verifyChainIntegrity();

        // 2. Binary Tree: Index citations and calculate Merkle root hash
        $citationMap = [];
        foreach ($project->citations as $citation) {
            $citationMap[$citation->key] = [
                'title' => $citation->title,
                'authors' => $citation->authors,
                'year' => $citation->year,
            ];
        }
        $merkleTree = $this->buildMerkleTree($citationMap);
        $merkleRoot = $merkleTree->computeMerkleRoot();

        // 3. Recursion: Verify latest document structure / outline if present
        $latestVersion = $project->documentVersions->last();
        $rawOutline = $latestVersion?->content['outline'] ?? [
            ['id' => 'sec_1', 'title' => 'Introduction', 'children' => [
                ['id' => 'sec_1_1', 'title' => 'Background'],
                ['id' => 'sec_1_2', 'title' => 'Problem Statement'],
            ]],
            ['id' => 'sec_2', 'title' => 'Methodology'],
            ['id' => 'sec_3', 'title' => 'Results'],
        ];

        $recursionAudit = $this->recursiveAuditor->auditTree($rawOutline);

        // 4. Graph-based Tree Mapping: Map outline and task dependencies, check for cycles
        $graphMapper = new DependencyGraphTreeMapper;
        $graphMapper->mapTreeToGraph($rawOutline);

        // Add task dependencies to the graph
        foreach ($project->tasks as $task) {
            $taskId = 'task_'.$task->id;
            $graphMapper->addNode($taskId, $task->title, ['status' => $task->status]);
        }

        $hasCycle = $graphMapper->hasCycles();
        $cyclePath = $hasCycle ? $graphMapper->findCycle() : null;

        // 5. Linear Search: Scan tasks linearly for high-priority or unassigned items
        $urgentTasks = $this->linearSearcher->findAll($project->tasks->all(), function ($task): bool {
            return ($task->priority ?? '') === 'high' && ($task->status ?? '') !== 'done';
        });

        // 6. Hash: Generate cryptographic seal for the architectural state
        $coreSnapshot = [
            'project_id' => $project->id,
            'title' => $project->title,
            'version_count' => $ledger->count(),
            'citation_count' => $merkleTree->count(),
            'merkle_root' => $merkleRoot,
            'outline_node_count' => $recursionAudit['node_count'],
            'graph_node_count' => $graphMapper->nodeCount(),
            'has_graph_cycles' => $hasCycle,
            'chain_integrity' => $chainStatus['valid'],
            'audited_at' => now()->toIso8601String(),
        ];

        $appSecret = config('app.key', 'research-platform-secret');
        $sealedEnvelope = $this->hasher->seal($coreSnapshot, $appSecret);

        return [
            'project_id' => $project->id,
            'title' => $project->title,
            'structures' => [
                'linked_list' => [
                    'active' => true,
                    'node_count' => $ledger->count(),
                    'chain_valid' => $chainStatus['valid'],
                    'message' => $chainStatus['message'],
                ],
                'binary_tree' => [
                    'active' => true,
                    'indexed_citations' => $merkleTree->count(),
                    'merkle_root' => $merkleRoot,
                ],
                'recursion' => [
                    'active' => true,
                    'outline_valid' => $recursionAudit['valid'],
                    'max_observed_depth' => $recursionAudit['max_observed_depth'],
                    'total_nodes' => $recursionAudit['node_count'],
                ],
                'graph_mapping' => [
                    'active' => true,
                    'nodes' => $graphMapper->nodeCount(),
                    'has_cycles' => $hasCycle,
                    'cycle_path' => $cyclePath,
                ],
                'linear_search' => [
                    'active' => true,
                    'urgent_unresolved_tasks_count' => count($urgentTasks),
                ],
                'hash_integrity' => [
                    'active' => true,
                    'hash' => $sealedEnvelope['hash'],
                    'signature' => $sealedEnvelope['signature'],
                ],
            ],
            'architectural_integrity_passed' => $chainStatus['valid'] && ! $hasCycle && $recursionAudit['valid'],
            'sealed_envelope' => $sealedEnvelope,
        ];
    }
}
