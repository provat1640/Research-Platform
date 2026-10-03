# Co-Auth MCP: Full-Stack Enterprise System Specification & Blueprint Manual
**Document Reference ID:** SPEC-2026-M11-LAREV  
**Core Infrastructure Blueprint Layout Target:** University Engineering Thesis Integration  
**Author Core Alignment:** Full-Stack System Architecture Division

---

## Section 1: Executive Abstract & Foundational Architecture

Modern academic research workflows remain profoundly isolated. Software engineering students, doctoral candidates, and academic evaluators operate within a fragmented landscape of single-user rich text files, disconnected code suites, standalone reference databases (e.g., Zotero), and contemporary large language models. AI interfaces operate blindly within browser windows, entirely stripped of underlying database schemas, local document states, and structural tracking logs. This semantic vacuum frequently causes hallucinations, faulty evaluation test cases, and compromises the traceability of academic papers.

**Co-Auth MCP** resolves this paradigm mismatch by implementing an enterprise-grade web application platform that bridges collaborative authoring pipelines directly with context-aware AI engines using the **Model Context Protocol (MCP)**. Built on a performance-optimized **PHP 8.3 / Laravel 11.x** application backend, a cascading **MySQL 8.0** data volume layer, and real-time **Laravel Reverb WebSockets**, the platform functions explicitly as an active *MCP Client*. The architecture converts localized file systems, relational indexing rules, and institutional metrics into query context fields directly consumable by secure, protocol-linked artificial intelligence agents.

---

## Section 2: Complete Relational Schema Specifications (MySQL Blueprint migrations)

To secure deterministic execution structures across all team deployment stations, developers must initialize database blueprints using isolated, transactional migration classes. The following schemas define structural constraints, cascade deletion parameters, fulltext indexing parameters, and dynamic JSON schema boundaries.

### 2.1 File: `2026_10_01_000001_create_users_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the user profile migration schema.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('orcid_id')->nullable()->unique();
            $table->boolean('is_teacher')->default(false);
            $table->timestamp('trial_ends_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### 2.2 File: `2026_10_01_000002_create_papers_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the manuscript tracking schema migration with native FULLTEXT search capabilities.
     */
    public function up(): void
    {
        Schema::create('papers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('abstract');
            $table->enum('status', ['draft', 'under_review', 'published'])->default('draft');
            $table->timestamps();
        });

        // Inject the native MySQL FULLTEXT Index descriptor manually
        DB::statement('ALTER TABLE papers ADD FULLTEXT fulltext_title_abstract (title, abstract)');
    }

    public function down(): void
    {
        Schema::dropIfExists('papers');
    }
};
```

### 2.3 File: `2026_10_01_000003_create_document_versions_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run version mapping to store block-based TipTap editor output arrays.
     */
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_id')->constrained()->onDelete('cascade');
            $table->json('content_blocks'); // Preserves structural JSON states from Editor interface
            $table->integer('version_sequence')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
```

---

## Section 3: Data Object Modeling & Structural Business Logic Mapping

Data modeling configurations apply strict data type filters and instantiate mapping logic between users, academic papers, tracking nodes, and internal evaluation parameters.

### 3.1 File: `app/Models/User.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Billable;

    protected $fillable = [
        'name',
        'email',
        'orcid_id',
        'is_teacher',
        'trial_ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'is_teacher' => 'boolean',
    ];

    public function papers(): HasMany
    {
        return $this->hasMany(Paper::class);
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }
}
```

### 3.2 File: `app/Models/Paper.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paper extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'abstract',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }
}
```

---

## Section 4: Security Access Layer Fences (Trial Verification Middleware)

The `CheckAiAccess` interceptor executes runtime checks on every incoming request bound for secure MCP tools. It checks the active user session against database-driven subscription logs managed by Laravel Cashier.

### 4.1 File: `app/Http/Middleware/CheckAiAccess.php`
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAiAccess
{
    /**
     * Intercept and validate AI resource routing footprints.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthenticated profile context.'], 401);
        }

        // Validate access criteria: Active 7-day trial token or valid premium stripe profile
        if ($user->onTrial() || $user->subscribed('premium_ai_tier')) {
            return $next($request);
        }

        return response()->json([
            'status' => 'access_denied',
            'error' => 'Your 7-day AI evaluation trial has expired. Upgrade your profile tier to restore context tool invocations.'
        ], 403);
    }
}
```

