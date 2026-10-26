const projectId = document.body.dataset.projectId;
const editor = document.querySelector('#editor-canvas');
const memberList = document.querySelector('#member-list');
const versionHistory = document.querySelector('#version-history');
const versionLabel = document.querySelector('#version-label');
const saveState = document.querySelector('#save-state');
const taskList = document.querySelector('#task-list');
const feedbackList = document.querySelector('#feedback-list');
const citationList = document.querySelector('#citation-list');
const mediaList = document.querySelector('#media-list');
const aiSummaryButton = document.querySelector('#ai-summary');
const aiResult = document.querySelector('#ai-result');
const setupForm = document.querySelector('#setup-form');
const setupState = document.querySelector('#setup-state');
const activeEditorBanner = document.querySelector('#active-editor-banner');
const methodologySelect = document.querySelector('#methodology-select');
const runningMethodology = document.querySelector('#running-methodology');
const paperPrinciple = document.querySelector('#paper-principle');
const aiModelBadge = document.querySelector('#ai-model-badge');
const aiLabPrompt = document.querySelector('#ai-lab-prompt');
const aiLabSubmit = document.querySelector('#ai-lab-submit');
const aiLabOutput = document.querySelector('#ai-lab-output');
const aiLabModel = document.querySelector('#ai-lab-model');
const aiActiveIdentity = document.querySelector('#ai-active-identity');
const aiAgents = document.querySelectorAll('[data-ai-agent]');

const updateAiIdentity = (model = '') => {
    const normalizedModel = model.toLowerCase();
    const identity = normalizedModel.includes('deepseek')
        ? { key: 'deepseek', name: 'DeepSeek Reviewer' }
        : normalizedModel.includes('qwen')
            ? { key: 'qwen', name: 'Qwen Builder' }
            : { key: 'llama', name: 'Llama Research Assistant' };

    if (aiActiveIdentity) aiActiveIdentity.textContent = identity.name;
    aiAgents.forEach((agent) => agent.classList.toggle('is-active', agent.dataset.aiAgent === identity.key));
};

updateAiIdentity();

// Cached project data
let currentProject = null;
let currentCitations = [];
let currentMedia = [];
let latestKnownVersion = 0;

// ================= 1. THEORY OF METHODOLOGY DEFINITIONS =================
const METHODOLOGY_THEORIES = {
    Qualitative: {
        paradigm: 'Interpretivism & Social Constructivism',
        desc: 'Assumes reality is socially constructed through participant narratives, cultural context, and lived experience. Aims for deep inductive discovery rather than statistical testing.',
        data: 'Semi-structured interviews, focus groups, field observation, grounded theory coding',
        validation: 'Lincoln & Guba criteria: Credibility, Transferability, Dependability, Confirmability',
    },
    Quantitative: {
        paradigm: 'Positivism & Post-Positivism (Empiricism)',
        desc: 'Assumes an objective reality exists independently of human observation. Employs deductive hypothesis testing, operationalized variables, and inferential statistics to establish causality.',
        data: 'Randomized controlled trials, structured surveys, telemetry instrumentation, regression analysis',
        validation: 'Internal & External Validity, Statistical Significance (p-values), Construct Reliability',
    },
    'Mixed methods': {
        paradigm: 'Pragmatism (John Dewey, C.S. Peirce)',
        desc: 'Rejects methodological dogmatism: "Truth is what works in practice." Integrates quantitative breadth with qualitative contextual depth using triangulation and sequential explanatory frameworks.',
        data: 'Convergent parallel datasets (e.g., broad quantitative survey + follow-up qualitative interviews)',
        validation: 'Design fidelity, interpretive consistency, cross-methodological triangulation',
    },
    'Design science': {
        paradigm: 'Sciences of the Artificial & Utility Theory (Hevner, Simon)',
        desc: 'Generates prescriptive scientific knowledge through the design, demonstration, and empirical evaluation of innovative technological artifacts (software, architectures, algorithms).',
        data: 'Functional prototypes, architectural blueprints, computational benchmarks, usability evaluations',
        validation: 'Utility theory, artifact novelty, structural robustness, empirical benchmark superiority',
    },
    'Literature review': {
        paradigm: 'Critical Realism & PRISMA Evidence Protocol',
        desc: 'Synthesizes cumulative scientific evidence without selection bias. Audits existing literature to map consensus, establish foundational baselines, and uncover unaddressed research frontiers.',
        data: 'Comprehensive database queries across IEEE Xplore, ACM DL, Scopus, PubMed, and Web of Science',
        validation: 'PRISMA checklist, reproducible search strings, explicit inclusion/exclusion audit',
    },
    'Action research': {
        paradigm: 'Critical Social Theory & Participatory Inquiry (Kurt Lewin)',
        desc: 'Simultaneously solves real-world organizational challenges while advancing scholarly theory through collaborative, iterative cycles: Plan → Act → Observe → Reflect.',
        data: 'Intervention logs, stakeholder debriefs, iterative process metrics, reflective journals',
        validation: 'Recoverability of inquiry, catalytic validity, tangible stakeholder transformation',
    },
};

const updateTheoryCard = (methodology) => {
    const card = document.querySelector('#theory-card');
    const paradigm = document.querySelector('#theory-card-paradigm');
    const desc = document.querySelector('#theory-card-desc');
    const dataPill = document.querySelector('#theory-pill-data');
    const valPill = document.querySelector('#theory-pill-validation');

    if (!card || !paradigm || !desc) return;

    const theory = METHODOLOGY_THEORIES[methodology];
    if (theory) {
        paradigm.textContent = theory.paradigm;
        desc.textContent = theory.desc;
        if (dataPill) dataPill.textContent = 'Data: ' + theory.data;
        if (valPill) valPill.textContent = 'Rigor: ' + theory.validation;
        card.classList.add('has-selection');
    } else {
        paradigm.textContent = 'Select a methodology';
        desc.textContent = 'Selecting a methodology will display its theoretical premise, epistemological ground, and when to use it over alternative methods.';
        if (dataPill) dataPill.textContent = 'Data type';
        if (valPill) valPill.textContent = 'Rigor criteria';
        card.classList.remove('has-selection');
    }
};

