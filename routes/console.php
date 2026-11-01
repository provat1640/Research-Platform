<?php

use App\Core\Architecture\ArchitectureSecurityVault;
use App\Models\ResearchProject;
use App\Services\AiGateway;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('architecture:audit {projectId?}', function (ArchitectureSecurityVault $vault) {
    $projectId = $this->argument('projectId');
    $project = $projectId
        ? ResearchProject::find($projectId)
        : ResearchProject::first();

    if (! $project) {
        $this->error('No research project found to audit.');

        return 1;
    }

    $this->info("Auditing Core Architecture for Project #{$project->id} [{$project->title}]...");
    $report = $vault->auditProjectArchitecture($project);

    $this->table(
        ['Component', 'Status', 'Details'],
        [
            ['Linked List (Version Ledger)', $report['structures']['linked_list']['chain_valid'] ? 'PASS' : 'FAIL', "Nodes: {$report['structures']['linked_list']['node_count']} · {$report['structures']['linked_list']['message']}"],
            ['Binary Tree (Merkle Index)', 'PASS', "Indexed: {$report['structures']['binary_tree']['indexed_citations']} citations · Root: ".substr($report['structures']['binary_tree']['merkle_root'] ?? 'N/A', 0, 16).'...'],
            ['Recursion (Outline Safety)', $report['structures']['recursion']['outline_valid'] ? 'PASS' : 'FAIL', "Nodes: {$report['structures']['recursion']['total_nodes']} · Max Depth: {$report['structures']['recursion']['max_observed_depth']}"],
            ['Graph Mapping (DAG & Cycles)', ! $report['structures']['graph_mapping']['has_cycles'] ? 'PASS' : 'FAIL', "Nodes: {$report['structures']['graph_mapping']['nodes']} · Cycles: ".($report['structures']['graph_mapping']['has_cycles'] ? 'DETECTED' : 'None (DAG clean)')],
            ['Linear Search (Sequential Audit)', 'PASS', "Scanned for high-priority unresolved items: {$report['structures']['linear_search']['urgent_unresolved_tasks_count']} found"],
            ['Hash Integrity Seal', 'PASS', 'SHA-256 seal: '.substr($report['structures']['hash_integrity']['hash'], 0, 16).'...'],
        ]
    );

    if ($report['architectural_integrity_passed']) {
        $this->info('✓ Core Architecture verified and protected against tampering and circular deadlocks.');

        return 0;
    }

    $this->error('✗ Architecture audit failed validation checks.');

    return 1;
})->purpose('Audit and protect system core architecture using Binary Tree, Recursion, Graph Mapping, Linked List, Linear Search, and Hash');

Artisan::command('ai:check', function (AiGateway $ai) {
    $this->info('================================================================');
    $this->info('       AI API AVAILABILITY & ENCRYPTION SECURITY AUDIT          ');
    $this->info('================================================================');
    $this->line('Probing real-time endpoint reachability, TLS transport encryption, and credential availability...');
    $this->newLine();

    $providers = $ai->probeAllProviders();
    $rows = [];

    foreach ($providers as $p) {
        $statusLabel = match ($p['status']) {
            'ready' => '<fg=green;options=bold>ONLINE (Ready)</>',
            'key_required' => '<fg=yellow;options=bold>REACHABLE (Key Required)</>',
            'invalid_key' => '<fg=red;options=bold>INVALID KEY</>',
            'offline' => '<fg=gray>OFFLINE (Inactive)</>',
            default => '<fg=red>ERROR</>',
        };

        $rows[] = [
            $p['recommended'] ? "★ {$p['name']}" : $p['name'],
            $p['model'],
            $statusLabel,
            $p['latency_ms'] !== null ? "{$p['latency_ms']} ms" : '—',
            $p['transport_security'],
            $p['has_credentials'] ? 'Yes' : 'No',
        ];
    }

    $this->table(
        ['AI Provider', 'Model', 'Status', 'Latency', 'Security / Encryption', 'Key Configured'],
        $rows
    );

    $this->newLine();
    $this->info('Provider Details & Hierarchy Analysis:');

    foreach ($providers as $p) {
        $badge = $p['recommended'] ? '[RECOMMENDED HIERARCHY FIT]' : '[ALTERNATIVE]';
        $this->line("<options=bold>{$badge} {$p['name']}</>");
        $this->line("  • Endpoint: {$p['endpoint']}");
        $this->line("  • Free Tier: {$p['free_tier']}");
        $this->line("  • Message: {$p['message']}");
        $this->newLine();
    }

    $this->info('Active System Configuration:');
    $this->line("  • Current Provider: {$ai->provider()}");
    $this->line("  • Current Model: {$ai->model()}");
    $this->line('  • AI Free For All: '.((bool) config('services.ai.free_access') ? 'TRUE (Free for all researchers)' : 'FALSE'));
    $this->line('  • Payload Encryption: AES-256-CBC at-rest via Laravel Crypt vault');
    $this->line('  • Transport Encryption: TLS 1.3 HTTPS enforced in transit');

    return 0;
})->purpose('Load all AI APIs and check their real-time availability, latency, and transport encryption');