---

## Section 5: Model Context Protocol (MCP) Core Controller Execution Engines

This component houses the core business logic. It handles the initial semantic uniqueness evaluation using native database indexing and encapsulates the JSON-RPC communication structures deployed over the wire to connect external models.

### 5.1 File: `app/Http/Controllers/PaperController.php`
```php
<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class PaperController extends Controller
{
    /**
     * Evaluate and store a new manuscript workspace while checking semantic differentiation limits.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'required|string|min:100',
        ]);

        // Query the FULLTEXT block index for semantic overlap thresholds
        $overlappingMatch = DB::table('papers')
            ->select('id', 'title')
            ->whereRaw("MATCH(title, abstract) AGAINST(? IN NATURAL LANGUAGE MODE)", [$request->abstract])
            ->first();

        if ($overlappingMatch) {
            return response()->json([
                'status' => 'rejected',
                'message' => 'Topic duplication exception: The submitted abstract text parameters align heavily with prior institutional research indices.',
                'conflicting_record_id' => $overlappingMatch->id
            ], 422);
        }

        $paper = Paper::create([
            'user_id' => auth()->id() ?? 1,
            'title' => $request->title,
            'abstract' => $request->abstract,
            'status' => 'draft'
        ]);

        return response()->json([
            'status' => 'approved',
            'message' => 'Manuscript workspace initialized successfully.',
            'paper_id' => $paper->id
        ], 201);
    }
}
```

### 5.2 File: `app/Services/McpClientService.php`
```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class McpClientService
{
    protected string $mcpServerRpcEndpoint;

    public function __construct()
    {
        $this->mcpServerRpcEndpoint = config('services.mcp.endpoint', 'http://127.0.0.1:8000');
    }

    /**
     * Package context structures into a formal JSON-RPC 2.0 tool invocation block frame.
     */
    public function callMcpTool(string $toolName, array $arguments, int $userId): array
    {
        $rpcPayload = [
            'jsonrpc' => '2.0',
            'method'  => 'tools/call',
            'params'  => [
                'name' => $toolName,
                'arguments' => array_merge($arguments, ['executing_user_id' => $userId])
            ],
            'id' => (int) time()
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                            ->timeout(15)
                            ->post($this->mcpServerRpcEndpoint . '/rpc', $rpcPayload);

            if ($response->failed()) {
                return ['error' => 'The upstream MCP server channel returned a network communication fault code.'];
            }

            return $response->json();
        } catch (\Exception $e) {
            return ['error' => 'Internal MCP transport abstraction error triggered: ' . $e->getMessage()];
        }
    }
}
```

---

## Section 6: Real-Time Sockets & Frontend Client Synchronization Architecture

Frontend frameworks establish direct background listening pipes via Laravel Echo over Reverb container nodes, receiving multi-user editing streams instantly without layout shifting elements.

