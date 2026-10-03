//

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';

const projectList = document.querySelector('#project-list');
const projectCount = document.querySelector('#project-count');
const projectDialog = document.querySelector('#project-dialog');
const projectForm = document.querySelector('#project-form');
const formError = document.querySelector('#form-error');
const readinessList = document.querySelector('#readiness-list');
const readinessSummary = document.querySelector('#readiness-summary');

const projectCard = (project) => `
	<a class="project-card" href="/projects/${project.id}"><div class="project-top"><div><h3>${project.title}</h3><p class="discipline">${project.discipline || 'Independent research'}</p></div><span class="project-status">${project.status.replace('_', ' ')}</span></div><div class="progress-label"><span>Project progress</span><strong>${project.progress}%</strong></div><div class="progress-track"><div class="progress-bar" style="width: ${project.progress}%"></div></div><div class="project-footer"><div class="member-stack"><span>MS</span>${project.member_count > 1 ? '<span>+ ' + (project.member_count - 1) + '</span>' : ''}</div><span>${project.last_activity || 'Just created'}</span></div></a>`;

const loadProjects = async () => {
	if (!projectList || !projectCount) return;

	const response = await fetch('/api/v1/projects', { headers: { Accept: 'application/json' } });
	if (!response.ok) throw new Error('Unable to load projects');
	const { data } = await response.json();
	projectCount.textContent = data.length;
	projectList.innerHTML = data.length ? data.map(projectCard).join('') : '<div class="empty-state">No research spaces yet. Start your first shared project above.</div>';
};

const loadReadiness = async () => {
	if (!readinessList) return;

	const response = await fetch('/api/v1/integrations/health', { headers: { Accept: 'application/json' } });
	if (!response.ok) throw new Error('Unable to check integrations');
	const { data } = await response.json();
	const services = [['ai', 'Research assistant'], ['supabase', 'Hosted sync'], ['reverb', 'Live collaboration']];
	const readyCount = services.filter(([key]) => data[key].status === 'ready' || data[key].status === 'configured').length;
	readinessSummary.textContent = `${readyCount}/${services.length} connected`;
	readinessList.innerHTML = services.map(([key, label]) => `<div class="readiness-item"><span class="readiness-dot ${data[key].status}"></span><div><strong>${label}</strong><small>${data[key].label} · ${data[key].detail}</small></div></div>`).join('');
};

document.querySelectorAll('[data-open-project-form]').forEach((button) => button.addEventListener('click', () => projectDialog.showModal()));
projectForm?.addEventListener('submit', async (event) => {
	event.preventDefault();
	formError.textContent = '';
	const response = await fetch('/api/v1/projects', { method: 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify(Object.fromEntries(new FormData(projectForm))) });
	if (!response.ok) { formError.textContent = 'Please check the project details and try again.'; return; }
	projectForm.reset(); projectDialog.close(); await loadProjects();
});

loadProjects().catch(() => { projectList.innerHTML = '<div class="empty-state">The workspace is getting ready. Run the migrations to connect your research spaces.</div>'; });
loadReadiness().catch(() => { if (readinessList) readinessList.innerHTML = '<div class="loading-line">Connectivity status is temporarily unavailable.</div>'; });