const updateResearchFrame = (methodology) => {
    const principles = {
        Qualitative: 'credibility · reflexivity · participant voice',
        Quantitative: 'validity · reliability · reproducibility',
        'Mixed methods': 'triangulation · integration · pragmatic fit',
        'Design science': 'utility · rigor · artifact evaluation',
        'Literature review': 'transparency · evidence synthesis · PRISMA',
        'Action research': 'participation · reflection · change',
    };

    if (runningMethodology) {
        runningMethodology.textContent = methodology ? `· ${methodology}` : '· Methodology pending';
    }
    if (paperPrinciple) {
        paperPrinciple.textContent = principles[methodology] || 'Research integrity · traceable evidence · reflective practice';
    }
};

methodologySelect?.addEventListener('change', (e) => {
    updateTheoryCard(e.target.value);
    updateResearchFrame(e.target.value);
});

const architectureDetails = {
    question: 'Start with a bounded question: define the problem, who it serves, and what a meaningful outcome looks like.',
    method: 'Make the paradigm visible. Link the research question to an appropriate method, theoretical stance, and ethics decision.',
    evidence: 'Treat every claim as traceable: connect source keys, figures, datasets, and observations to the section where they are used.',
    review: 'Use feedback and versions as part of the method. Record limitations, supervisor decisions, and reflections instead of losing them in email.',
    contribution: 'Close the loop with a defensible contribution: findings, artifact utility, implications, and a concrete next research step.',
};

document.querySelectorAll('[data-architecture-node]').forEach((node) => {
    node.addEventListener('click', () => {
        document.querySelectorAll('[data-architecture-node]').forEach((item) => item.classList.remove('is-selected'));
        node.classList.add('is-selected');
        const detail = document.querySelector('#architecture-detail');
        if (detail) detail.textContent = architectureDetails[node.dataset.architectureNode];
    });
});

document.querySelector('#toggle-architecture')?.addEventListener('click', (event) => {
    const tree = document.querySelector('#architecture-tree');
    if (!tree) return;
    const isCollapsed = tree.classList.toggle('is-collapsed');
    event.currentTarget.setAttribute('aria-expanded', String(!isCollapsed));
    event.currentTarget.textContent = isCollapsed ? 'Expand tree ↓' : 'Collapse tree ↑';
});

const runAiSummary = async (instruction, sourceButton = null) => {
    const buttons = [aiSummaryButton, aiLabSubmit, ...document.querySelectorAll('[data-ai-instruction]')].filter(Boolean);
    buttons.forEach((button) => { button.disabled = true; });
    if (sourceButton) sourceButton.textContent = 'Thinking…';
    if (aiSummaryButton) aiSummaryButton.textContent = 'Analyzing thesis context…';
    if (aiLabOutput) aiLabOutput.innerHTML = '<span class="ai-output-loading">Synthesizing project context…</span>';
    if (aiResult) aiResult.textContent = '';

    try {
        const response = await fetch(`/api/v1/projects/${projectId}/ai/summary`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ instruction: instruction || 'Summarize the current project state and recommend the next three research actions.' }),
        });
        const payload = await response.json();
        if (!response.ok) {
            throw new Error(payload.message || 'The research assistant is unavailable right now.');
        }

        const content = payload.data.content || 'No response was returned.';
        if (aiResult) aiResult.textContent = content;
        if (aiLabOutput) aiLabOutput.textContent = content;
        if (aiModelBadge) aiModelBadge.textContent = `${payload.data.provider || 'AI'} · ${payload.data.model || 'configured model'}`;
        if (aiLabModel) aiLabModel.textContent = `${payload.data.provider || 'AI'} · ${payload.data.model || 'configured model'}`;
        updateAiIdentity(payload.data.model || '');
    } catch (error) {
        if (aiResult) aiResult.textContent = error.message;
        if (aiLabOutput) aiLabOutput.textContent = error.message;
    } finally {
        buttons.forEach((button) => { button.disabled = false; });
        if (aiSummaryButton) aiSummaryButton.textContent = '✦ Ask research assistant';
        if (sourceButton) sourceButton.textContent = 'Run research copilot';
    }
};

document.querySelectorAll('[data-ai-instruction]').forEach((button) => {
    button.addEventListener('click', () => {
        if (aiLabPrompt) aiLabPrompt.value = button.dataset.aiInstruction;
        runAiSummary(button.dataset.aiInstruction, button.classList.contains('ai-quick-action') ? aiLabSubmit : button);
    });
});

aiLabSubmit?.addEventListener('click', () => runAiSummary(aiLabPrompt?.value, aiLabSubmit));
aiLabPrompt?.addEventListener('keydown', (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') {
        event.preventDefault();
        runAiSummary(aiLabPrompt.value, aiLabSubmit);
    }
});

