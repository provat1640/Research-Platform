# Project Proposal: Co-Auth
## Collaborative Academic Research Studio & Model Context Protocol (MCP) Platform

---

### Project Metadata & Administrative Information

| Parameter | Specification |
| :--- | :--- |
| **Project Title** | **Co-Auth: Collaborative Academic Research Studio & MCP Platform** |
| **Application Domain** | Higher Education, Academic Research, Software Engineering & Thesis Publication |
| **Core Architecture** | Model Context Protocol (MCP) Client-Server Architecture with WebSocket Synchronization |
| **Target Document Type** | Master's Thesis, Doctoral Dissertation, Undergraduate Capstone, Scientific Journal Article |
| **Framework & Language** | [Laravel 13.x](file:///c:/Users/Lenovo/research-platform/composer.json) · [PHP 8.4+](file:///c:/Users/Lenovo/research-platform/composer.json) · Vanilla Modern JS (ES2024) |
| **Real-time Engine** | [Laravel Reverb](file:///c:/Users/Lenovo/research-platform/config/reverb.php) (Event-Driven WebSockets on Port 8080) |
| **Automated Verification** | 53 Passing Feature Tests ([PlaybookMatrixTest](file:///c:/Users/Lenovo/research-platform/tests/Feature/PlaybookMatrixTest.php)) · PSR-12 ([Laravel Pint](file:///c:/Users/Lenovo/research-platform/pint.json)) |

---

## 1. Introduction

### 1.1 Background Overview
Academic research and scientific writing are foundational to technological innovation, evidence-based policy, and scholarly progress. In universities and research institutes worldwide, graduate researchers, doctoral candidates, and undergraduate capstone students spend hundreds of hours formulating research questions, selecting epistemological frameworks, gathering empirical literature, conducting experiments, and drafting formal manuscripts under the supervision of faculty advisors.

#### Current Practices and Existing Systems
The prevailing workflow in academic research is heavily fragmented across isolated, non-interoperable tools:
- **General-Purpose Document Processors:** Tools such as Microsoft Word and Google Docs serve as the de facto drafting environment. While effective for generic business documents, they lack native academic scaffolding—such as live margin toggling (0.5" to 1.25"), single vs. dual-column IEEE/ACM layouts, running heads, and automated methodology compliance checks.
- **Standalone Reference Managers:** Reference software such as Zotero, Mendeley, and EndNote operate completely outside the writing canvas. Researchers manually copy and paste citation keys or rely on brittle browser plugins that often corrupt formatting during collaborative merges.
- **Asynchronous Revision Loops:** Feedback exchange between supervisors and students is primarily conducted through email attachments containing marked-up `.docx` or `.pdf` files. This creates chaotic version sprawl (`thesis_final_v2_supervisor_edits.docx`), lost comment histories, and mutual write-collision when multiple co-authors work concurrently.

#### The Current State of AI in the Academic Domain
Over the past three years, Generative Artificial Intelligence (GenAI) and Large Language Models (LLMs) like OpenAI ChatGPT, Anthropic Claude, and Google Gemini have been widely adopted by students and researchers for brainstorming, literature drafting, and grammar proofreading. However, existing AI deployments in academia suffer from a critical limitation known as the **"Semantic Vacuum"**:
- **Disconnection from Project State:** Contemporary AI interfaces exist as standalone web chatbots or generic editor sidecars. They possess zero programmatic visibility into the student's underlying database, active thesis research questions, selected epistemological methodology, pending supervisor notes, or cited bibliographic records.
- **Severe Hallucination Risks:** When asked to summarize or provide literature guidance, LLMs frequently fabricate non-existent citations, generate spurious DOIs, or recommend methodologies ontologically incompatible with the research question.
- **Superficial Integration:** Current academic writing systems either treat AI as a complete afterthought (a floating chatbot iframe) or rely on brittle copy-paste prompt engineering that risks leaking unpublished proprietary research data.

---

### 1.2 Problem Statement
Modern academic research pipelines lack a unified, context-aware environment that aligns collaborative drafting with verifiable, hallucination-resistant artificial intelligence.

#### Gaps and Limitations in Existing Systems
1. **The Semantic Disconnect:** AI models cannot autonomously inspect the active project state (e.g., whether a study is grounded in Positivism, Constructivism, or Design Science), leading to generic, irrelevant, or contradictory methodological advice.
2. **Citation Fabrication & Invalidation:** Generic LLMs invent citations that appear plausible but do not exist in academic databases, compromising research integrity.
3. **Collaborative Version Collisions:** Small research teams (2–3 co-authors plus a thesis advisor) working on empirical manuscripts lack conflict-free live presence and immutable audit trails within standard word processors.
4. **Epistemological Ambiguity:** Students frequently struggle to distinguish between ontological assumptions (e.g., Lincoln & Guba trustworthiness criteria in Qualitative research vs. statistical p-values in Quantitative research), leading to severe methodology rejections during thesis defense.

#### Affected Stakeholders and Need for a Software Solution
This problem directly impedes graduate and doctoral students, university faculty advisors, independent co-authors, and academic department review boards. A specialized software solution is urgently needed to consolidate the writing surface, real-time collaboration, citation management, and AI assistance into a single deterministic architecture.

#### Why AI Is Essential (Manual Work Replaced)
Today, researchers must manually:
- Cross-reference whether their draft sections align with their stated research questions and epistemological stances.
- Audit drafts against open supervisor feedback items scattered across long email threads.
- Synthesize unstructured test logs, empirical benchmarks, and qualitative coding transcripts into structured evaluation reports.
- Verify whether cited sources in the prose correspond to valid entries in their bibliography.

Co-Auth delegates this cognitive reconciliation to an AI assistant that communicates through the **Model Context Protocol (MCP)**, querying the relational database directly to provide deterministic, grounded academic synthesis without human intervention.

---

### 1.3 Objectives

#### General Objective
To design, implement, and validate **Co-Auth**, an enterprise-grade collaborative research studio that unifies authentic physical academic paper typesetting, in-context citation indexing, and real-time multi-user synchronization with context-grounded AI engines.

#### AI Objective
To establish a secure, protocol-driven AI integration using Anthropic's **Model Context Protocol (MCP)** via a dedicated `/mcp/research` server and `ProjectContextTool`. The AI component will:
1. Autonomously ingest verified project metadata (research questions, epistemological methodology, open supervisor feedback, active tasks, citation registry, and empirical figures).
2. Synthesize project state summaries and propose prioritized next academic actions without inventing facts or sources.
3. Evaluate structured empirical test data against research hypotheses, outputting deterministic JSON performance scores and threats-to-validity assessments.

---

### 1.4 Scope

#### In-Scope Features
- **Authentic Paper Sheet Studio:** Single- and dual-column layouts (IEEE/ACM conference standard), customizable margins (0.5", 0.75", 1.0", 1.25"), academic typefaces (Serif, Sans, Mono), line spacing (1.0 to 2.0), and running headers/footers.
- **Epistemological Methodology Theory Engine:** Interactive paradigm card and comparative guide differentiating 6 core frameworks (Qualitative, Quantitative, Mixed Methods, Design Science, PRISMA Literature Review, Action Research).
- **In-Context Citations & BibTeX Pipeline:** In-text citation insertion (`[1]`, `[2]`), real-time citation filtering, automatic APA/IEEE bibliography compiler, and standard `.bib` export.
- **Visual Evidence & Figure Gallery:** Sequential `Figure X` numbering, academic captions, multi-source ingestion (file upload and image URLs), and storage linking.
- **Real-Time Collaboration & Presence:** [Laravel Reverb](file:///c:/Users/Lenovo/research-platform/config/reverb.php) WebSocket broadcasting, live editor section banners, collaborator role invites (`student`, `co-author`, `supervisor`, `reviewer`), and immutable version snapshots (`DocumentVersion`).
- **Context-Grounded AI via MCP:** Dedicated `/mcp/research` JSON-RPC endpoint implementing `ProjectContextTool` for external LLMs (Claude, Cursor, Gemini), plus in-app AI synthesis endpoints.
- **Export & Typesetting:** Real-time academic metrics (word/character count, 220 wpm reading time), clean GitHub-flavored Markdown export, and print/PDF stylesheets.
- **Security & Governance:** Strict ORCID registration validation (`^\d{4}-\d{4}-\d{4}-[\dX]{4}$`), project-level tenant isolation, and 7-day evaluation trial verification via Cashier.

#### Out-of-Scope Features
- Complete client-side WYSIWYG LaTeX compiler (the system exports to `.bib` and clean Markdown for LaTeX/Overleaf ingestion).
- Automated plagiarism similarity indexing against external proprietary paywalled databases (e.g., Turnitin).
- Commercial credit card transaction processing in local test environments (handled via Cashier mock states).
- Direct submission API integration to external journal publication portals (e.g., Elsevier Editorial Manager).

#### What the AI Will Do vs. What the AI Will Not Do
| AI Capability (In-Scope) | Boundary / Limitation (Out-of-Scope) |
| :--- | :--- |
| Ingest live project relational context via MCP tools. | The AI will **not** browse unvetted external web search engines during context calls. |
| Summarize project health and highlight unresolved supervisor feedback. | The AI will **not** fabricate citations, empirical findings, or authors. |
| Evaluate structured experimental test data arrays in JSON format. | The AI will **not** execute arbitrary PHP, SQL, shell, or Python code. |
| Provide methodological recommendations aligned with epistemological theories. | The AI will **not** ghostwrite entire manuscripts without human supervision. |

---

### 1.5 Stakeholders

| Stakeholder | Category | Role in the System | Core Interest & Value |
| :--- | :--- | :--- | :--- |
| **Student / Primary Author** | End User | Drafts manuscript, manages bibliography, uploads empirical figures, configures methodology. | Eliminates formatting frustration, ensures alignment with advisor expectations, prevents citation errors. |
| **Thesis Advisor / Supervisor** | Client & Evaluator | Inspects drafts, leaves structured feedback notes, tracks milestone progress, validates methodology. | Transparent feedback loop, no messy email threads, instant visibility into student progress. |
| **Co-Author / Research Partner** | End User | Collaborates on sections in real time, uploads figures, manages task board items. | Conflict-free simultaneous editing, shared citation library, synchronized working directory. |
| **System Administrator** | Technical Lead | Deploys Docker containers, monitors Reverb WebSocket health, configures database migrations and AI keys. | Low maintenance overhead, robust test coverage (53 tests), automated single-command setup. |
| **Institutional IRB / Ethics Board** | Regulatory Body | Audits institutional research ethics status recorded in project metadata. | Transparent tracking of ethical review status (`not_assessed`, `submitted`, `approved`). |

---

### 1.6 Proposed Solution

#### Solution Overview
Co-Auth addresses academic research fragmentation by providing a unified web platform that integrates an authentic, physical paper canvas with real-time multi-user synchronization and a **Model Context Protocol (MCP)** server.

#### Overall Workflow
1. **Project Initiation & Identity Verification:** The researcher authenticates with their verified ORCID ID and initializes a research project with a research question, discipline, and epistemological stance.
2. **Methodological Calibration:** The researcher selects a methodology (e.g., Design Science or Qualitative); the interactive theory engine instantly configures ontology, data collection types, and academic rigor criteria.
3. **Authentic Paper Typesetting:** The researcher selects a template (IMRaD, IEEE/ACM, PRISMA, etc.) and writes in a calibrated paper sheet with configurable margins and dual-column rendering.
4. **Evidence & Citation Integration:** Figures are embedded with sequential academic attribution (`Figure 1: ...`), and references are inserted as in-text chips (`[1]`) backed by a live searchable bibliography.
5. **Real-Time Collaboration & Review:** Co-authors and supervisors edit simultaneously. Laravel Reverb broadcasts presence heartbeats, while supervisors leave inline feedback in a dedicated desk.
6. **Context-Grounded AI Assistance:** Authorized AI agents query `/mcp/research` via `ProjectContextTool` to extract relational project state, generating grounded summaries and test evaluations with zero hallucinations.

#### System Overview Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          Client Browser Layer                               │
│  ┌───────────────────────────────────────────────────────────────────────┐  │
│  │   Authentic Academic Paper Studio (resources/views/project-workspace) │  │
│  │   - Multi-Pane Layouts (Studio, Focus Zen, Reviewer Split)            │  │
│  │   - Real-Time Presence & Editor Heartbeats (workspace.js)             │  │
│  │   - In-Context Citations & Visual Evidence Gallery                    │  │
│  └───────────────────▲───────────────────────────────▲───────────────────┘  │
└──────────────────────┼───────────────────────────────┼──────────────────────┘
                       │ REST API (JSON)               │ Echo / WebSockets
                       ▼                               ▼
┌──────────────────────────────────────────┐ ┌────────────────────────────────┐
│      Laravel 13 Application Backend      │ │  Laravel Reverb Sockets Cluster│
│  - Middleware: EnsureProjectMember       │ │  - Private Document Channels  │
│  - Middleware: CheckAiAccess (Trial Gate)│ │  - Broadcasts (Port 8080)      │
│  - Controllers: ResearchProject, Citations│ └────────────────────────────────┘
└──────────────────────┬───────────────────┘
                       │
       ┌───────────────┴───────────────┐
       ▼                               ▼
┌──────────────────────────────┐ ┌────────────────────────────────────────────┐
│   Relational Database Layer  │ │  ★ MODEL CONTEXT PROTOCOL (MCP) AI CORE ★  │
│  - Users (ORCID Enforced)    │ │                                            │
│  - ResearchProjects & Papers │ │  Endpoint: POST /mcp/research              │
│  - Citations & ProjectMedia  │ │  Server: ResearchServer                    │
│  - DocumentVersions (Audits) │ │  Tool: ProjectContextTool                  │
│  - ProjectTasks & Feedback   │ │  AiController: summarize() & evaluate()    │
└──────────────────────────────┘ └──────────────────────▲─────────────────────┘
                                                        │ JSON-RPC 2.0
                                                        ▼
                                         ┌────────────────────────────────────┐
                                         │  External AI Agents / LLM Engines  │
                                         │  (Anthropic Claude, Gemini, Cursor)│
                                         └────────────────────────────────────┘
```

---

### 1.7 AI Features

#### Existing AI Features in Current Tools & Their Limitations
- **Generic Chatbot Interfaces (ChatGPT, Copilot):** Lack connection to the user's local database or project file system. Users must manually copy-paste abstracts and supervisor notes into prompts, frequently exceeding token limits or exposing sensitive empirical findings.
- **Generic Autocompletion (Grammarly, Word Copilot):** Operates at the sentence level without understanding research paradigms, leading to text suggestions that violate academic methodology standards (e.g., introducing statistical language into an interpretivist qualitative study).

#### Proposed AI Features in Co-Auth

| Feature Name | Input Data | Output Data | Manual Work Replaced |
| :--- | :--- | :--- | :--- |
| **Structured Project Context Extraction (`ProjectContextTool`)** | Project ID + Authenticated MCP Session Token | Structured JSON containing research questions, methodology, members, open tasks, unaddressed supervisor notes, citations, and figures | Replaces manual prompt drafting, manual context collation, and eliminates information omission. |
| **Context-Grounded Project Summarizer** | Relational state + user instruction | Concise, actionable academic synthesis and the next 3 prioritized research actions | Replaces manual review of disparate task boards, sticky notes, and email chains. |
| **Deterministic Empirical Evaluator** | Experimental test data array + evaluation parameters | Validated JSON report with pass/fail status, numerical score (1–5), empirical findings, and threats to validity | Replaces hours of manual spreadsheet benchmarking and subjective qualitative scoring. |
| **Semantic Uniqueness Gatekeeper** | Manuscript title + abstract text | MySQL FULLTEXT natural language similarity score and duplicate conflict identification | Replaces manual literature search to verify whether a thesis topic duplicates existing department studies. |

#### AI Techniques Applied
1. **Model Context Protocol (MCP):** Implementation of Anthropic's open MCP standard via `Laravel\Mcp`, enabling AI models to autonomously invoke database inspection tools over HTTP JSON-RPC 2.0.
2. **Context-Grounded Zero-Shot System Prompting:** System-level constraints enforcing academic rigor (`"Do not invent sources, findings, or citations. Return concise, actionable academic guidance."`).
3. **Structured Test Data Evaluation:** Programmatic JSON schema validation evaluating empirical arrays without arbitrary code execution.
4. **Native Natural Language Fulltext Indexing:** Database-level natural language matching across paper titles and abstracts (`MATCH(title, abstract) AGAINST(? IN NATURAL LANGUAGE MODE)`).

#### Why AI Is Essential to the System
AI is not a cosmetic chatbot add-on in Co-Auth; **it is the architectural bridge of the entire research studio**. If the AI and MCP layers were removed:
- External LLMs would have zero mechanism to inspect the project's methodology, supervisor notes, or citations.
- The platform would devolve into a basic text editor with no ability to evaluate empirical test arrays or provide grounded methodological guidance.
- Researchers would be forced back into the manual, error-prone cycle of copying unstructured context into public AI windows, recreating the hallucination crisis the platform was specifically built to eliminate.

---

## 2. System Requirements

### 2.1 Functional Requirements (Core Platform)
- **FR-01:** The system shall enforce registration with a valid, unique ORCID identifier adhering to the regex `^\d{4}-\d{4}-\d{4}-[\dX]{4}$`.
- **FR-02:** The system shall allow authenticated researchers to create, read, update, and manage research projects containing titles, disciplines, descriptions, research questions, and expected outcomes.
- **FR-03:** The system shall restrict all project-level reads and modifications to verified project owners and authorized collaborators through [EnsureProjectMember](file:///c:/Users/Lenovo/research-platform/app/Http/Middleware/EnsureProjectMember.php) middleware.
- **FR-04:** The system shall provide an interactive methodology theory differentiation guide covering Qualitative, Quantitative, Mixed Methods, Design Science, PRISMA Literature Review, and Action Research paradigms.
- **FR-05:** The system shall provide an authentic paper sheet editor supporting single-column and dual-column layouts, customizable margins (0.5" to 1.25"), academic typefaces, and line spacing presets.
- **FR-06:** The system shall allow users to insert in-text citation chips at the cursor position and dynamically compile an APA/IEEE-formatted bibliography.
- **FR-07:** The system shall provide an endpoint to export the project citation library as a valid BibTeX (`.bib`) file.
- **FR-08:** The system shall support image uploads and public URL figure insertions with sequential `Figure X` numbering and captions.
- **FR-09:** The system shall create an immutable [DocumentVersion](file:///c:/Users/Lenovo/research-platform/app/Models/DocumentVersion.php) record upon each manuscript save, capturing content, author, and timestamp.
- **FR-10:** The system shall broadcast active editing presence and section heartbeats across all connected collaborators using Laravel Reverb WebSockets.
- **FR-11:** The system shall provide a shared task board and supervisor feedback desk for real-time review exchanges.

### 2.2 Functional Requirements (AI & MCP Components)
- **FR-AI-01:** The system shall expose a secure Model Context Protocol (MCP) server at `/mcp/research` guarded by [EnsureMcpAccess](file:///c:/Users/Lenovo/research-platform/app/Http/Middleware/EnsureMcpAccess.php).
- **FR-AI-02:** The system shall provide a `ProjectContextTool` exposing structured project data (questions, methodology, tasks, feedback, papers, citations, media) to authorized MCP clients.
- **FR-AI-03:** The system shall verify active evaluation trial status or subscription tier via [CheckAiAccess](file:///c:/Users/Lenovo/research-platform/app/Http/Middleware/CheckAiAccess.php) prior to executing AI invocations.
- **FR-AI-04:** The system shall provide a project summarization endpoint that returns grounded, non-hallucinatory academic next steps.
- **FR-AI-05:** The system shall evaluate structured empirical test arrays, returning validated JSON containing scores (1–5), findings, and threats to validity.
- **FR-AI-06:** The system shall reject duplicate manuscript abstracts utilizing database FULLTEXT natural language matching.

### 2.3 Non-Functional Requirements

#### Performance
- **NFR-P1 (Response Time):** Standard REST API endpoints shall respond within 200 milliseconds under normal server load.
- **NFR-P2 (WebSocket Latency):** Live presence updates and version broadcasts via Laravel Reverb shall propagate to clients within 100 milliseconds.
- **NFR-P3 (Query Optimization):** Project listings and workspace dashboards shall eager-load relationships, preventing N+1 query performance degradation.

#### Security & Tenant Isolation
- **NFR-S1 (Authentication):** Unauthenticated requests to protected API endpoints shall immediately terminate with HTTP 401.
- **NFR-S2 (Access Control):** Non-members attempting to access a research project shall receive HTTP 403 or 404.
- **NFR-S3 (Credential Masking):** Integration health checks shall report connection status (`ready`, `unreachable`) without ever exposing API keys, secrets, or bearer tokens.
- **NFR-S4 (Output Sanitization):** Model outputs shall be treated strictly as untrusted text and sanitized against Cross-Site Scripting (XSS).

#### Reliability & Usability
- **NFR-R1 (Graceful Degradation):** If the external AI provider is unreachable or times out, the system shall return an HTTP 503 error without crashing local manuscript drafting or document saving.
- **NFR-U1 (Responsive Design):** The workspace interface shall adapt cleanly across desktop workstation displays, laptops, and tablet viewports.
- **NFR-U2 (Print Fidelity):** The system shall implement dedicated `@media print` rules ensuring clean rendering to standard A4 or US Letter paper formats.

### 2.4 Auxiliary Cases (Secondary & Exceptional Scenarios)

In addition to the primary happy-path workflows, Co-Auth handles auxiliary use cases and edge conditions essential for enterprise research continuity, data integrity, and compliance:

- **AC-01 (ORCID Validation Mismatch & Duplicate Recovery):**
  - *Trigger:* A researcher attempts to register with an invalid ORCID string or an ORCID already bound to another profile.
  - *System Response:* `AuthController::storeRegistration()` intercepts the input with regular expression pattern matching (`^\d{4}-\d{4}-\d{4}-[\dX]{4}$`). If invalid or duplicated, the registration halts with user-friendly inline error feedback, preserving form fields and preventing duplicate or orphaned academic identities.
- **AC-02 (WebSocket Disconnection & Automatic State Re-synchronization):**
  - *Trigger:* Transient network drop, client Wi-Fi switch, or temporary restart of the [Laravel Reverb](file:///c:/Users/Lenovo/research-platform/config/reverb.php) WebSocket daemon.
  - *System Response:* The client-side listener in [workspace.js](file:///c:/Users/Lenovo/research-platform/resources/js/workspace.js) implements exponential backoff reconnection to private channel `project.{id}`. Local editor buffer retains unsaved typing in memory; upon socket restoration, presence indicators and active section locks seamlessly re-sync without data loss or page refreshes.
- **AC-03 (Trial Expiration & AI Access Gate Enforcement):**
  - *Trigger:* A user whose 7-day evaluation window has elapsed (`trial_ends_at < now()`) attempts to invoke AI summarization, test evaluation, or Model Context Protocol tools.
  - *System Response:* The [CheckAiAccess](file:///c:/Users/Lenovo/research-platform/app/Http/Middleware/CheckAiAccess.php) middleware halts execution, returning HTTP 403 Forbidden with `{ "error": "Evaluation trial expired" }`. The system displays an upgrade prompt while preserving complete, unhindered access to the core paper editor, citation manager, and supervisor feedback desk.
- **AC-04 (Malformed DOI & Fault-Tolerant Citation Ingestion):**
  - *Trigger:* A researcher enters a malformed or unreachable Digital Object Identifier (DOI), missing publication year, or unescaped BibTeX special characters.
  - *System Response:* [CitationController](file:///c:/Users/Lenovo/research-platform/app/Http/Controllers/CitationController.php) validates essential metadata fields (`title`, `authors`), allows null/optional DOI values, safely sanitizes special characters, and auto-generates a standardized numeric in-text marker (`[n]`). During BibTeX export, missing values are omitted cleanly without crashing the `.bib` serialization loop.
- **AC-05 (Media Upload Format Rejection & Storage Fallback):**
  - *Trigger:* An author attempts to upload a non-image file (e.g., `.exe`, `.sh`) or a file exceeding maximum allowed upload size into the Figure Gallery.
  - *System Response:* Server validation rejects the payload with HTTP 422 Unprocessable Entity, providing clear format requirements (`image/png`, `image/jpeg`, `image/webp`). If file storage is unavailable, authors can directly embed verified public image URLs with automatic thumbnail fallbacks.
- **AC-06 (External AI Provider Outage & Graceful Degradation):**
  - *Trigger:* The upstream LLM endpoint (Anthropic Claude, Google Gemini, OpenAI) suffers an outage, network timeout, or HTTP 429 rate limit during project summarization.
  - *System Response:* [AiController](file:///c:/Users/Lenovo/research-platform/app/Http/Controllers/AiController.php) catches the connection exception, logs the event for administrative monitoring, and returns an HTTP 503 response with a clear diagnostic message. Manuscript saving, document versioning, and collaborative drafting remain entirely uninterrupted.
- **AC-07 (Collaborative Version Rollback & Non-Destructive Audit Reconstruction):**
  - *Trigger:* A collaborator inadvertently deletes critical sections or introduces breaking edits during simultaneous writing.
  - *System Response:* The project owner opens the [DocumentVersion](file:///c:/Users/Lenovo/research-platform/app/Models/DocumentVersion.php) history pane, inspects chronological diffs, and restores the selected snapshot. Restoring a prior version creates a new incremental audit checkpoint without deleting associated supervisor comments or task items.
- **AC-08 (Institutional Ethics Clearance Gatekeeper):**
  - *Trigger:* An empirical research project involving human participants has not yet received institutional ethics approval (`ethics_status: 'not_assessed'` or `'submitted'`).
  - *System Response:* The workspace displays a persistent amber Institutional Review Board (IRB) warning badge in the project metadata header, advising the authors that ethics clearance is pending prior to formal thesis defense or camera-ready export.

---

## 3. Tools and Technologies

### 3.1 Programming Languages
- **PHP 8.4:** Core server-side runtime leveraging strict typing, constructor property promotion, and match expressions.
- **JavaScript (ECMAScript 2024):** Modular, vanilla client-side application logic without heavy runtime dependencies.
- **SQL (ANSI / MySQL dialect):** Relational database queries, constraints, and fulltext natural language indexing.
- **CSS3 / Vanilla CSS:** Bespoke design system utilizing CSS Custom Properties, flexbox, grid, and `@media print` directives.

### 3.2 Frameworks and Server Libraries
- **[Laravel 13.x](file:///c:/Users/Lenovo/research-platform/composer.json):** Full-stack web framework providing Eloquent ORM, routing, middleware fences, and blade templating.
- **[Laravel Reverb 1.0](file:///c:/Users/Lenovo/research-platform/config/reverb.php):** High-performance, event-driven WebSocket server running natively on PHP 8.4.
- **[Laravel Cashier 16.x](file:///c:/Users/Lenovo/research-platform/composer.json):** Subscription, billing, and trial token management.
- **Laravel Echo & Pusher JS:** Browser WebSocket subscription layer connecting to Reverb private channels.
- **Vite 6.x:** Next-generation frontend build tool and asset bundler.

### 3.3 Databases & Storage
- **MySQL 8.0 / SQLite 3:** Relational database storage supporting foreign key cascades, JSON column types, and FULLTEXT indexes.
- **Local / S3 Block Storage:** File storage for uploaded figures and empirical charts linked via `public/storage`.

### 3.4 AI Protocol, Models and APIs
- **Model Context Protocol (MCP) SDK (`Laravel\Mcp`):** Standard protocol exposing structured tools to external LLM clients.
- **Supported External AI Clients:** Anthropic Claude (via Claude Desktop / Claude Code), Cursor IDE, Google Gemini.
- **Internal AI Gateway:** Configurable OpenAI-compatible REST API integration (supporting OpenAI GPT-4o, Anthropic Claude 3.5 Sonnet, or Google Gemini 1.5/2.0 Flash).

### 3.5 Development & Quality Assurance Tooling
- **PHPUnit 12.x:** Automated unit and feature testing framework.
- **Laravel Pint:** Opinionated code style formatter enforcing PSR-12 and Laravel conventions.
- **Node.js 18+ & npm:** Package management for frontend assets.
- **Composer 2.x:** PHP dependency management.

---

## 4. Project Timeline and Work Plan

The project is structured across a **14-week academic development lifecycle**, broken into 5 core milestones.

### 4.1 Milestone Breakdown Table

| Milestone | Duration | Target Deliverables | Key Outputs |
| :--- | :--- | :--- | :--- |
| **M1: Architecture & Data Modeling** | Weeks 1 – 3 | Relational schema migrations, ORCID identity registration, base authentication, tenant authorization middleware. | Database blueprints, seeder routines, passing auth test cases (AUTH-01 to AUTH-09). |
| **M2: Authentic Paper Studio & Typesetting** | Weeks 4 – 6 | 3-pane research studio, physical paper sheet simulation, typography strip, column toggles, margins, print stylesheets. | Responsive paper viewport, Markdown export engine, print layout rules. |
| **M3: Methodology Engine & Citations** | Weeks 7 – 9 | Epistemological theory modal, interactive theory card, in-text citation chips, APA/IEEE reference compiler, BibTeX export. | Citation library API, BibTeX serializer, empirical figure gallery upload engine. |
| **M4: Real-Time Sockets & Collaboration** | Weeks 10 – 12 | Laravel Reverb WebSocket integration, private channel subscriptions, presence heartbeats, supervisor feedback desk. | Conflict-free multi-user editing, immutable version snapshot history, task board. |
| **M5: MCP AI Integration & Verification** | Weeks 13 – 14 | Model Context Protocol server (`/mcp/research`), `ProjectContextTool`, AI Gateway summarizer/evaluator, full test matrix. | 53 passing feature tests, complete documentation, final proposal and demo release. |

### 4.2 Task Distribution Among Team Members

To illustrate realistic group collaboration (e.g., 3-student team):

| Team Member | Primary Specialization | Assigned Responsibilities |
| :--- | :--- | :--- |
| **Member 1 (Lead Architecture & MCP)** | Backend & AI Protocol Engineering | - Implements Model Context Protocol server (`ResearchServer`, `ProjectContextTool`).<br>- Develops `AiGateway` and AI Controller (`summarize`, `evaluate`).<br>- Configures tenant isolation middleware and ORCID validation rules. |
| **Member 2 (Frontend & Real-Time Engine)** | UI/UX & WebSocket Engineering | - Develops the Authentic Paper Studio viewport, margin controls, and typography engine.<br>- Implements [workspace.js](file:///c:/Users/Lenovo/research-platform/resources/js/workspace.js) and Reverb WebSocket listeners.<br>- Builds the Epistemological Methodology Theory comparison modal and templates. |
| **Member 3 (Database, Citations & QA)** | Database, Integration & Testing | - Designs relational migrations (Citations, Media, Versions, Feedback).<br>- Develops BibTeX export serializer and in-context citation search engine.<br>- Authors the PHPUnit feature test suite ([PlaybookMatrixTest](file:///c:/Users/Lenovo/research-platform/tests/Feature/PlaybookMatrixTest.php)) and executes Pint formatting. |

---

## 5. System Architecture & Comparative Analysis

### 5.1 Existing Systems vs. Co-Auth Proposed System

| Dimension | Traditional Word Processors (Word, Google Docs) | Generic AI Chatbots (ChatGPT, Copilot) | Co-Auth Research Studio & MCP Platform |
| :--- | :--- | :--- | :--- |
| **Academic Page Simulation** | Generic continuous canvas; manual margin and column styling. | Plain chat window or code block view; no paper preview. | **Authentic physical sheet viewport** with live running heads, academic margins, and dual-column IEEE toggles. |
| **AI Context Visibility** | None (user must copy-paste or install third-party plugins). | Blind to local database, research questions, or citations; high hallucination risk. | **Grounding via MCP (`/mcp/research`)**: AI directly queries relational project state via `ProjectContextTool`. |
| **Citation Integration** | External plugins required (Zotero/EndNote); prone to corruption. | Frequently hallucinates non-existent authors, DOIs, and paper titles. | **Native In-Context Chips**, live search, automatic bibliography generator, and standard BibTeX (`.bib`) export. |
| **Methodological Rigor** | No theoretical scaffolding or paradigm guidance. | Superficial suggestions; conflates qualitative and quantitative rigor. | **Interactive Epistemological Theory Engine** comparing 6 paradigms (Constructivism, Positivism, DSR, etc.). |
| **Supervisor Review Loop** | Messy email attachments (`thesis_v2_comments.docx`); version sprawl. | Isolated chat sessions; cannot share review threads with advisor. | **Integrated Supervisor Desk**, live presence indicators, and immutable document version trails. |

---

### 5.2 Detailed Architectural Component Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                            PRESENTATION LAYER                               │
│                                                                             │
│   ┌───────────────────────────┐           ┌─────────────────────────────┐   │
│   │   Browser Paper Studio    │           │    Supervisor Review Desk   │   │
│   │   (Single/Dual Col Canvas)│           │   (Feedback, Tasks, Team)   │   │
│   └─────────────┬─────────────┘           └──────────────┬──────────────┘   │
└─────────────────┼────────────────────────────────────────┼──────────────────┘
                  │ HTTPS REST (JSON API v1)               │ WSS (WebSockets)
                  ▼                                        ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│                           APPLICATION / API LAYER                           │
│                                                                             │
│   ┌───────────────────────────┐           ┌─────────────────────────────┐   │
│   │    Laravel HTTP Kernel    │           │    Laravel Reverb Cluster   │   │
│   │ - EnsureProjectMember     │           │ - Private Sockets (:8080)   │   │
│   │ - CheckAiAccess           │           │ - Presence Channel Events   │   │
│   │ - ResearchProjectController│          │ - DocumentEdited Broadcasts │   │
│   │ - CitationController      │           └─────────────────────────────┘   │
│   └─────────────┬─────────────┘                                             │
└─────────────────┼───────────────────────────────────────────────────────────┘
                  │
                  ├────────────────────────────────────────┐
                  ▼                                        ▼
┌───────────────────────────────────┐    ┌────────────────────────────────────┐
│      PERSISTENCE / DATA LAYER     │    │       PROTOCOL / AI LAYER          │
│                                   │    │                                    │
│   ┌───────────────────────────┐   │    │   ┌────────────────────────────┐   │
│   │  MySQL 8.0 / SQLite 3     │   │    │   │ Laravel MCP Web Server     │   │
│   │ - users (ORCID unique)    │   │    │   │ - Route: /mcp/research     │   │
│   │ - research_projects       │   │    │   │ - Tool: ProjectContextTool │   │
│   │ - document_versions (JSON)│   │    │   └─────────────┬──────────────┘   │
│   │ - citations & media       │   │    └─────────────────┼──────────────────┘
│   │ - tasks & feedback        │   │                      │ JSON-RPC 2.0
│   └───────────────────────────┘   │                      ▼
└───────────────────────────────────┘    ┌────────────────────────────────────┐
                                         │  External Context-Aware AI Engine  │
                                         │  (Claude Desktop, Cursor, Gemini)  │
                                         └────────────────────────────────────┘
```

---

## 6. Ethical, Legal, and Social Considerations

### 6.1 Academic Integrity and Anti-Hallucination Safeguards
A primary ethical dilemma in academic software is student over-reliance on generative AI models that produce fraudulent literature reviews. Co-Auth mitigates this by:
1. Grounding all AI assistance in verified database records through the Model Context Protocol.
2. Directing system prompts to explicitly forbid fabricating sources, citations, or statistical claims.
3. Requiring all cited works to exist in the verified project citations registry before being compiled into the official bibliography.

### 6.2 Data Privacy & Intellectual Property (IP) Protection
Unpublished thesis research, novel software architectures, and early patent disclosures are highly sensitive intellectual property. Traditional public AI chatbots frequently train their foundation models on submitted user prompts. Co-Auth protects researcher IP by:
- Operating as an on-premise or private-cloud instance where project data remains strictly within the institution's database.
- Transmitting project metadata over MCP only during explicit, authorized user actions.
- Ensuring zero tenant cross-talk via verified session and project-membership authorization gates.

### 6.3 Ethical Research Compliance (IRB Integration)
To reinforce institutional research compliance, Co-Auth incorporates an **Institutional Ethics Status** tracker directly into the project setup:
- Tracks Institutional Review Board (IRB) / Human Subjects approval status (`not_assessed`, `not_required`, `submitted`, `approved`).
- Flags unapproved empirical human studies before publication or export.

---

## 7. Future Work & Long-Term Roadmap

1. **Two-Way Overleaf & Git Sync:** Implement automated bi-directional Git synchronization allowing LaTeX power users to push and pull manuscript changes directly to and from Overleaf.
2. **Institutional SSO Integration:** Connect institutional Shibboleth, SAML 2.0, and eduGAIN identity providers for seamless single-sign-on across university campus portals.
3. **Automated Plagiarism Pre-Screening:** Integrate open-access bibliometric API checks (Crossref, Semantic Scholar, OpenAlex) to highlight overlapping phrasing prior to formal defense submissions.
4. **Offline Mobile Reviewer Companion:** Build a progressive web application (PWA) allowing thesis advisors to annotate and voice-record feedback on student drafts while offline.

---

## 8. Submission Verification & Print Guidelines

### 8.1 Verification Command Checklist
Prior to final submission, the following verification commands must be executed from the repository root:

```bash
# 1. Verify all database migrations are applied
php artisan migrate:status

# 2. Run the automated test suite (53 passing tests required)
php artisan test --compact

# 3. Format PHP codebase to PSR-12 standard
vendor/bin/pint --dirty --format agent

# 4. Compile frontend production bundle
npm run build

# 5. Verify registered routes (including MCP and WebSockets)
php artisan route:list --except-vendor
```

### 8.2 PDF & Hard Copy Generation
- **Soft Copy (PDF):** Navigate to the project workspace at `http://127.0.0.1:8000/projects/1` and click **"🖨 Print / PDF"** (or press `Ctrl+P`). Select *"Save as PDF"* with *A4 paper size* and *default margins*. The platform's `@media print` rules will automatically strip UI chrome and render a clean, publication-grade document ready for BLC submission.
- **Hard Copy:** Print the generated PDF double-sided on standard A4 paper for submission during the scheduled continuous assessment session.
