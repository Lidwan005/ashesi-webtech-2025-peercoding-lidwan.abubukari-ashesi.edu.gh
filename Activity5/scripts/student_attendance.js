// Student Attendance Functions

document.addEventListener('DOMContentLoaded', function () {
    // Load statistics when navigating to stats section
    const statsLink = document.querySelector('[data-section="stats"]');
    if (statsLink) {
        statsLink.addEventListener('click', loadAttendanceStats);
    }

    // Handle attendance code form submission
    const codeForm = document.getElementById('attendance-code-form');
    if (codeForm) {
        codeForm.addEventListener('submit', function (e) {
            e.preventDefault();
            markAttendanceWithCode();
        });
    }

    // Auto-uppercase code input
    const codeInput = document.getElementById('attendance-code-input');
    if (codeInput) {
        codeInput.addEventListener('input', function (e) {
            e.target.value = e.target.value.toUpperCase();
        });
    }
});

// Mark Attendance with Code
function markAttendanceWithCode() {
    const codeInput = document.getElementById('attendance-code-input');
    const resultDiv = document.getElementById('attendance-result');
    const code = codeInput.value.trim();

    if (!code) {
        resultDiv.innerHTML = '<p style="color: #e74c3c;">Please enter an attendance code.</p>';
        return;
    }

    resultDiv.innerHTML = '<p style="color: #3498db;">Submitting...</p>';

    const formData = new FormData();
    formData.append('action', 'mark_attendance_with_code');
    formData.append('code', code);

    fetch('actions.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                resultDiv.innerHTML = `
                    <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 4px;">
                        <h4 style="margin: 0 0 10px 0;">✓ Success!</h4>
                        <p style="margin: 0;">${data.message}</p>
                        <p style="margin: 10px 0 0 0; font-weight: bold;">${data.course}</p>
                    </div>
                `;
                codeInput.value = '';

                // Reload page after 2 seconds to update attendance history
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                resultDiv.innerHTML = `
                    <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px;">
                        <h4 style="margin: 0 0 10px 0;">✗ Error</h4>
                        <p style="margin: 0;">${data.message}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.innerHTML = `
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 4px;">
                    <p style="margin: 0;">Failed to submit attendance. Please try again.</p>
                </div>
            `;
        });
}

// Load Attendance Statistics
function loadAttendanceStats() {
    const container = document.getElementById('stats-container');
    container.innerHTML = '<p>Loading statistics...</p>';

    const formData = new FormData();
    formData.append('action', 'get_student_attendance_stats');

    fetch('actions.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                displayStats(data.stats);
            } else {
                container.innerHTML = '<p style="color: #e74c3c;">Failed to load statistics.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = '<p style="color: #e74c3c;">Failed to load statistics.</p>';
        });
}

// Display Statistics
function displayStats(stats) {
    const container = document.getElementById('stats-container');

    if (!stats || stats.length === 0) {
        container.innerHTML = '<p>No enrollment data available. Enroll in courses to see your attendance statistics.</p>';
        return;
    }

    let html = '<div style="display: grid; gap: 20px;">';

    stats.forEach(course => {
        const percentage = course.attendance_percentage || 0;
        const total = course.total_sessions || 0;
        const attended = course.attended_sessions || 0;

        // Determine color based on percentage
        let barColor = '#e74c3c'; // Red for low
        if (percentage >= 75) barColor = '#2ecc71'; // Green for good
        else if (percentage >= 50) barColor = '#f39c12'; // Orange for medium

        html += `
            <div class="course-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="margin: 0 0 10px 0; color: #2c3e50;">${course.course_code}</h3>
                <p style="margin: 0 0 15px 0; color: #7f8c8d;">${course.course_name}</p>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span>Attendance Rate:</span>
                    <strong style="font-size: 1.2em; color: ${barColor};">${percentage}%</strong>
                </div>
                
                <div style="background: #ecf0f1; height: 20px; border-radius: 10px; overflow: hidden; margin-bottom: 15px;">
                    <div style="background: ${barColor}; height: 100%; width: ${percentage}%; transition: width 0.3s;"></div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 0.9em;">
                    <div>
                        <div style="color: #7f8c8d;">Total Sessions</div>
                        <div style="font-size: 1.3em; font-weight: bold;">${total}</div>
                    </div>
                    <div>
                        <div style="color: #7f8c8d;">Attended</div>
                        <div style="font-size: 1.3em; font-weight: bold; color: ${barColor};">${attended}</div>
                    </div>
                    <div>
                        <div style="color: #7f8c8d;">Present</div>
                        <div style="font-size: 1.1em; color: #2ecc71;">${course.present_count || 0}</div>
                    </div>
                    <div>
                        <div style="color: #7f8c8d;">Late</div>
                        <div style="font-size: 1.1em; color: #f39c12;">${course.late_count || 0}</div>
                    </div>
                    <div>
                        <div style="color: #7f8c8d;">Absent</div>
                        <div style="font-size: 1.1em; color: #e74c3c;">${course.absent_count || 0}</div>
                    </div>
                    <div>
                        <div style="color: #7f8c8d;">Excused</div>
                        <div style="font-size: 1.1em; color: #3498db;">${course.excused_count || 0}</div>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}
