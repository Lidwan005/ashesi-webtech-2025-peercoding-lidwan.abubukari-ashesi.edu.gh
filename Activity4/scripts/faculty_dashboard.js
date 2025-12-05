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
                    // Ideally reload sessions list here
                } else {
                    alert(data.message);
                }
            });
    });
});