// ================= 2. RESEARCH PAPER TEMPLATES =================
const PAPER_TEMPLATES = {
    imrad: `
        <h2>Abstract</h2>
        <p><em>[Provide a concise 150-250 word summary covering the research problem, methodology, key findings, and practical implications.]</em></p>
        
        <h2>1. Introduction</h2>
        <p>Establish the academic and real-world significance of the research topic. What gap in knowledge exists, and why must it be addressed now?</p>
        <p><strong>Research Objectives:</strong> Clearly enumerate the primary questions or hypotheses this paper investigates.</p>

        <h2>2. Materials & Methods</h2>
        <p>Describe the research design, participants, materials, and procedures with sufficient detail for external replication. Ground your methodological choices in established literature.</p>
        <p><em>[Tip: Cite validation instruments and statistical or qualitative analysis procedures used.]</em></p>

        <h2>3. Results & Empirical Findings</h2>
        <p>Present the findings objectively using text, tables, and figures. Highlight significant patterns, statistical trends, or thematic categories without interpretive commentary.</p>

        <h2>4. Discussion</h2>
        <p>Interpret the results in the context of the initial research questions. How do your findings compare with prior work? What are the theoretical and practical implications?</p>
        <p><strong>Limitations:</strong> Candidly acknowledge any methodological boundaries, sample constraints, or confounding variables.</p>

        <h2>5. Conclusion & Future Work</h2>
        <p>Summarize the core contributions and outline concrete recommendations for subsequent research.</p>
    `,

    ieee_cs: `
        <h2>Abstract</h2>
        <p><em>[State the computational or engineering problem, the proposed architecture/algorithm, implementation highlights, and benchmark speedup or accuracy improvements.]</em></p>
        <p><strong>Index Terms:</strong> Software Architecture, Distributed Systems, Empirical Evaluation, Benchmarking.</p>

        <h2>I. Introduction</h2>
        <p>Modern computing environments face critical bottlenecks. This paper introduces a novel system architecture designed to solve scalability and latency constraints.</p>
        <p><strong>Our Contributions:</strong></p>
        <ul>
            <li>We formulate the distributed coordination challenge under high concurrency.</li>
            <li>We design and implement an end-to-end framework.</li>
            <li>We empirically evaluate our solution against baseline state-of-the-art benchmarks.</li>
        </ul>

        <h2>II. Related Work</h2>
        <p>Review prior systems and highlight where current literature fails to address the targeted performance parameters.</p>

        <h2>III. System Architecture & Design</h2>
        <p>Provide a comprehensive technical description of the proposed framework, data flows, and algorithmic mechanisms.</p>

        <h2>IV. Experimental Evaluation</h2>
        <p>Detail the experimental testbed, hardware/software specifications, workloads, and metrics (latency, throughput, resource consumption).</p>

        <h2>V. Threats to Validity</h2>
        <p>Discuss construct, internal, and external validity threats and how they were mitigated.</p>

        <h2>VI. Conclusion & Future Work</h2>
        <p>Summarize key findings and prospective future engineering iterations.</p>
    `,

    qualitative_thesis: `
        <h2>Abstract</h2>
        <p><em>[Summarize the lived experiences, social context, qualitative inquiry approach, core emergent themes, and theoretical contribution.]</em></p>

        <h2>1. Research Context & Problem Statement</h2>
        <p>Situate the inquiry within the social, organizational, or cultural context. Why is qualitative depth essential for understanding this phenomenon?</p>

        <h2>2. Theoretical Framework & Epistemology</h2>
        <p>Articulate the interpretivist/constructivist theoretical lens through which the study was conceptualized and analyzed.</p>

        <h2>3. Methodology & Participant Selection</h2>
        <p>Detail the purposive or theoretical sampling strategy, participant profiles, interview protocols, and ethical safeguards.</p>

        <h2>4. Qualitative Analysis & Emergent Themes</h2>
        <p><strong>Theme 1: Navigating Structural Ambiguity</strong> — Integrate verbatim participant quotes with rich analytical synthesis.</p>
        <p><strong>Theme 2: Collaborative Sensemaking</strong> — Illustrate how participants negotiated shared understanding.</p>

        <h2>5. Discussion & Implications</h2>
        <p>Connect emergent themes back to existing academic literature and organizational practice.</p>

        <h2>6. Ethical Considerations & Reflexivity</h2>
        <p>Reflect on researcher positioning, potential biases, and member-checking validation steps.</p>
    `,

    prisma_review: `
        <h2>Abstract</h2>
        <p><em>[Structured summary: Background, Objectives, Data Sources, Eligibility Criteria, Synthesis Methods, Key Findings, and Conclusions.]</em></p>

        <h2>1. Background & Review Questions</h2>
        <p>Formulate explicit research questions using PICO/SPIDER frameworks. Define the scope and justification for this systematic literature review.</p>

        <h2>2. Search Protocol & PRISMA Strategy</h2>
        <p>Document the electronic databases searched (e.g., IEEE Xplore, ACM DL, Scopus), exact boolean query strings, and date limits.</p>

        <h2>3. Study Selection & Quality Appraisal</h2>
        <p>Detail the multi-stage screening process (title/abstract screening followed by full-text evaluation) with explicit inclusion/exclusion criteria.</p>

        <h2>4. Bibliometric & Thematic Synthesis</h2>
        <p>Categorize accepted studies by publication chronology, geography, methodologies used, and technological frameworks.</p>

        <h2>5. Identified Research Gaps & Future Roadmap</h2>
        <p>Synthesize major unresolved frontiers and offer actionable recommendations for the academic community.</p>
    `,

    dsr_framework: `
        <h2>Abstract</h2>
        <p><em>[Summarize the business/engineering problem, the designed artifact, the evaluation environment, and the utility delivered.]</em></p>

        <h2>1. Problem Identification & Motivation</h2>
        <p>Define the specific problem in practice and demonstrate its academic and organizational significance.</p>

        <h2>2. Solution Objectives & Requirements</h2>
        <p>Derive functional and non-functional requirements that an effective artifact must satisfy.</p>

        <h2>3. Artifact Design & Architecture</h2>
        <p>Present the structure, modules, and internal mechanisms of the designed system, model, or algorithm.</p>

        <h2>4. Demonstration</h2>
        <p>Demonstrate the artifact solving an instance of the problem in an institutional or simulated case environment.</p>

        <h2>5. Empirical Evaluation</h2>
        <p>Measure the artifact's utility, efficacy, and performance using qualitative or quantitative criteria.</p>

        <h2>6. Theoretical & Practical Contributions</h2>
        <p>Articulate design principles and reusable prescriptive knowledge derived from the creation process.</p>
    `,
};

