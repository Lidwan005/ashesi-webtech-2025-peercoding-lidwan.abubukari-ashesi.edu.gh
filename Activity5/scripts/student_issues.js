// Student Issues Management

function loadStudentIssues() {
    const container = document.getElementById('issues-container');
    if (!container) return;

    container.innerHTML = '<p>Loading issues...</p>';

    const formData = new FormData();
    formData.append('action', 'get_student_issues');

    fetch('actions.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                displayIssues(data.issues);
            } else {
                container.innerHTML = '<p style="color: #e74c3c;">Failed to load issues.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p style="color: #e74c3c;">Failed to load issues.</p>';
        });
}

function displayIssues(issues) {
    const container = document.getElementById('issues-container');

    if (!issues || issues.length === 0) {
        container.innerHTML = '<p>No issues reported yet.</p>';
        return;
    }

    let html = '';

    issues.forEach(issue => {
        const statusColor = {
            'pending': '#f39c12',
            'resolved': '#2ecc71',
            'dismissed': '#95a5a6'
        }[issue.status] || '#f39c12';

        const statusBadge = `<span style="background: ${statusColor}; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.9em;">${issue.status.toUpperCase()}</span>`;

        html += `
            <div class="issue-card" style="background: #fff; padding: 20px; margin: 15px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid ${statusColor};">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 10px 0; color: #2c3e50;">${escapeHtml(issue.subject)}</h3>
                        <p style="margin: 0; color: #7f8c8d; font-size: 0.9em;">
                            <strong>Student:</strong> ${escapeHtml(issue.student_name)} (${escapeHtml(issue.student_email)})
                        </p>
                        ${issue.course_code ? `
                            <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 0.9em;">
                                <strong>Course:</strong> ${escapeHtml(issue.course_code)} - ${escapeHtml(issue.course_name)}
                            </p>
                        ` : ''}
                        ${issue.session_date ? `
                            <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 0.9em;">
                                <strong>Session:</strong> ${issue.session_date} at ${issue.start_time}
                            </p>
                        ` : ''}
                        <p style="margin: 5px 0 0 0; color: #7f8c8d; font-size: 0.9em;">
                            <strong>Reported:</strong> ${new Date(issue.created_at).toLocaleString()}
                        </p>
                    </div>
                    <div>
                        ${statusBadge}
                    </div>
                </div>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                    <p style="margin: 0; color: #2c3e50; line-height: 1.6;">${escapeHtml(issue.description)}</p>
                </div>

                ${issue.status === 'pending' ? `
                    <div style="display: flex; gap: 10px;">
                        <button onclick="resolveIssue(${issue.issue_id}, 'resolved')" class="btn-approve">
                            Mark as Resolved
                        </button>
                        <button onclick="resolveIssue(${issue.issue_id}, 'dismissed')" class="btn-reject">
                            Dismiss
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    });

    container.innerHTML = html;
}

function resolveIssue(issueId, newStatus) {
    if (!confirm(`Are you sure you want to mark this issue as ${newStatus}?`)) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'resolve_issue');
    formData.append('issue_id', issueId);
    formData.append('new_status', newStatus);

    fetch('actions.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadStudentIssues(); // Reload the list
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update issue status');
        });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Load issues when the issues tab is clicked
document.addEventListener('DOMContentLoaded', function () {
    const issuesLink = document.querySelector('[data-section="issues"]');
    if (issuesLink) {
        issuesLink.addEventListener('click', loadStudentIssues);
    }
});
