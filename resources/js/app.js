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

const projectCard = (project) => `
	<a class="project-card" href="/projects/${project.id}"><div class="project-top"><div><h3>${project.title}</h3><p class="discipline">${project.discipline || 'Independent research'}</p></div><span class="project-status">${project.status.replace('_', ' ')}</span></div><div class="progress-label"><span>Project progress</span><strong>${project.progress}%</strong></div><div class="progress-track"><div class="progress-bar" style="width: ${project.progress}%"></div></div><div class="project-footer"><div class="member-stack"><span>MS</span>${project.member_count > 1 ? '<span>+ ' + (project.member_count - 1) + '</span>' : ''}</div><span>${project.last_activity || 'Just created'}</span></div></a>`;

const loadProjects = async () => {
	const response = await fetch('/api/v1/projects', { headers: { Accept: 'application/json' } });
	if (!response.ok) throw new Error('Unable to load projects');
	const { data } = await response.json();
	projectCount.textContent = data.length;
	projectList.innerHTML = data.length ? data.map(projectCard).join('') : '<div class="empty-state">No research spaces yet. Start your first shared project above.</div>';
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