// ================= 3. WORKSPACE CORE LOADER =================
const loadWorkspace = async () => {
    try {
        const [projectResponse, taskResponse, feedbackResponse, citationsResponse, mediaResponse] = await Promise.all([
            fetch(`/api/v1/projects/${projectId}`, { headers: { Accept: 'application/json' } }),
            fetch(`/api/v1/projects/${projectId}/tasks`, { headers: { Accept: 'application/json' } }),
            fetch(`/api/v1/projects/${projectId}/feedback`, { headers: { Accept: 'application/json' } }),
            fetch(`/api/v1/projects/${projectId}/citations`, { headers: { Accept: 'application/json' } }),
            fetch(`/api/v1/projects/${projectId}/media`, { headers: { Accept: 'application/json' } }),
        ]);

        const { data: project } = await projectResponse.json();
        const { data: tasks } = await taskResponse.json();
        const { data: feedback } = await feedbackResponse.json();
        const { data: citations } = await citationsResponse.json();
        const { data: media } = await mediaResponse.json();

        currentProject = project;
        currentCitations = citations || [];
        currentMedia = media || [];

        renderMembers(project.members);
        renderFormatting(project);
        renderActivePresence(project.active_editor);
        renderCitations(currentCitations);
        renderMedia(currentMedia);

        // Version state
        if (project.latest_version) {
            latestKnownVersion = project.latest_version.version_number;
            versionLabel.textContent = `Version ${project.latest_version.version_number}`;
            versionHistory.innerHTML = `
                <div class="version-row">
                    <div>
                        <strong>Version ${project.latest_version.version_number}</strong>
                        <small>Saved by ${project.latest_version.content.author || 'collaborator'}</small>
                    </div>
                    <button type="button" class="button button-quiet button-sm" data-restore-version="${project.latest_version.id}">Restore</button>
                </div>
            `;
        }

        // Tasks
        taskList.innerHTML = tasks.length
            ? tasks.map((task) => `
                <div class="task-row">
                    <button class="task-toggle ${task.status === 'done' ? 'is-done' : ''}" data-task-id="${task.id}" data-task-status="${task.status}" type="button">
                        ${task.status === 'done' ? '✓' : '○'}
                    </button>
                    <span>${task.title}</span>
                </div>
            `).join('')
            : '<div class="loading-line">No tasks yet. Add a milestone above.</div>';
        document.querySelector('#task-count').textContent = tasks.filter((t) => t.status !== 'done').length;

        // Feedback
        feedbackList.innerHTML = feedback.length
            ? feedback.map((item) => `
                <div class="feedback-item ${item.status === 'resolved' ? 'is-resolved' : ''}">
                    <div class="feedback-item-header">
                        <strong>${item.author.name}</strong>
                        <span class="feedback-status-badge">${item.status}</span>
                    </div>
                    <p>${item.body}</p>
                    <button class="text-button" data-feedback-id="${item.id}" type="button">
                        ${item.status === 'resolved' ? 'Resolved ✓' : 'Mark resolved'}
                    </button>
                </div>
            `).join('')
            : '<div class="loading-line">No supervisor notes yet.</div>';
        document.querySelector('#feedback-count').textContent = feedback.filter((i) => i.status === 'open').length;

        // Setup form populate
        if (setupForm) {
            setupForm.elements.research_question.value = project.research_question || '';
            setupForm.elements.methodology.value = project.methodology || '';
            setupForm.elements.expected_outcome.value = project.expected_outcome || '';
            setupForm.elements.ethics_status.value = project.ethics_status || 'not_assessed';
            updateTheoryCard(project.methodology);
            updateResearchFrame(project.methodology);
        }

        updateManuscriptStats();
    } catch (err) {
        console.error('Workspace load error:', err);
    }
};

// ================= 4. COLLABORATION (INDIVIDUAL & 2/3 GROUP) =================
const renderMembers = (members) => {
    if (!memberList) return;
    const roleColors = {
        supervisor: 'avatar-coral',
        'co-author': 'avatar-amber',
        student: 'avatar-teal',
        reviewer: 'avatar-purple',
    };

    memberList.innerHTML = members.map((member) => {
        const initials = member.name.split(' ').map((p) => p[0]).join('').slice(0, 2);
        const roleClass = roleColors[member.role] || 'avatar-teal';
        return `
            <div class="member-row">
                <span class="avatar ${roleClass}">${initials}</span>
                <div class="member-info">
                    <strong>${member.name}</strong>
                    <small class="role-badge role-${member.role}">${member.role.replace('-', ' ')}</small>
                </div>
                <span class="member-online" title="Online in workspace">●</span>
            </div>
        `;
    }).join('');

    const count = members.length;
    const banner = document.querySelector('#workspace-sync-status');
    if (banner) {
        banner.textContent = `Real-time sync active · ${count} member${count > 1 ? 's' : ''}`;
    }
};

const renderActivePresence = (activeEditor) => {
    if (!activeEditorBanner) return;
    if (activeEditor) {
        activeEditorBanner.innerHTML = `<strong>${activeEditor.name}</strong> is editing: <em>${activeEditor.section}</em> (${activeEditor.active_at})`;
    } else {
        activeEditorBanner.textContent = 'All team members in sync';
    }
};

// Periodic Presence Heartbeat for Multi-User Working Directory
setInterval(async () => {
    try {
        const sectionHeading = editor?.querySelector('h2:hover, h2') ? editor.querySelector('h2').textContent.slice(0, 40) : 'Manuscript';
        await fetch(`/api/v1/projects/${projectId}/presence`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ section: sectionHeading }),
        });
    } catch (_) {}
}, 15000);

