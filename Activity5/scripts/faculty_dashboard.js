document.addEventListener('DOMContentLoaded', function () {
    // Navigation
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.content-section');

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1) + '-section';

            // Update active link
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            // Update active section
            sections.forEach(s => s.classList.remove('active'));
            document.getElementById(targetId).classList.add('active');

            // Load requests when navigating to requests section
            if (targetId === 'requests-section' && typeof loadEnrollmentRequests === 'function') {
                loadEnrollmentRequests();
            }
        });
    });

    // Modals
    const courseModal = document.getElementById('course-modal');
    const sessionModal = document.getElementById('session-modal');

    document.getElementById('add-course-btn').addEventListener('click', () => {
        courseModal.style.display = 'block';
    });

    document.getElementById('add-session-btn').addEventListener('click', () => {
        sessionModal.style.display = 'block';
    });

    // Close modals when clicking outside
    window.onclick = function (event) {
        if (event.target == courseModal) courseModal.style.display = "none";
        if (event.target == sessionModal) sessionModal.style.display = "none";
    }

    // Handle Forms
    document.getElementById('course-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('actions.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    location.reload(); // Reload to show new course
                } else {
                    alert(data.message);
                }
            });
    });

    document.getElementById('session-form').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('actions.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    sessionModal.style.display = 'none';
                    location.reload(); // Reload to show new session
                } else {
                    alert(data.message);
                }
            });
    });
});

// Generate Attendance Code
function generateCode(sessionId) {
    console.log('generateCode called with sessionId:', sessionId);

    if (!confirm('Generate attendance code for this session?')) {
        console.log('User cancelled code generation');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'generate_attendance_code');
    formData.append('session_id', sessionId);

    console.log('Sending request to generate code...');

    fetch('actions.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            console.log('Response received:', data);
            if (data.status === 'success') {
                alert(`Attendance code generated: ${data.code}\nExpires: ${data.expires_at}`);
                location.reload(); // Reload to show the code
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to generate code. Check console for details.');
        });
}

// Copy Code to Clipboard
function copyCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert('Code copied to clipboard: ' + code);
    }).catch(err => {
        console.error('Failed to copy:', err);
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Code copied to clipboard: ' + code);
    });
}

// Edit Session
function editSession(sessionId) {
    // Find the session card
    const sessionCard = document.querySelector(`[data-session-id="${sessionId}"]`);
    if (!sessionCard) return;

    // Extract current values (this is a simple implementation)
    alert('Edit functionality: Please delete and recreate the session for now, or implement a full edit modal.');
    // TODO: Implement full edit modal with pre-filled values
}

// Delete Session
function deleteSession(sessionId) {
    if (!confirm('Are you sure you want to delete this session? This will also delete all attendance records for this session.')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete_session');
    formData.append('session_id', sessionId);

    fetch('actions.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                location.reload(); // Reload to update the list
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete session');
        });
}

