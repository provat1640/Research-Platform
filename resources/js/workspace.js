const projectId = document.body.dataset.projectId;
const editor = document.querySelector('#editor-canvas');
const memberList = document.querySelector('#member-list');
const versionHistory = document.querySelector('#version-history');
const versionLabel = document.querySelector('#version-label');
const saveState = document.querySelector('#save-state');

const loadWorkspace = async () => {
    const response = await fetch(`/api/v1/projects/${projectId}`, { headers: { Accept: 'application/json' } });
    const { data } = await response.json();
    memberList.innerHTML = data.members.map((member) => `<div class="member-row"><span class="avatar ${member.role === 'teacher' ? 'avatar-coral' : 'avatar-teal'}">${member.name.split(' ').map((part) => part[0]).join('').slice(0, 2)}</span><div><strong>${member.name}</strong><small>${member.role}</small></div><span class="member-online">●</span></div>`).join('');
    if (data.latest_version) { versionLabel.textContent = `Version ${data.latest_version.version_number}`; versionHistory.innerHTML = `<div class="version-row"><strong>Version ${data.latest_version.version_number}</strong><small>Saved by ${data.latest_version.content.author || 'your team'}</small></div>`; }
};

document.querySelectorAll('[data-command]').forEach((button) => button.addEventListener('click', () => { editor.focus(); document.execCommand(button.dataset.command); }));
document.querySelector('#save-version').addEventListener('click', async () => {
    saveState.textContent = 'Saving…';
    const response = await fetch(`/api/v1/projects/${projectId}/versions`, { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ content: { html: editor.innerHTML } }) });
    if (!response.ok) { saveState.textContent = 'Could not save'; return; }
    const { data } = await response.json();
    versionLabel.textContent = `Version ${data.version_number}`; saveState.textContent = 'All changes saved'; versionHistory.insertAdjacentHTML('afterbegin', `<div class="version-row"><strong>Version ${data.version_number}</strong><small>Just now · ${data.author}</small></div>`);
});

loadWorkspace().catch(() => { memberList.innerHTML = '<div class="loading-line">Unable to load collaborators.</div>'; });