// ================= 5. PAPER MARGINS, LAYOUT & TYPOGRAPHY =================
const applyFormattingClasses = (formatting) => {
    const body = document.body;

    // Margins
    ['margin-normal', 'margin-narrow', 'margin-moderate', 'margin-wide'].forEach((cls) => body.classList.remove(cls));
    body.classList.add(`margin-${formatting.paper_margin || 'normal'}`);

    // Columns
    ['layout-single-col', 'layout-two-col'].forEach((cls) => body.classList.remove(cls));
    body.classList.add(formatting.paper_layout === 'two_column' ? 'layout-two-col' : 'layout-single-col');

    // Font
    ['font-serif', 'font-sans', 'font-mono'].forEach((cls) => body.classList.remove(cls));
    body.classList.add(`font-${formatting.font_family || 'serif'}`);

    // Line Spacing
    ['spacing-1-0', 'spacing-1-15', 'spacing-1-5', 'spacing-2-0'].forEach((cls) => body.classList.remove(cls));
    const spacingKey = (formatting.line_spacing || '1.5').replace('.', '-');
    body.classList.add(`spacing-${spacingKey}`);

    // Theme
    ['theme-clean-white', 'theme-warm-sepia', 'theme-academic-dark'].forEach((cls) => body.classList.remove(cls));
    const themeCls = formatting.active_theme === 'warm_sepia' ? 'theme-warm-sepia' : (formatting.active_theme === 'academic_dark' ? 'theme-academic-dark' : 'theme-clean-white');
    body.classList.add(themeCls);

    // Sync button active states
    document.querySelectorAll('[data-margin]').forEach((b) => b.classList.toggle('is-active', b.dataset.margin === formatting.paper_margin));
    document.querySelectorAll('[data-column]').forEach((b) => b.classList.toggle('is-active', b.dataset.column === formatting.paper_layout));
    document.querySelectorAll('[data-font]').forEach((b) => b.classList.toggle('is-active', b.dataset.font === formatting.font_family));
    document.querySelectorAll('[data-spacing]').forEach((b) => b.classList.toggle('is-active', b.dataset.spacing === formatting.line_spacing));
    document.querySelectorAll('[data-theme-mode]').forEach((b) => b.classList.toggle('is-active', b.dataset.themeMode === formatting.active_theme));
};

const renderFormatting = (project) => {
    applyFormattingClasses({
        paper_margin: project.paper_margin || 'normal',
        paper_layout: project.paper_layout || 'single_column',
        font_family: project.font_family || 'serif',
        line_spacing: project.line_spacing || '1.5',
        active_theme: project.active_theme || 'clean_white',
    });
};

const saveFormatting = async (partial) => {
    const merged = {
        paper_margin: document.querySelector('#margin-buttons .is-active')?.dataset.margin || 'normal',
        paper_layout: document.querySelector('#column-buttons .is-active')?.dataset.column || 'single_column',
        font_family: document.querySelector('#font-buttons .is-active')?.dataset.font || 'serif',
        line_spacing: document.querySelector('#spacing-buttons .is-active')?.dataset.spacing || '1.5',
        active_theme: document.querySelector('.theme-btn.is-active')?.dataset.themeMode || 'clean_white',
        ...partial,
    };

    applyFormattingClasses(merged);

    try {
        await fetch(`/api/v1/projects/${projectId}/formatting`, {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(merged),
        });
    } catch (e) {
        console.error('Failed saving layout:', e);
    }
};

// ================= 6. CITATIONS & BIBLIOGRAPHY =================
const renderCitations = (citations) => {
    if (!citationList) return;
    citationList.innerHTML = citations.length
        ? citations.map((citation, idx) => `
            <div class="citation-card citation-item">
                <div class="citation-top">
                    <span class="citation-tag">${citation.in_text_tag || '[' + (idx + 1) + ']'}</span>
                    <button type="button" class="button button-quiet button-sm" data-insert-citation-tag="${citation.in_text_tag || '[' + (idx + 1) + ']'}" data-cite-key="${citation.key}" title="Insert into editor">Insert in text</button>
                </div>
                <strong>${citation.title}</strong>
                <p class="citation-authors">${citation.authors} (${citation.year || 'n.d.'})</p>
                <small class="citation-source">${citation.source || ''} ${citation.doi ? '· DOI: ' + citation.doi : ''}</small>
            </div>
        `).join('')
        : '<div class="loading-line">No citations added yet. Use "＋ Add citation" above.</div>';

    // Also populate quick picker in dialog
    const quickPicker = document.querySelector('#quick-citation-picker');
    if (quickPicker) {
        quickPicker.innerHTML = citations.length
            ? citations.map((citation, idx) => `
                <div class="picker-row quick-citation-option">
                    <div>
                        <span class="citation-tag">${citation.in_text_tag || '[' + (idx + 1) + ']'}</span>
                        <strong>${citation.title}</strong>
                        <small>${citation.authors} (${citation.year || ''})</small>
                    </div>
                    <button type="button" class="button button-primary button-sm" data-insert-citation-tag="${citation.in_text_tag || '[' + (idx + 1) + ']'}" data-cite-key="${citation.key}">Insert</button>
                </div>
            `).join('')
            : '<div class="loading-line">No citations in library. Switch to "Add new source" tab.</div>';
    }
};

const insertInTextCitation = (tag, key) => {
    editor.focus();
    const citationHtml = ` <span class="cite-ref" contenteditable="false" data-cite-key="${key}">${tag}</span> `;
    document.execCommand('insertHTML', false, citationHtml);
    document.querySelector('#citation-dialog')?.close();
};

