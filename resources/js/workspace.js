const projectId = document.body.dataset.projectId;
const editor = document.querySelector('#editor-canvas');
const memberList = document.querySelector('#member-list');
const versionHistory = document.querySelector('#version-history');
const versionLabel = document.querySelector('#version-label');
const saveState = document.querySelector('#save-state');
const taskList = document.querySelector('#task-list');
const feedbackList = document.querySelector('#feedback-list');
const aiSummaryButton = document.querySelector('#ai-summary');
const aiResult = document.querySelector('#ai-result');

if (window.subscribeToProject) {
    window.subscribeToProject(projectId, (payload) => {
        versionLabel.textContent = `Version ${payload.version_number}`;
        saveState.textContent = 'A collaborator saved a new version';
    });
}

const loadWorkspace = async () => {
    const [projectResponse, taskResponse, feedbackResponse] = await Promise.all([
        fetch(`/api/v1/projects/${projectId}`, { headers: { Accept: 'application/json' } }),
        fetch(`/api/v1/projects/${projectId}/tasks`, { headers: { Accept: 'application/json' } }),
        fetch(`/api/v1/projects/${projectId}/feedback`, { headers: { Accept: 'application/json' } }),
    ]);
    const { data } = await projectResponse.json();
    const { data: tasks } = await taskResponse.json();
    const { data: feedback } = await feedbackResponse.json();
    memberList.innerHTML = data.members.map((member) => `<div class="member-row"><span class="avatar ${member.role === 'teacher' ? 'avatar-coral' : 'avatar-teal'}">${member.name.split(' ').map((part) => part[0]).join('').slice(0, 2)}</span><div><strong>${member.name}</strong><small>${member.role}</small></div><span class="member-online">●</span></div>`).join('');
    if (data.latest_version) { versionLabel.textContent = `Version ${data.latest_version.version_number}`; versionHistory.innerHTML = `<div class="version-row"><strong>Version ${data.latest_version.version_number}</strong><small>Saved by ${data.latest_version.content.author || 'your team'}</small></div>`; }
    taskList.innerHTML = tasks.length ? tasks.map((task) => `<div class="task-row"><button class="task-toggle ${task.status === 'done' ? 'is-done' : ''}" data-task-id="${task.id}" data-task-status="${task.status}" type="button">${task.status === 'done' ? '✓' : '○'}</button><span>${task.title}</span></div>`).join('') : '<div class="loading-line">No tasks yet.</div>';
    document.querySelector('#task-count').textContent = tasks.filter((task) => task.status !== 'done').length;
    feedbackList.innerHTML = feedback.length ? feedback.map((item) => `<div class="feedback-item ${item.status === 'resolved' ? 'is-resolved' : ''}"><strong>${item.author.name}</strong><p>${item.body}</p><button class="text-button" data-feedback-id="${item.id}" type="button">${item.status === 'resolved' ? 'Resolved' : 'Mark resolved'}</button></div>`).join('') : '<div class="loading-line">No feedback yet.</div>';
    document.querySelector('#feedback-count').textContent = feedback.filter((item) => item.status === 'open').length;
};

document.querySelectorAll('[data-command]').forEach((button) => button.addEventListener('click', () => { editor.focus(); document.execCommand(button.dataset.command); }));
document.querySelector('#save-version').addEventListener('click', async () => {
    saveState.textContent = 'Saving…';
    const response = await fetch(`/api/v1/projects/${projectId}/versions`, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ content: { html: editor.innerHTML } }) });
    if (!response.ok) { saveState.textContent = 'Could not save'; return; }
    const { data } = await response.json();
    versionLabel.textContent = `Version ${data.version_number}`; saveState.textContent = 'All changes saved'; versionHistory.insertAdjacentHTML('afterbegin', `<div class="version-row"><strong>Version ${data.version_number}</strong><small>Just now · ${data.author}</small></div>`);
});

document.querySelector('#task-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    await fetch(`/api/v1/projects/${projectId}/tasks`, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(Object.fromEntries(new FormData(event.target))) });
    event.target.reset(); await loadWorkspace();
});

document.querySelector('#feedback-form').addEventListener('submit', async (event) => {
    event.preventDefault();
    await fetch(`/api/v1/projects/${projectId}/feedback`, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(Object.fromEntries(new FormData(event.target))) });
    event.target.reset(); await loadWorkspace();
});

document.addEventListener('click', async (event) => {
    const taskToggle = event.target.closest('[data-task-id]');
    const feedbackToggle = event.target.closest('[data-feedback-id]');
    if (taskToggle) { const status = taskToggle.dataset.taskStatus === 'done' ? 'todo' : 'done'; await fetch(`/api/v1/tasks/${taskToggle.dataset.taskId}`, { method: 'PATCH', headers: { Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ status }) }); await loadWorkspace(); }
    if (feedbackToggle && !feedbackToggle.parentElement.classList.contains('is-resolved')) { await fetch(`/api/v1/feedback/${feedbackToggle.dataset.feedbackId}`, { method: 'PATCH', headers: { Accept: 'application/json', 'Content-Type': 'application/json' }, body: JSON.stringify({ status: 'resolved' }) }); await loadWorkspace(); }
});

aiSummaryButton.addEventListener('click', async () => {
    aiSummaryButton.disabled = true;
    aiSummaryButton.textContent = 'Thinking…';
    aiResult.textContent = '';
    const response = await fetch(`/api/v1/projects/${projectId}/ai/summary`, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({}) });
    const payload = await response.json();
    aiSummaryButton.disabled = false;
    aiSummaryButton.textContent = 'Ask research assistant';
    aiResult.textContent = response.ok ? payload.data.content : payload.message;
});

loadWorkspace().catch(() => { memberList.innerHTML = '<div class="loading-line">Unable to load collaborators.</div>'; });