// Enrollment Management Functions

function loadAvailableCourses() {
    const formData = new FormData();
    formData.append('action', 'get_available_courses');

    fetch('actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayCourses(data.courses);
            } else {
                console.error('Failed to load courses:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading courses:', error);
        });
}

function displayCourses(courses) {
    const container = document.getElementById('courses-list');
    if (!container) return;

    if (courses.length === 0) {
        container.innerHTML = '<p>No courses available at this time.</p>';
        return;
    }

    let html = '';
    courses.forEach(course => {
        const status = course.enrollment_status;
        let statusBadge = '';
        let actionButton = '';

        if (status === 'approved') {
            statusBadge = '<span class="enrolled-badge">Enrolled</span>';
        } else if (status === 'pending') {
            statusBadge = '<span class="pending-badge">Request Pending</span>';
        } else if (status === 'rejected') {
            statusBadge = '<span class="rejected-badge">Request Rejected</span>';
            actionButton = `<button class="btn-primary" onclick="enrollCourse(${course.course_id})">Request Again</button>`;
        } else {
            actionButton = `<button class="btn-primary" onclick="enrollCourse(${course.course_id})">Request to Join</button>`;
        }

        html += `
            <div class="course-card ${status === 'approved' ? 'enrolled' : ''}">
                <h3>${course.course_code}</h3>
                <p class="course-name">${course.course_name}</p>
                <div class="course-details">
                    <span><strong>Semester:</strong> ${course.semester}</span>
                    <span><strong>Credits:</strong> ${course.credits}</span>
                    <span><strong>Faculty:</strong> ${course.faculty_name || 'TBA'}</span>
                </div>
                ${statusBadge}
                ${actionButton}
            </div>
        `;
    });

    container.innerHTML = html;
}

function enrollCourse(courseId) {
    if (!confirm('Submit enrollment request for this course?')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'enroll');
    formData.append('course_id', courseId);

    fetch('actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                loadAvailableCourses(); // Reload to update UI
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error submitting request:', error);
            alert('Failed to submit request. Please try again.');
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('courses-list')) {
        loadAvailableCourses();
    }
});