const appendBibliographyToPaper = () => {
    if (!currentCitations.length) {
        alert('Please add citations to the project before generating references.');
        return;
    }

    const existingRefs = editor.querySelector('.paper-references');
    if (existingRefs) {
        existingRefs.remove();
    }

    const referencesHtml = `
        <section class="paper-references" contenteditable="false">
            <h2>References</h2>
            <ol class="references-list">
                ${currentCitations.map((c) => `
                    <li>
                        <span class="ref-authors">${c.authors}</span> (${c.year || 'n.d.'}). 
                        <strong class="ref-title">${c.title}</strong>. 
                        <em class="ref-source">${c.source || ''}</em>. 
                        ${c.doi ? '<span class="ref-doi">https://doi.org/' + c.doi + '</span>' : (c.url ? '<a href="' + c.url + '" target="_blank">' + c.url + '</a>' : '')}
                    </li>
                `).join('')}
            </ol>
        </section>
    `;

    editor.insertAdjacentHTML('beforeend', referencesHtml);
    saveState.textContent = 'Bibliography appended to paper';
};

// ================= 7. MEDIA & FIGURES =================
const renderMedia = (media) => {
    if (!mediaList) return;
    mediaList.innerHTML = media.length
        ? media.map((item) => `
            <div class="media-card">
                <div class="media-thumb-wrap">
                    <img src="${item.file_path}" alt="${item.title}" loading="lazy">
                </div>
                <div class="media-info">
                    <strong>Figure ${item.figure_number}: ${item.title}</strong>
                    <p>${item.caption || 'No caption provided.'}</p>
                    <button type="button" class="button button-quiet button-sm" data-insert-figure-id="${item.id}">Insert into paper</button>
                </div>
            </div>
        `).join('')
        : '<div class="loading-line">No figures yet. Click "＋ Add figure" to embed diagrams.</div>';
};

const insertFigureIntoEditor = (mediaItem) => {
    editor.focus();
    const figureHtml = `
        <figure class="paper-figure" contenteditable="false" data-media-id="${mediaItem.id}">
            <img src="${mediaItem.file_path}" alt="${mediaItem.title}" style="max-width: 100%;">
            <figcaption>
                <strong class="fig-num">Figure ${mediaItem.figure_number}:</strong> ${mediaItem.caption || mediaItem.title}
            </figcaption>
        </figure>
        <p></p>
    `;
    document.execCommand('insertHTML', false, figureHtml);
    document.querySelector('#media-dialog')?.close();
    saveState.textContent = 'Figure inserted into manuscript';
};

// ================= 8. EVENT HANDLERS & LISTENERS =================

// Format text buttons
document.querySelectorAll('[data-command]').forEach((button) => {
    button.addEventListener('click', () => {
        editor.focus();
        document.execCommand(button.dataset.command, false, null);
    });
});

document.querySelectorAll('[data-format-block]').forEach((button) => {
    button.addEventListener('click', () => {
        editor.focus();
        document.execCommand('formatBlock', false, `<${button.dataset.formatBlock}>`);
    });
});

// Layout switcher (Full Studio, Focus Mode, Review Split)
document.querySelectorAll('[data-layout-mode]').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('[data-layout-mode]').forEach((b) => b.classList.remove('is-active'));
        btn.classList.add('is-active');

        const mode = btn.dataset.layoutMode;
        document.body.classList.remove('layout-full', 'layout-focus', 'layout-review');
        document.body.classList.add(`layout-${mode}`);
    });
});

// Theme switcher
document.querySelectorAll('[data-theme-mode]').forEach((btn) => {
    btn.addEventListener('click', () => {
        saveFormatting({ active_theme: btn.dataset.themeMode });
    });
});

// Paper layout strip buttons
document.querySelector('#margin-buttons')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-margin]');
    if (btn) saveFormatting({ paper_margin: btn.dataset.margin });
});

document.querySelector('#column-buttons')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-column]');
    if (btn) saveFormatting({ paper_layout: btn.dataset.column });
});

document.querySelector('#font-buttons')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-font]');
    if (btn) saveFormatting({ font_family: btn.dataset.font });
});

document.querySelector('#spacing-buttons')?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-spacing]');
    if (btn) saveFormatting({ line_spacing: btn.dataset.spacing });
});

// Sidebar Tab switching
document.querySelectorAll('[data-sidebar-tab]').forEach((tabBtn) => {
    tabBtn.addEventListener('click', () => {
        document.querySelectorAll('[data-sidebar-tab]').forEach((b) => b.classList.remove('is-active'));
        document.querySelectorAll('.sidebar-panel').forEach((p) => p.classList.remove('is-active'));

        tabBtn.classList.add('is-active');
        const targetId = `tab-panel-${tabBtn.dataset.sidebarTab}`;
        document.getElementById(targetId)?.classList.add('is-active');
    });
});

// Modals Trigger Handlers
document.querySelector('#trigger-theory-modal')?.addEventListener('click', () => document.querySelector('#theory-dialog')?.showModal());
document.querySelector('#open-theory-guide')?.addEventListener('click', () => document.querySelector('#theory-dialog')?.showModal());
document.querySelector('#btn-open-templates')?.addEventListener('click', () => document.querySelector('#template-dialog')?.showModal());
document.querySelector('#btn-open-media')?.addEventListener('click', () => document.querySelector('#media-dialog')?.showModal());
document.querySelector('#btn-upload-figure-tab')?.addEventListener('click', () => document.querySelector('#media-dialog')?.showModal());
document.querySelector('#btn-open-citations')?.addEventListener('click', () => document.querySelector('#citation-dialog')?.showModal());
document.querySelector('#btn-add-citation')?.addEventListener('click', () => {
    document.querySelector('#citation-dialog')?.showModal();
    document.querySelector('#btn-cite-mode-new')?.click();
});
document.querySelector('#btn-invite-member')?.addEventListener('click', () => document.querySelector('#member-dialog')?.showModal());

// Close Dialog buttons
document.querySelectorAll('[data-close-dialog]').forEach((btn) => {
    btn.addEventListener('click', () => {
        document.getElementById(btn.dataset.closeDialog)?.close();
    });
});

