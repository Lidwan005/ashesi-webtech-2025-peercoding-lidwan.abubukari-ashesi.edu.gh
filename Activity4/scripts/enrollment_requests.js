// Enrollment Requests Management for Faculty

function loadEnrollmentRequests() {
    const formData = new FormData();
    formData.append('action', 'get_pending_requests');

    fetch('actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayRequests(data.requests);
            } else {
                console.error('Failed to load requests:', data.message);
                document.getElementById('requests-container').innerHTML = '<p>Error loading requests.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading requests:', error);
            document.getElementById('requests-container').innerHTML = '<p>Error loading requests.</p>';
        });
}

function displayRequests(requests) {
    const container = document.getElementById('requests-container');
    if (!container) return;

    if (requests.length === 0) {
        container.innerHTML = '<p>No pending enrollment requests.</p>';
        return;
    }

    let html = '';
    requests.forEach(request => {
        html += `
            <div class="request-card" data-request-id="${request.enrollment_id}">
                <div class="request-header">
                    <div class="request-info">
                        <h4>${request.student_name}</h4>
                        <p><strong>Email:</strong> ${request.student_email}</p>
                        <p><strong>Course:</strong> ${request.course_code} - ${request.course_name}</p>
                        <p><strong>Requested:</strong> ${new Date(request.enrolled_at).toLocaleDateString()}</p>
                    </div>
                    <div class="request-actions">
                        <button class="btn-approve" onclick="approveRequest(${request.enrollment_id})">Approve</button>
                        <button class="btn-reject" onclick="rejectRequest(${request.enrollment_id})">Reject</button>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function approveRequest(enrollmentId) {
    if (!confirm('Approve this enrollment request?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'approve_request');
    formData.append('enrollment_id', enrollmentId);

    fetch('actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadEnrollmentRequests(); // Reload requests
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error approving request:', error);
            alert('Failed to approve request. Please try again.');
        });
}

function rejectRequest(enrollmentId) {
    if (!confirm('Reject this enrollment request?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'reject_request');
    formData.append('enrollment_id', enrollmentId);

    fetch('actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadEnrollmentRequests(); // Reload requests
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error rejecting request:', error);
            alert('Failed to reject request. Please try again.');
        });
}

// Load requests when navigating to requests section
document.addEventListener('DOMContentLoaded', function () {
    // Load requests on initial page load if on requests section
    const requestsSection = document.getElementById('requests-section');
    if (requestsSection && requestsSection.classList.contains('active')) {
        loadEnrollmentRequests();
    }
});
