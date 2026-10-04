<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $project->title }} · Research Studio · Co-Auth</title>
        @vite(['resources/css/app.css', 'resources/js/workspace.js'])
    </head>
    <body data-project-id="{{ $project->id }}" class="layout-full theme-clean-white font-serif margin-normal spacing-1-5 layout-single-col">
        <div class="workspace-shell">
            <header class="workspace-topbar">
                <a class="brand" href="{{ route('dashboard') }}">
                    <span class="brand-mark">C</span>
                    <span><strong>Co-Auth</strong><small>research studio</small></span>
                </a>
                <div class="workspace-crumb">
                    <a href="{{ route('dashboard') }}">All projects</a>
                    <span>/</span>
                    <strong class="project-title-crumb">{{ $project->title }}</strong>
                </div>

                <!-- Live Collaboration Presence Indicator -->
                <div class="team-presence-bar" id="team-presence-bar" title="Active working session">
                    <span class="presence-pulse"></span>
                    <span id="active-editor-banner" class="active-editor-banner">3 collaborators in room</span>
                </div>

                <!-- Layout & View Switcher -->
                <div class="layout-switch-group" role="toolbar" aria-label="Workspace Layout">
                    <button type="button" class="layout-btn is-active" data-layout-mode="full" title="Full Studio (Editor + Sidebars)">Studio</button>
                    <button type="button" class="layout-btn" data-layout-mode="focus" title="Focus Manuscript (Centered Paper)">Focus</button>
                    <button type="button" class="layout-btn" data-layout-mode="review" title="Reviewer Split (Manuscript + Feedback)">Review</button>
                </div>

                <!-- Theme / Palette Switcher -->
                <div class="theme-switch-group" role="toolbar" aria-label="Color Theme">
                    <button type="button" class="theme-btn theme-white is-active" data-theme-mode="clean_white" title="Clean White Paper"></button>
                    <button type="button" class="theme-btn theme-sepia" data-theme-mode="warm_sepia" title="Warm Sepia Manuscript"></button>
                    <button type="button" class="theme-btn theme-dark" data-theme-mode="academic_dark" title="Academic Dark Slate"></button>
                </div>

                <div class="workspace-actions">
                    <span class="save-state" id="save-state">All changes saved</span>
                    <button class="button button-quiet" type="button" id="open-format-panel" title="Paper margins and typography">📐 Paper layout</button>
                    <button class="button button-quiet" type="button" id="print-paper" title="Print or export manuscript to PDF">🖨 Print / PDF</button>
                    <a class="button button-quiet" href="{{ route('dashboard') }}">← Back</a>
                </div>
            </header>

            <main class="workspace-main">
                <!-- Project Heading & AI Assistant -->
                <section class="workspace-heading">
                    <div>
                        <div class="heading-tag-row">
                            <span class="discipline-tag">{{ $project->discipline ?: 'Interdisciplinary Research' }}</span>
                            <span class="status-pill status-{{ $project->status ?: 'draft' }}">{{ ucfirst($project->status ?: 'Draft') }}</span>
                        </div>
                        <h1>{{ $project->title }}</h1>
                        <p class="workspace-description">{{ $project->description ?: 'A shared collaborative space for thesis drafting, evidence capture, citations, and supervisor reviews.' }}</p>
                        <div class="ai-button-row">
                            <button class="button button-ai" id="ai-summary" type="button">✦ Ask research assistant</button>
                            <button class="button button-quiet" id="open-theory-guide" type="button">📚 Differentiate methodology theories</button>
                        </div>
                        <p class="ai-result" id="ai-result" role="status"></p>
                    </div>
                    <div class="workspace-progress">
                        <strong>{{ $project->progress }}%</strong>
                        <span>Milestone health</span>
                        <div class="progress-track">
                            <div class="progress-bar" style="width: {{ $project->progress }}%"></div>
                        </div>
                    </div>
                </section>

                <!-- Research Setup & Methodology Theory Differentiation Panel -->
                <section class="setup-panel panel">
                    <div class="panel-heading">
                        <div>
                            <p class="eyebrow">Research Method & Theoretical Framework</p>
                            <h2>Define the study methodology and epistemological ground</h2>
                        </div>
                        <div class="setup-heading-actions">
                            <button type="button" class="text-button" id="trigger-theory-modal">Methodology theory comparison →</button>
                            <span class="save-state" id="setup-state">Project setup</span>
                        </div>
                    </div>
                    <form id="setup-form" class="setup-form">
                        <label>
                            Research question
                            <textarea name="research_question" rows="2" placeholder="What core problem or inquiry is this study investigating?"></textarea>
                        </label>
                        <div class="methodology-select-block">
                            <label>
                                Methodology & Theoretical Stance
                                <select name="methodology" id="methodology-select">
                                    <option value="">Choose an approach</option>
                                    <option value="Qualitative">Qualitative (Interpretivism / Constructivism)</option>
                                    <option value="Quantitative">Quantitative (Positivism / Post-Positivism)</option>
                                    <option value="Mixed methods">Mixed Methods (Pragmatism & Triangulation)</option>
                                    <option value="Design science">Design Science Research (Artifact & Utility Theory)</option>
                                    <option value="Literature review">Systematic Literature Review (PRISMA Synthesis)</option>
                                    <option value="Action research">Action Research (Critical Theory & Cyclical Intervention)</option>
                                </select>
                            </label>
                            <!-- Dynamic Methodology Theory Differentiation Card -->
                            <div class="theory-card" id="theory-card">
                                <div class="theory-card-badge" id="theory-card-paradigm">Select a methodology</div>
                                <p class="theory-card-desc" id="theory-card-desc">Selecting a methodology will display its theoretical premise, epistemological ground, and when to use it over alternative methods.</p>
                                <div class="theory-meta-row" id="theory-card-meta">
                                    <span class="theory-pill" id="theory-pill-data">Data type</span>
                                    <span class="theory-pill" id="theory-pill-validation">Rigor criteria</span>
                                </div>
                            </div>
                        </div>
                        <label>
                            Expected contribution / outcome
                            <textarea name="expected_outcome" rows="2" placeholder="What contribution, prototype, empirical evidence, or template do you expect to produce?"></textarea>
                        </label>
                        <label>
                            Institutional ethics status
                            <select name="ethics_status">
                                <option value="not_assessed">Not assessed</option>
                                <option value="not_required">Not required</option>
                                <option value="submitted">Submitted for IRB/Ethics review</option>
                                <option value="approved">Approved by Ethics Committee</option>
                            </select>
                        </label>
                        <div class="setup-submit-row">
                            <button class="button button-primary" type="submit">Save research setup</button>
                        </div>
                    </form>
                </section>

                <!-- Workspace Grid: Editor + Multi-Tab Collaboration Hub -->
                <div class="workspace-grid" id="workspace-grid">
                    <!-- Manuscript Editor Panel -->
                    <section class="editor-panel">
                        <!-- Formatting & Academic Tools Toolbar -->
                        <div class="editor-toolbar">
                            <div class="toolbar-group">
                                <button type="button" data-command="bold" title="Bold (Ctrl+B)"><strong>B</strong></button>
                                <button type="button" data-command="italic" title="Italic (Ctrl+I)"><em>I</em></button>
                                <button type="button" data-command="underline" title="Underline (Ctrl+U)"><u>U</u></button>
                                <span class="toolbar-separator"></span>
                                <button type="button" data-format-block="h2" title="Section Heading (H2)">H2</button>
                                <button type="button" data-format-block="h3" title="Sub-section Heading (H3)">H3</button>
                                <button type="button" data-command="insertUnorderedList" title="Bulleted List">☷</button>
                                <button type="button" data-command="insertOrderedList" title="Numbered List">1.</button>
                                <button type="button" data-format-block="blockquote" title="Blockquote">“</button>
                            </div>

                            <div class="toolbar-academic-group">
                                <button type="button" class="toolbar-chip-btn" id="btn-open-templates" title="Choose a ready-to-use research paper template">
                                    <span class="icon">📄</span> Templates
                                </button>
                                <button type="button" class="toolbar-chip-btn" id="btn-open-media" title="Insert figures, diagrams, or media">
                                    <span class="icon">🖼</span> Insert Figure
                                </button>
                                <button type="button" class="toolbar-chip-btn" id="btn-open-citations" title="Insert citation in context">
                                    <span class="icon">“</span> Cite
                                </button>
                                <button type="button" class="toolbar-chip-btn" id="btn-append-bibliography" title="Generate bibliography at end of paper">
                                    <span class="icon">📚</span> Bibliography
                                </button>
                            </div>

                            <div class="toolbar-save-group">
                                <span class="version-label" id="version-label">Version 0</span>
                                <button class="button button-primary" id="save-version" type="button">Save version</button>
                            </div>
                        </div>

                        <!-- Paper Margins & Layout Quick Control Strip -->
                        <div class="paper-layout-strip" id="paper-layout-strip">
                            <div class="strip-item">
                                <span class="strip-label">Margin:</span>
                                <div class="strip-buttons" id="margin-buttons">
                                    <button type="button" class="strip-btn is-active" data-margin="normal" title="Standard 1.0 inch (25.4mm) academic margin">Normal (1")</button>
                                    <button type="button" class="strip-btn" data-margin="narrow" title="Narrow 0.5 inch (12.7mm)">Narrow (0.5")</button>
                                    <button type="button" class="strip-btn" data-margin="moderate" title="Moderate 0.75 inch (19mm)">Moderate (0.75")</button>
                                    <button type="button" class="strip-btn" data-margin="wide" title="Wide 1.25 inch (31.7mm) with room for review notes">Wide (1.25")</button>
                                </div>
                            </div>
                            <div class="strip-item">
                                <span class="strip-label">Columns:</span>
                                <div class="strip-buttons" id="column-buttons">
                                    <button type="button" class="strip-btn is-active" data-column="single_column" title="Single column standard thesis layout">Single Col</button>
                                    <button type="button" class="strip-btn" data-column="two_column" title="Two-column IEEE/ACM conference layout">Two Col</button>
                                </div>
                            </div>
                            <div class="strip-item">
                                <span class="strip-label">Font:</span>
                                <div class="strip-buttons" id="font-buttons">
                                    <button type="button" class="strip-btn is-active" data-font="serif" title="Academic Serif">Serif</button>
                                    <button type="button" class="strip-btn" data-font="sans" title="Modern Sans">Sans</button>
                                    <button type="button" class="strip-btn" data-font="mono" title="Technical Mono">Mono</button>
                                </div>
                            </div>
                            <div class="strip-item">
                                <span class="strip-label">Spacing:</span>
                                <div class="strip-buttons" id="spacing-buttons">
                                    <button type="button" class="strip-btn" data-spacing="1.0" title="Single spacing">1.0</button>
                                    <button type="button" class="strip-btn is-active" data-spacing="1.5" title="1.5 spacing standard thesis">1.5</button>
                                    <button type="button" class="strip-btn" data-spacing="2.0" title="Double spacing APA submission">2.0</button>
                                </div>
                            </div>
                        </div>

                        <!-- Authentic Paper Sheet Container -->
                        <div class="paper-sheet-viewport">
                            <div class="paper-sheet" id="paper-sheet">
                                <!-- Running Paper Header -->
                                <div class="paper-running-head">
                                    <span class="running-title">{{ $project->title }}</span>
                                    <span class="running-page">Page 1</span>
                                </div>

                                <!-- Editable Manuscript Canvas -->
                                <div class="editor-canvas" id="editor-canvas" contenteditable="true" role="textbox" aria-label="Thesis manuscript editor" spellcheck="true">
                                    <h2>1. Introduction & Research Problem</h2>
                                    <p>What happens when thesis writers and supervisors share one living research space? Graduate and scientific research requires rigorous documentation, rapid feedback loops, and empirical validation.</p>
                                    <p>Use this canvas to shape your argument, capture evidence, cite in context <span class="cite-ref" contenteditable="false" data-cite-key="hevner2004">[1]</span>, and leave a verifiable trail for peer reviewers and supervisors.</p>

                                    <h2>2. Theoretical Framework & Methodology</h2>
                                    <p>Ground your inquiry in a clearly articulated paradigm <span class="cite-ref" contenteditable="false" data-cite-key="creswell2018">[2]</span>. State whether your claims stem from inductive qualitative themes, deductive statistical hypotheses, or iterative artifact construction.</p>

                                    <figure class="paper-figure" contenteditable="false">
                                        <img src="https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=1000&q=80" alt="Collaborative Research Framework">
                                        <figcaption><strong class="fig-num">Figure 1:</strong> Collaborative Research Framework illustrating triangulation between qualitative feedback, quantitative milestones, and automated document synthesis in distributed teams.</figcaption>
                                    </figure>

                                    <h2>3. Working Notes & Empirical Evidence</h2>
                                    <p>Document observations, system benchmarks, participant transcripts, and open questions here as the project develops.</p>
                                </div>

                                <!-- Paper Running Footer -->
                                <div class="paper-running-foot">
                                    <span>Co-Auth Institutional Research Platform</span>
                                    <span>Confidential Working Draft</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Multi-Tab Sidebar: Collaboration, Citations, Media, Tasks, Feedback -->
                    <aside class="workspace-sidebar" id="workspace-sidebar">
                        <!-- Sidebar Navigation Tabs -->
                        <div class="sidebar-tab-row" role="tablist">
                            <button type="button" class="sidebar-tab is-active" data-sidebar-tab="team" title="Collaborators & Live Presence">Team</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="citations" title="Citations & Bibliography">Citations</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="figures" title="Figures & Media Gallery">Figures</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="tasks" title="Task Board & Tasks">Tasks</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="reviews" title="Supervisor Feedback Desk">Feedback</button>
                            <button type="button" class="sidebar-tab" data-sidebar-tab="history" title="Version Snapshots">History</button>
                        </div>

                        <!-- TAB 1: Team & Collaboration (Individual & 2/3 Group Seamless Work) -->
                        <div class="sidebar-panel is-active" id="tab-panel-team">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Project Team</p>
                                    <h2>Collaborators</h2>
                                </div>
                                <button class="button button-quiet button-sm" type="button" id="btn-invite-member" title="Invite a co-author or supervisor">＋ Invite</button>
                            </div>
                            <div class="working-directory-banner">
                                <span class="dir-icon">📁</span>
                                <div>
                                    <strong>Active Working Directory</strong>
                                    <small id="workspace-sync-status">Real-time sync active · 3 members</small>
                                </div>
                            </div>
                            <div id="member-list" class="member-list">
                                <div class="loading-line">Loading team…</div>
                            </div>
                            <div class="team-hint-box">
                                <p><strong>Seamless multi-user collaboration:</strong> Co-authors and supervisors can view drafts, post inline feedback, and save version snapshots without overwriting each other's work.</p>
                            </div>
                        </div>

                        <!-- TAB 2: In-Context Citations & References -->
                        <div class="sidebar-panel" id="tab-panel-citations">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Bibliography</p>
                                    <h2>Citations Library</h2>
                                </div>
                                <button class="button button-quiet button-sm" type="button" id="btn-add-citation">＋ Add citation</button>
                            </div>
                            <p class="panel-subcopy">Insert citations at your cursor position or generate the bibliography list.</p>
                            <button type="button" class="button button-primary button-full" id="btn-generate-references-action">📚 Append References to Paper</button>
                            <div id="citation-list" class="citation-list">
                                <div class="loading-line">Loading citations…</div>
                            </div>
                        </div>

                        <!-- TAB 3: Figures & Media Gallery -->
                        <div class="sidebar-panel" id="tab-panel-figures">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Visual Evidence</p>
                                    <h2>Figures & Media</h2>
                                </div>
                                <button class="button button-quiet button-sm" type="button" id="btn-upload-figure-tab">＋ Add figure</button>
                            </div>
                            <p class="panel-subcopy">Embed diagrams, charts, and plots directly into your manuscript.</p>
                            <div id="media-list" class="media-gallery">
                                <div class="loading-line">Loading media gallery…</div>
                            </div>
                        </div>

                        <!-- TAB 4: Shared Work / Task Board -->
                        <div class="sidebar-panel" id="tab-panel-tasks">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Shared Work</p>
                                    <h2>Task Board</h2>
                                </div>
                                <span id="task-count" class="review-count">0</span>
                            </div>
                            <form id="task-form" class="inline-form">
                                <input name="title" required placeholder="Add a research milestone or task…">
                                <button class="icon-button" type="submit" title="Add task">＋</button>
                            </form>
                            <div id="task-list" class="task-list">
                                <div class="loading-line">Loading tasks…</div>
                            </div>
                        </div>

                        <!-- TAB 5: Supervisor Desk / Feedback Loop -->
                        <div class="sidebar-panel" id="tab-panel-reviews">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Supervisor Desk</p>
                                    <h2>Feedback Loop</h2>
                                </div>
                                <span id="feedback-count" class="review-count">0</span>
                            </div>
                            <div id="feedback-list" class="feedback-list">
                                <div class="loading-line">Loading feedback…</div>
                            </div>
                            <form id="feedback-form" class="feedback-form">
                                <textarea name="body" required rows="2" placeholder="Leave review feedback or supervisor note…"></textarea>
                                <button class="button button-primary" type="submit">Post feedback</button>
                            </form>
                        </div>

                        <!-- TAB 6: Document Trail / Version History -->
                        <div class="sidebar-panel" id="tab-panel-history">
                            <div class="side-heading">
                                <div>
                                    <p class="eyebrow">Document Trail</p>
                                    <h2>Version History</h2>
                                </div>
                            </div>
                            <p class="panel-subcopy">Every save captures a permanent snapshot. You can restore previous drafts anytime.</p>
                            <div id="version-history" class="version-history">
                                <div class="loading-line">No versions yet</div>
                            </div>
                        </div>
                    </aside>
                </div>
            </main>
        </div>

        <!-- ================= MODALS & DIALOGS ================= -->

        <!-- 1. Methodology Theory Differentiation Modal -->
        <dialog class="app-dialog theory-dialog" id="theory-dialog">
            <div class="dialog-content-large">
                <button class="dialog-close" type="button" data-close-dialog="theory-dialog" aria-label="Close">×</button>
                <p class="eyebrow">Epistemological & Ontological Guide</p>
                <h2>Differentiate Theories of Research Methodologies</h2>
                <p class="dialog-copy">Different methodologies make different assumptions about reality, evidence, and truth. Choose the methodology whose theoretical stance matches your research question.</p>

                <div class="theory-comparison-table-wrap">
                    <table class="theory-table">
                        <thead>
                            <tr>
                                <th>Methodology</th>
                                <th>Theoretical Foundation</th>
                                <th>Epistemology (Truth & Reality)</th>
                                <th>Core Question</th>
                                <th>Primary Evidence</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Qualitative</strong></td>
                                <td><span class="theory-pill">Constructivism / Interpretivism</span></td>
                                <td>Reality is socially constructed through lived experience, cultural context, and subjective meaning.</td>
                                <td><em>"How" or "Why does a phenomenon occur?"</em></td>
                                <td>Semi-structured interviews, participant observations, thematic codes.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Qualitative">Apply</button></td>
                            </tr>
                            <tr>
                                <td><strong>Quantitative</strong></td>
                                <td><span class="theory-pill">Positivism / Post-Positivism</span></td>
                                <td>An objective, measurable reality exists independently of human observation; tested via deduction.</td>
                                <td><em>"What is the causal effect or relationship between variables?"</em></td>
                                <td>Structured surveys, randomized control trials, regression, p-values.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Quantitative">Apply</button></td>
                            </tr>
                            <tr>
                                <td><strong>Mixed Methods</strong></td>
                                <td><span class="theory-pill">Pragmatism (Dewey, Peirce)</span></td>
                                <td>Truth is found in practical utility and pluralistic inquiry; triangulates numbers and narratives.</td>
                                <td><em>"What happened (quant), and why did participants react this way (qual)?"</em></td>
                                <td>Sequential explanatory design, convergent parallel datasets.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Mixed methods">Apply</button></td>
                            </tr>
                            <tr>
                                <td><strong>Design Science</strong></td>
                                <td><span class="theory-pill">Sciences of the Artificial (Hevner, Simon)</span></td>
                                <td>Knowledge is produced through the creation, demonstration, and evaluation of innovative artifacts.</td>
                                <td><em>"How can we engineer a novel artifact to solve an unsolved problem?"</em></td>
                                <td>Software prototypes, algorithmic benchmarks, utility evaluations.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Design science">Apply</button></td>
                            </tr>
                            <tr>
                                <td><strong>Literature Review</strong></td>
                                <td><span class="theory-pill">PRISMA Framework / Critical Realism</span></td>
                                <td>Synthesizes cumulative scientific evidence without selection bias to uncover consensus and frontiers.</td>
                                <td><em>"What is the synthesized state of knowledge, and where are the gaps?"</em></td>
                                <td>Database query logs (IEEE/ACM/PubMed), bibliometrics, inclusion audits.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Literature review">Apply</button></td>
                            </tr>
                            <tr>
                                <td><strong>Action Research</strong></td>
                                <td><span class="theory-pill">Critical Theory / Participatory Action</span></td>
                                <td>Theory and practice advance simultaneously through cyclical intervention: Plan → Act → Observe → Reflect.</td>
                                <td><em>"How can researchers and practitioners collaboratively improve a system?"</em></td>
                                <td>Organizational field logs, change interventions, reflective debriefs.</td>
                                <td><button type="button" class="button button-quiet button-sm" data-apply-method="Action research">Apply</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="dialog-actions">
                    <button class="button button-quiet" type="button" data-close-dialog="theory-dialog">Close guide</button>
                </div>
            </div>
        </dialog>

        <!-- 2. Research Paper Templates Modal -->
        <dialog class="app-dialog template-dialog" id="template-dialog">
            <div class="dialog-content-large">
                <button class="dialog-close" type="button" data-close-dialog="template-dialog" aria-label="Close">×</button>
                <p class="eyebrow">Readymade Academic Scaffolding</p>
                <h2>Start from a Research Paper Template</h2>
                <p class="dialog-copy">Jumpstart your thesis without guessing section structures or reviewer expectations. Each template comes pre-filled with outline headings, guidance prompts, and checklist notes.</p>

                <div class="template-card-grid">
                    <div class="template-card">
                        <div class="template-card-top">
                            <span class="template-badge">Scientific Standard</span>
                            <h3>IMRaD Research Manuscript</h3>
                        </div>
                        <p class="template-desc">The universal scientific framework: Introduction, Methodology, Results, and Discussion. Ideal for empirical science and biomedical research.</p>
                        <div class="template-sections">
                            <span>Abstract</span><span>1. Intro</span><span>2. Methods</span><span>3. Results</span><span>4. Discussion</span><span>5. References</span>
                        </div>
                        <button type="button" class="button button-primary button-full" data-template-id="imrad">Use IMRaD Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-card-top">
                            <span class="template-badge">Engineering & CS</span>
                            <h3>IEEE / ACM Computer Science Paper</h3>
                        </div>
                        <p class="template-desc">Designed for software engineering, algorithms, AI, and systems architecture. Includes Related Work, System Design, and Empirical Benchmarks.</p>
                        <div class="template-sections">
                            <span>Abstract</span><span>I. Intro</span><span>II. Related Work</span><span>III. Architecture</span><span>IV. Evaluation</span><span>V. Threats</span>
                        </div>
                        <button type="button" class="button button-primary button-full" data-template-id="ieee_cs">Use IEEE / ACM Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-card-top">
                            <span class="template-badge">Social Sciences</span>
                            <h3>Qualitative Case Study & Thesis</h3>
                        </div>
                        <p class="template-desc">Focused on inductive inquiry, lived experience, interview narratives, and thematic qualitative coding with ethical transparency.</p>
                        <div class="template-sections">
                            <span>1. Problem</span><span>2. Theoretical Lens</span><span>3. Case Selection</span><span>4. Thematic Findings</span><span>5. Ethics</span>
                        </div>
                        <button type="button" class="button button-primary button-full" data-template-id="qualitative_thesis">Use Qualitative Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-card-top">
                            <span class="template-badge">Evidence Synthesis</span>
                            <h3>Systematic Literature Review (PRISMA)</h3>
                        </div>
                        <p class="template-desc">Follows PRISMA guidelines for comprehensive literature queries, quality appraisal, bibliometric synthesis, and research gap mapping.</p>
                        <div class="template-sections">
                            <span>1. Review Qs</span><span>2. Search Strategy</span><span>3. Inclusion Criteria</span><span>4. Bibliometrics</span><span>5. Gaps</span>
                        </div>
                        <button type="button" class="button button-primary button-full" data-template-id="prisma_review">Use PRISMA Template</button>
                    </div>

                    <div class="template-card">
                        <div class="template-card-top">
                            <span class="template-badge">Systems & Innovation</span>
                            <h3>Design Science Research (DSR)</h3>
                        </div>
                        <p class="template-desc">Rigorous framework for constructing and evaluating novel technological artifacts, frameworks, and software systems solving practical problems.</p>
                        <div class="template-sections">
                            <span>1. Problem</span><span>2. Objectives</span><span>3. Artifact Design</span><span>4. Demonstration</span><span>5. Evaluation</span>
                        </div>
                        <button type="button" class="button button-primary button-full" data-template-id="dsr_framework">Use DSR Template</button>
                    </div>
                </div>

                <div class="dialog-actions">
                    <button class="button button-quiet" type="button" data-close-dialog="template-dialog">Cancel</button>
                </div>
            </div>
        </dialog>

        <!-- 3. Insert Figure / Media Modal -->
        <dialog class="app-dialog" id="media-dialog">
            <form id="media-form" class="dialog-form" enctype="multipart/form-data">
                <button class="dialog-close" type="button" data-close-dialog="media-dialog" aria-label="Close">×</button>
                <p class="eyebrow">Visual Evidence</p>
                <h2>Insert Figure or Media</h2>
                <p class="dialog-copy">Add architecture diagrams, experimental charts, or conceptual figures with standard academic captions and figure numbers.</p>

                <div class="media-input-tabs">
                    <label class="radio-label">
                        <input type="radio" name="media_source_type" value="url" checked> Image URL / Unsplash
                    </label>
                    <label class="radio-label">
                        <input type="radio" name="media_source_type" value="file"> Upload image file
                    </label>
                </div>

                <div id="media-url-field">
                    <label>
                        Image URL
                        <input type="url" name="image_url" placeholder="https://... (or leave default demo sample)" value="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1000&q=80">
                    </label>
                </div>

                <div id="media-file-field" style="display: none;">
                    <label>
                        Select Image File
                        <input type="file" name="image_file" accept="image/*">
                    </label>
                </div>

                <label>
                    Figure Title / Header
                    <input type="text" name="title" required placeholder="e.g. Figure 2: Experimental Performance Comparison" value="Figure: System Architecture">
                </label>

                <label>
                    Academic Caption & Attribution
                    <textarea name="caption" rows="2" placeholder="Describe the figure and cite the source or experimental setup…">Comparative latency and throughput benchmarks under varying concurrent loads across distributed nodes.</textarea>
                </label>

                <div class="form-row-2">
                    <label>
                        Alignment
                        <select name="alignment" id="media-alignment">
                            <option value="center">Center (Standard Academic)</option>
                            <option value="full">Full width</option>
                            <option value="left">Left aligned</option>
                            <option value="right">Right aligned</option>
                        </select>
                    </label>
                </div>

                <div class="dialog-actions">
                    <button class="button button-quiet" type="button" data-close-dialog="media-dialog">Cancel</button>
                    <button class="button button-primary" type="submit">Insert into Manuscript</button>
                </div>
                <p class="form-error" id="media-form-error" role="alert"></p>
            </form>
        </dialog>

        <!-- 4. In-Context Citation Modal -->
        <dialog class="app-dialog" id="citation-dialog">
            <div class="dialog-form">
                <button class="dialog-close" type="button" data-close-dialog="citation-dialog" aria-label="Close">×</button>
                <p class="eyebrow">Academic Sourcing</p>
                <h2>Insert Citation in Context</h2>
                <p class="dialog-copy">Pick an existing reference from your project bibliography or add a new scholarly source to cite at the cursor.</p>

                <div class="citation-mode-toggle">
                    <button type="button" class="tab-chip is-active" id="btn-cite-mode-existing">Select existing citation</button>
                    <button type="button" class="tab-chip" id="btn-cite-mode-new">＋ Add new source</button>
                </div>

                <!-- Existing Citations List for 1-Click In-Text Insertion -->
                <div id="existing-citations-container">
                    <div id="quick-citation-picker" class="quick-citation-picker">
                        <div class="loading-line">Loading citations…</div>
                    </div>
                </div>

                <!-- New Citation Form -->
                <form id="citation-form" style="display: none;">
                    <label>
                        Paper or Book Title
                        <input type="text" name="title" required placeholder="e.g. Deep Residual Learning for Image Recognition">
                    </label>
                    <label>
                        Authors
                        <input type="text" name="authors" required placeholder="e.g. He, K., Zhang, X., Ren, S., & Sun, J.">
                    </label>
                    <div class="form-row-2">
                        <label>
                            Publication Year
                            <input type="text" name="year" placeholder="e.g. 2016" value="2024">
                        </label>
                        <label>
                            Citation Key (optional)
                            <input type="text" name="key" placeholder="e.g. he2016deep">
                        </label>
                    </div>
                    <label>
                        Journal, Conference, or Publisher
                        <input type="text" name="source" placeholder="e.g. IEEE Conference on Computer Vision and Pattern Recognition (CVPR)">
                    </label>
                    <div class="form-row-2">
                        <label>
                            DOI
                            <input type="text" name="doi" placeholder="e.g. 10.1109/CVPR.2016.90">
                        </label>
                        <label>
                            URL
                            <input type="url" name="url" placeholder="https://doi.org/...">
                        </label>
                    </div>
                    <div class="dialog-actions">
                        <button class="button button-quiet" type="button" data-close-dialog="citation-dialog">Cancel</button>
                        <button class="button button-primary" type="submit">Save & Insert Citation</button>
                    </div>
                    <p class="form-error" id="citation-form-error" role="alert"></p>
                </form>
            </div>
        </dialog>

        <!-- 5. Invite Collaborator Modal (Team of 2/3 Multi-User Workflow) -->
        <dialog class="app-dialog" id="member-dialog">
            <form id="member-form" class="dialog-form">
                <button class="dialog-close" type="button" data-close-dialog="member-dialog" aria-label="Close">×</button>
                <p class="eyebrow">Multi-User Collaboration</p>
                <h2>Invite Project Collaborator</h2>
                <p class="dialog-copy">Add co-authors, thesis advisors, or peer reviewers. A group of 2-3 can draft and review together seamlessly in real-time.</p>

                <label>
                    Collaborator Email
                    <input type="email" name="email" required placeholder="e.g. coauthor@university.edu or supervisor@institution.org">
                </label>

                <label>
                    Role in Project
                    <select name="role" required>
                        <option value="co-author">Co-Author (Full write & version access)</option>
                        <option value="supervisor">Supervisor (Reviewer & feedback author)</option>
                        <option value="reviewer">Peer Reviewer (Read & feedback)</option>
                        <option value="student">Student Researcher</option>
                    </select>
                </label>

                <div class="dialog-actions">
                    <button class="button button-quiet" type="button" data-close-dialog="member-dialog">Cancel</button>
                    <button class="button button-primary" type="submit">Add Collaborator</button>
                </div>
                <p class="form-error" id="member-form-error" role="alert"></p>
            </form>
        </dialog>
    </body>
</html>