// Apply methodology from theory table
document.addEventListener('click', (e) => {
    const applyMethodBtn = e.target.closest('[data-apply-method]');
    if (applyMethodBtn) {
        const method = applyMethodBtn.dataset.applyMethod;
        if (methodologySelect) {
            methodologySelect.value = method;
            updateTheoryCard(method);
        }
        document.querySelector('#theory-dialog')?.close();
        setupForm?.dispatchEvent(new Event('submit', { cancelable: true }));
    }
});

// Apply research paper template
document.addEventListener('click', (e) => {
    const tplBtn = e.target.closest('[data-template-id]');
    if (tplBtn) {
        const templateKey = tplBtn.dataset.templateId;
        const templateHtml = PAPER_TEMPLATES[templateKey];
        if (templateHtml) {
            if (editor.textContent.trim().length > 150) {
                if (!confirm('Applying this template will insert its sections into your manuscript. Proceed?')) return;
            }
            editor.innerHTML = templateHtml;
            document.querySelector('#template-dialog')?.close();
            saveState.textContent = 'Template applied to editor';
            updateManuscriptStats();
        }
    }
});

// Citation insertion
document.addEventListener('click', (e) => {
    const insertBtn = e.target.closest('[data-insert-citation-tag]');
    if (insertBtn) {
        insertInTextCitation(insertBtn.dataset.insertCitationTag, insertBtn.dataset.citeKey);
    }

    const figureBtn = e.target.closest('[data-insert-figure-id]');
    if (figureBtn) {
        const mediaId = Number(figureBtn.dataset.insertFigureId);
        const item = currentMedia.find((m) => m.id === mediaId);
        if (item) insertFigureIntoEditor(item);
    }
});

document.querySelector('#btn-append-bibliography')?.addEventListener('click', appendBibliographyToPaper);
document.querySelector('#btn-generate-references-action')?.addEventListener('click', appendBibliographyToPaper);

// Print / PDF Export
document.querySelector('#print-paper')?.addEventListener('click', () => window.print());

// Save version
document.querySelector('#save-version')?.addEventListener('click', async () => {
    saveState.textContent = 'Saving version…';
    try {
        const response = await fetch(`/api/v1/projects/${projectId}/versions`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ content: { html: editor.innerHTML } }),
        });
        if (!response.ok) throw new Error('Save failed');
        const { data } = await response.json();
        versionLabel.textContent = `Version ${data.version_number}`;
        saveState.textContent = 'All changes saved';
        versionHistory?.insertAdjacentHTML(
            'afterbegin',
            `<div class="version-row">
                <div>
                    <strong>Version ${data.version_number}</strong>
                    <small>Just now · ${data.author}</small>
                </div>
                <button type="button" class="button button-quiet button-sm" data-restore-version="${data.id}">Restore</button>
            </div>`
        );
    } catch (e) {
        saveState.textContent = 'Could not save version';
    }
});

// Version Restore
document.addEventListener('click', (e) => {
    const restoreBtn = e.target.closest('[data-restore-version]');
    if (restoreBtn) {
        if (confirm('Restore this saved manuscript draft? Current unversioned changes will be replaced.')) {
            loadWorkspace();
        }
    }
});

// Citation dialog mode toggle
document.querySelector('#btn-cite-mode-existing')?.addEventListener('click', () => {
    document.querySelector('#btn-cite-mode-existing').classList.add('is-active');
    document.querySelector('#btn-cite-mode-new').classList.remove('is-active');
    document.querySelector('#existing-citations-container').style.display = 'block';
    document.querySelector('#citation-form').style.display = 'none';
});

document.querySelector('#btn-cite-mode-new')?.addEventListener('click', () => {
    document.querySelector('#btn-cite-mode-new').classList.add('is-active');
    document.querySelector('#btn-cite-mode-existing').classList.remove('is-active');
    document.querySelector('#existing-citations-container').style.display = 'none';
    document.querySelector('#citation-form').style.display = 'block';
});

// Media Form - URL vs File toggle
document.querySelectorAll('input[name="media_source_type"]').forEach((radio) => {
    radio.addEventListener('change', () => {
        const isUrl = radio.value === 'url';
        document.querySelector('#media-url-field').style.display = isUrl ? 'block' : 'none';
        document.querySelector('#media-file-field').style.display = isUrl ? 'none' : 'block';
    });
});

// Media Form Submit
document.querySelector('#media-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorEl = document.querySelector('#media-form-error');
    errorEl.textContent = '';
    const formData = new FormData(e.target);

    try {
        const response = await fetch(`/api/v1/projects/${projectId}/media`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: formData,
        });

        if (!response.ok) {
            const err = await response.json();
            errorEl.textContent = err.message || 'Unable to upload media figure.';
            return;
        }

        const { data } = await response.json();
        currentMedia.unshift(data);
        renderMedia(currentMedia);
        insertFigureIntoEditor(data);
        e.target.reset();
    } catch (err) {
        errorEl.textContent = 'Server connection error.';
    }
});

// Citation Form Submit
document.querySelector('#citation-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorEl = document.querySelector('#citation-form-error');
    errorEl.textContent = '';
    const bodyData = Object.fromEntries(new FormData(e.target));

    try {
        const response = await fetch(`/api/v1/projects/${projectId}/citations`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(bodyData),
        });

        if (!response.ok) {
            const err = await response.json();
            errorEl.textContent = err.message || 'Unable to save citation.';
            return;
        }

        const { data } = await response.json();
        currentCitations.push(data);
        renderCitations(currentCitations);
        insertInTextCitation(data.in_text_tag, data.key);
        e.target.reset();
    } catch (err) {
        errorEl.textContent = 'Server connection error.';
    }
});

