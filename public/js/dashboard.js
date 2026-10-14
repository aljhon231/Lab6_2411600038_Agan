const dashboardConfig = document.querySelector('[data-dashboard-stats]');

if (dashboardConfig) {
    const statTargets = {
        total_students: document.getElementById('stat-students'),
        total_courses: document.getElementById('stat-courses'),
        total_enrollments: document.getElementById('stat-enrollments'),
        almost_full_courses: document.getElementById('stat-almost-full'),
    };

    const status = document.getElementById('poll-status');

    async function fetchJson(url) {
        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(`Dashboard request failed (${response.status}).`);
        }

        return response.json();
    }

    function element(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    async function updateStats() {
        try {
            const stats = await fetchJson(dashboardConfig.dataset.dashboardStats);
            for (const [key, target] of Object.entries(statTargets)) {
                if (target) target.textContent = stats[key];
            }
        } catch (error) {
            status.textContent = error.message;
        }
    }

    async function updateAlmostFullCourses() {
        const list = document.getElementById('almost-full-list');
        if (!list) return;

        const result = await fetchJson(dashboardConfig.dataset.almostFull);
        list.replaceChildren();
        if (result.data.length === 0) {
            list.append(element('div', 'empty-inline', 'No courses are close to full right now.'));
            return;
        }

        for (const course of result.data) {
            const link = element('a', 'alert-course');
            link.href = `/courses/${encodeURIComponent(course.id)}`;
            link.append(element('span', 'alert-dot'));
            const details = element('span');
            details.append(element('strong', '', course.name), element('small', '', course.code));
            link.append(details);
            link.append(element('b', '', `${course.available_slots} ${course.available_slots === 1 ? 'slot' : 'slots'}`));
            list.append(link);
        }
    }

    async function updateRecentTransactions() {
        const tableHost = document.getElementById('recent-enrollments-table');
        if (!tableHost) return;

        const result = await fetchJson(dashboardConfig.dataset.recent);
        const table = element('div', 'table-wrap');
        const header = element('thead');
        const heading = element('tr');
        for (const label of ['Date', 'Student', 'Course', 'Action', 'Slot change']) {
            heading.append(element('th', '', label));
        }
        header.append(heading);
        const body = element('tbody');

        if (result.data.length === 0) {
            const row = element('tr');
            const cell = element('td', 'table-empty', 'No enrollment activity yet.');
            cell.colSpan = 5;
            row.append(cell);
            body.append(row);
        }

        for (const transaction of result.data) {
            const row = element('tr');
            row.append(element('td', '', new Date(transaction.created_at).toLocaleString()));
            row.append(element('td', '', transaction.user.name));
            const courseCell = element('td');
            const courseLink = element('a', 'text-link', transaction.course.name);
            courseLink.href = `/courses/${encodeURIComponent(transaction.course.id)}`;
            courseCell.append(courseLink);
            row.append(courseCell);
            const typeCell = element('td');
            typeCell.append(element('span', `type-badge type-${transaction.type}`, `${transaction.type[0].toUpperCase()}${transaction.type.slice(1)}`));
            row.append(typeCell);
            const quantity = transaction.quantity > 0 ? `+${transaction.quantity}` : `${transaction.quantity}`;
            row.append(element('td', transaction.quantity < 0 ? 'negative' : 'positive', quantity));
            body.append(row);
        }

        const tableElement = element('table');
        tableElement.append(header, body);
        table.append(tableElement);
        tableHost.replaceChildren(table);
    }

    async function refreshDashboard() {
        await updateStats();
        try {
            await Promise.all([updateAlmostFullCourses(), updateRecentTransactions()]);
            status.textContent = `Updated at ${new Date().toLocaleTimeString()}. Refreshes every 30 seconds.`;
        } catch (error) {
            status.textContent = error.message;
        }
    }

    refreshDashboard();
    window.setInterval(refreshDashboard, 30_000);
}