### 6.1 File: `resources/js/components/research-editor.js`
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export class CoAuthWorkspaceEngine {
    /**
     * Initialize connection contexts for the 3-pane real-time layout workspace.
     */
    constructor(paperId, wsAuthToken) {
        this.paperId = paperId;
        this.authToken = wsAuthToken;
        this.connectReverbSocketCluster();
    }

    connectReverbSocketCluster() {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: import.meta.env.VITE_REVERB_APP_KEY,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT,
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    'Authorization': `Bearer ${this.authToken}`
                }
            }
        });

        // Initialize listener on the isolated private document channel cluster
        window.Echo.private(`private-papers.${this.paperId}`)
            .listen('DocumentEdited', (payload) => {
                this.injectLiveContentBlock(payload.contentBlocks);
            })
            .listen('PeerReviewGenerated', (payload) => {
                this.renderLiveTeacherFeedbackPanel(payload.score, payload.comments);
            });
    }

    injectLiveContentBlock(contentBlocks) {
        const editorViewPane = document.getElementById('editor-canvas');
        if (editorViewPane && contentBlocks.html_payload) {
            // Apply atomic DOM replacement updates safely
            editorViewPane.innerHTML = contentBlocks.html_payload;
        }
    }

    renderLiveTeacherFeedbackPanel(score, comments) {
        const scoreWidget = document.getElementById('ui-teacher-score');
        const commentWidget = document.getElementById('ui-teacher-comments');

        if (scoreWidget) scoreWidget.innerText = `Score: ${score}/5`;
        if (commentWidget) commentWidget.innerText = comments;
    }
}
```

---

## Section 7: Quality Assurance Automation Framework (Integrated Feature Tests)

These programmatic assessment configurations guarantee that all database tracking rules, fulltext restrictions, and trial access restrictions operate cleanly under continuous integration testing routines.

### 7.1 File: `tests/Feature/CoAuthValidationSuite.php`
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Paper;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CoAuthValidationSuite extends TestCase
{
    use RefreshDatabase;

    /**
     * Verify that the custom middleware layers block expired accounts.
     */
    public function test_access_gate_blocks_expired_trial_footprints(): void
    {
        $expiredUser = User::factory()->create([
            'trial_ends_at' => now()->subMinutes(5)
        ]);

        $response = $this->actingAs($expiredUser)
                         ->postJson('/api/v1/mcp/invoke-tool', [
                             'tool_name' => 'extract_citation_context',
                             'arguments' => ['query' => 'machine learning']
                         ]);

        $response->assertStatus(403)
                 ->assertJsonFragment(['status' => 'access_denied']);
    }

    /**
     * Assert that the semantic uniqueness controller blocks matching abstract records.
     */
    public function test_semantic_validator_flags_and_blocks_duplicate_research_topics(): void
    {
        // Populate database with baseline target research
        Paper::create([
            'user_id' => 1,
            'title' => 'Relational Sync Engine Base',
            'abstract' => 'This study analyzes a high density dataset query processing block that maps nodes cleanly across distributed server infrastructure.',
            'status' => 'draft'
        ]);

        $activeUser = User::factory()->create(['trial_ends_at' => now()->addDays(2)]);

        // Post highly overlapping textual abstract payload signatures
        $response = $this->actingAs($activeUser)
                         ->postJson('/api/v1/papers', [
                             'title' => 'Alternative Research Document Mapping title',
                             'abstract' => 'This study analyzes a high density dataset query processing block that maps nodes cleanly across distributed server infrastructure.'
                         ]);

        $response->assertStatus(422)
                 ->assertJsonFragment(['status' => 'rejected']);
    }
}
```

---

## Section 8: Environment Dockerization & CI Run Specifications

Professional teams avoid local computer platform runtime configuration discrepancies by packaging execution stacks within Docker environments.

### 8.1 File: `docker-compose.yml`
```yaml
version: '3.8'
services:
  laravel.test:
    build:
      context: ./vendor/laravel/sail/runtimes/8.3
      dockerfile: Dockerfile
    ports:
      - '${APP_PORT:-80}:80'
      - '${VITE_PORT:-5173}:5173'
    environment:
      WWWUSER: '${WWWUSER}'
      LARAVEL_SAIL: 1
    volumes:
      - '.:/var/www/html'
    networks:
      - coauth_network
  mysql:
    image: 'mysql:8.0'
    ports:
      - '${FORWARD_DB_PORT:-3306}:3306'
    environment:
      MYSQL_ROOT_PASSWORD: '${DB_PASSWORD}'
      MYSQL_DATABASE: '${DB_DATABASE}'
    volumes:
      - 'coauth_mysql_volume:/var/lib/mysql'
    networks:
      - coauth_network
networks:
  coauth_network:
    driver: bridge
volumes:
  coauth_mysql_volume:
    driver: local
```

### 8.2 File: `.github/workflows/laravel-ci.yml`
```yaml
name: Full-Stack Integration Pipeline

on:
  push:
    branches: [ "main" ]
  pull_request:
    branches: [ "main" ]

jobs:
  execute-test-matrix:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ALLOW_EMPTY_PASSWORD: yes
          MYSQL_DATABASE: coauth_test
        ports:
          - 3306:3306

    steps:
    - uses: actions/checkout@v4
    
    - name: Initialize PHP Development Framework Environment
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.3'
        extensions: mbstring, mysql, xml, ctype
        
    - name: Pull Dependent Assets
      run: |
        composer install --prefer-dist --no-progress
        npm install
        npm run build
        
    - name: Run Validation Suite
      run: php artisan test --env=testing
```
