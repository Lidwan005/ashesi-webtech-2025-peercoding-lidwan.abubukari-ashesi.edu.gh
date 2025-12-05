// Attendance Management Functions

function loadSessions() {
    // This will be called from faculty dashboard
    const coursesContainer = document.getElementById('sessions-list');
    if (!coursesContainer) return;

    // Sessions are already loaded in the PHP, we just need to add attendance buttons
}

function takeAttendance(sessionId) {
    const formData = new FormData();
    formData.append('action', 'get_session_students');
    formData.append('session_id', sessionId);

    fetch('actions.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showAttendanceModal(sessionId, data.students);
            } else {
                alert('Failed to load students: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error loading students:', error);
            alert('Failed to load students. Please try again.');
        });
}

function showAttendanceModal(sessionId, students) {
    let modalHtml = `
        <div class="modal" id="attendance-modal">
            <div class="modal-content">
                <span class="close" onclick="closeAttendanceModal()">&times;</span>
                <h3>Take Attendance</h3>
                <div class="students-list">
    `;

    students.forEach(student => {
        const currentStatus = student.attendance_status || '';
        modalHtml += `
            <div class="student-item">
                <span class="student-name">${student.full_name}</span>
                <span class="student-email">${student.email}</span>
                <select class="attendance-select" data-student-id="${student.user_id}">
                    <option value="">Select Status</option>
                    <option value="present" ${currentStatus === 'present' ? 'selected' : ''}>Present</option>
                    <option value="absent" ${currentStatus === 'absent' ? 'selected' : ''}>Absent</option>
                    <option value="late" ${currentStatus === 'late' ? 'selected' : ''}>Late</option>
                    <option value="excused" ${currentStatus === 'excused' ? 'selected' : ''}>Excused</option>
                </select>
            </div>
        `;
    });

    modalHtml += `
                </div>
                <button class="btn-primary" onclick="saveAttendance(${sessionId})">Save Attendance</button>
            </div>
        </div>
    `;

    // Remove old modal if exists
    const oldModal = document.getElementById('attendance-modal');
    if (oldModal) oldModal.remove();

    // Add new modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    document.getElementById('attendance-modal').style.display = 'block';
}

function closeAttendanceModal() {
    const modal = document.getElementById('attendance-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.remove();
    }
}

function saveAttendance(sessionId) {
    const selects = document.querySelectorAll('.attendance-select');
    const promises = [];

    selects.forEach(select => {
        const status = select.value;
        if (!status) return; // Skip if no status selected

        const studentId = select.dataset.studentId;
        const formData = new FormData();
        formData.append('action', 'record_attendance');
        formData.append('session_id', sessionId);
        formData.append('student_id', studentId);
        formData.append('status', status);

        promises.push(
            fetch('actions.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
        );
    });

    Promise.all(promises)
        .then(results => {
            const failed = results.filter(r => r.status !== 'success');
            if (failed.length === 0) {
                alert('Attendance saved successfully!');
                closeAttendanceModal();
            } else {
                alert('Some attendance records failed to save. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error saving attendance:', error);
            alert('Failed to save attendance. Please try again.');
        });
}