// Member Form Submit (Collaboration)
document.querySelector('#member-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorEl = document.querySelector('#member-form-error');
    errorEl.textContent = '';
    const bodyData = Object.fromEntries(new FormData(e.target));

    try {
        const response = await fetch(`/api/v1/projects/${projectId}/members`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(bodyData),
        });

        if (!response.ok) {
            const err = await response.json();
            errorEl.textContent = err.message || 'Unable to add collaborator.';
            return;
        }

        document.querySelector('#member-dialog')?.close();
        e.target.reset();
        await loadWorkspace();
    } catch (err) {
        errorEl.textContent = 'Server connection error.';
    }
});

// Task Form Submit
document.querySelector('#task-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    await fetch(`/api/v1/projects/${projectId}/tasks`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(Object.fromEntries(new FormData(e.target))),
    });
    e.target.reset();
    await loadWorkspace();
});

// Feedback Form Submit
document.querySelector('#feedback-form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    await fetch(`/api/v1/projects/${projectId}/feedback`, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(Object.fromEntries(new FormData(e.target))),
    });
    e.target.reset();
    await loadWorkspace();
});

// Setup Form Submit
setupForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    setupState.textContent = 'Saving…';
    const response = await fetch(`/api/v1/projects/${projectId}/setup`, {
        method: 'PATCH',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify(Object.fromEntries(new FormData(setupForm))),
    });
    setupState.textContent = response.ok ? 'Saved' : 'Could not save';
});

// Task & Feedback Toggles
document.addEventListener('click', async (event) => {
    const taskToggle = event.target.closest('[data-task-id]');
    const feedbackToggle = event.target.closest('[data-feedback-id]');
    if (taskToggle) {
        const status = taskToggle.dataset.taskStatus === 'done' ? 'todo' : 'done';
        await fetch(`/api/v1/tasks/${taskToggle.dataset.taskId}`, {
            method: 'PATCH',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ status }),
        });
        await loadWorkspace();
    }
    if (feedbackToggle && !feedbackToggle.parentElement.classList.contains('is-resolved')) {
        await fetch(`/api/v1/feedback/${feedbackToggle.dataset.feedbackId}`, {
            method: 'PATCH',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: 'resolved' }),
        });
        await loadWorkspace();
    }
});

// AI Assistant button
aiSummaryButton?.addEventListener('click', () => runAiSummary());

// Live Manuscript Statistics (Word count, character count, reading time)
const updateManuscriptStats = () => {
    if (!editor) return;
    const text = editor.innerText || '';
    const words = text.trim() ? text.trim().split(/\s+/).filter(Boolean).length : 0;
    const chars = text.length;
    const readMinutes = Math.max(1, Math.ceil(words / 220));

    const wordEl = document.querySelector('#stat-words');
    const charEl = document.querySelector('#stat-chars');
    const timeEl = document.querySelector('#stat-read-time');

    if (wordEl) wordEl.textContent = `${words.toLocaleString()} words`;
    if (charEl) charEl.textContent = `${chars.toLocaleString()} characters`;
    if (timeEl) timeEl.textContent = `~${readMinutes} min read`;
};

editor?.addEventListener('input', updateManuscriptStats);

// Markdown Manuscript Exporter
const convertHtmlToMarkdown = (html) => {
    const temp = document.createElement('div');
    temp.innerHTML = html;

    temp.querySelectorAll('figure').forEach((fig) => {
        const img = fig.querySelector('img');
        const caption = fig.querySelector('figcaption');
        const src = img ? img.getAttribute('src') : '';
        const capText = caption ? caption.innerText.trim() : '';
        fig.outerHTML = `\n\n![${capText}](${src})\n*${capText}*\n\n`;
    });

    temp.querySelectorAll('h1').forEach((h) => { h.outerHTML = `\n\n# ${h.innerText.trim()}\n\n`; });
    temp.querySelectorAll('h2').forEach((h) => { h.outerHTML = `\n\n## ${h.innerText.trim()}\n\n`; });
    temp.querySelectorAll('h3').forEach((h) => { h.outerHTML = `\n\n### ${h.innerText.trim()}\n\n`; });
    temp.querySelectorAll('h4').forEach((h) => { h.outerHTML = `\n\n#### ${h.innerText.trim()}\n\n`; });
    temp.querySelectorAll('blockquote').forEach((bq) => { bq.outerHTML = `\n\n> ${bq.innerText.trim()}\n\n`; });
    temp.querySelectorAll('ul li').forEach((li) => { li.outerHTML = `- ${li.innerText.trim()}\n`; });
    temp.querySelectorAll('ol li').forEach((li, idx) => { li.outerHTML = `${idx + 1}. ${li.innerText.trim()}\n`; });
    temp.querySelectorAll('.cite-ref').forEach((ref) => { ref.outerHTML = `[${ref.innerText.trim()}]`; });
    temp.querySelectorAll('p').forEach((p) => { p.outerHTML = `\n\n${p.innerText.trim()}\n\n`; });

    return temp.innerText.replace(/\n{3,}/g, '\n\n').trim();
};

document.querySelector('#btn-export-markdown')?.addEventListener('click', () => {
    const title = currentProject?.title || 'manuscript';
    const slug = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    const mdContent = `# ${title}\n\n` + convertHtmlToMarkdown(editor.innerHTML);

    const blob = new Blob([mdContent], { type: 'text/markdown;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${slug}.md`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    saveState.textContent = 'Exported manuscript to Markdown (.md)';
});

// Citation Search Filters
const filterCitations = (query) => {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.citation-item').forEach((item) => {
        item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
    document.querySelectorAll('.quick-citation-option').forEach((item) => {
        item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
};

document.querySelector('#citation-search')?.addEventListener('input', (e) => filterCitations(e.target.value));
document.querySelector('#modal-citation-search')?.addEventListener('input', (e) => filterCitations(e.target.value));

// Initial Load
loadWorkspace();