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
        const isEnrolled = course.is_enrolled > 0;
        html += `
            <div class="course-card ${isEnrolled ? 'enrolled' : ''}">
                <h3>${course.course_code}</h3>
                <p class="course-name">${course.course_name}</p>
                <div class="course-details">
                    <span><strong>Semester:</strong> ${course.semester}</span>
                    <span><strong>Credits:</strong> ${course.credits}</span>
                    <span><strong>Faculty:</strong> ${course.faculty_name || 'TBA'}</span>
                </div>
                ${isEnrolled
                ? '<span class="enrolled-badge">Enrolled</span>'
                : `<button class="btn-primary" onclick="enrollCourse(${course.course_id})">Enroll</button>`
            }
            </div>
        `;
    });

    container.innerHTML = html;
}

function enrollCourse(courseId) {
    if (!confirm('Are you sure you want to enroll in this course?')) {
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
            console.error('Error enrolling:', error);
            alert('Failed to enroll. Please try again.');
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('courses-list')) {
        loadAvailableCourses();
    }
});
