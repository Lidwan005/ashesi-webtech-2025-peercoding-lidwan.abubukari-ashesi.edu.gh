<?php
require 'auth_session.php';
require 'db_connect.php';
requireRole('student');

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['name'];

// Fetch Attendance History - only for approved enrollments
// Joining attendance with sessions and courses to get details
$query = "
    SELECT 
        c.course_code, 
        c.course_name, 
        s.session_date, 
        s.start_time, 
        s.session_type, 
        a.status 
    FROM attendance a
    JOIN sessions s ON a.session_id = s.session_id
    JOIN courses c ON s.course_id = c.course_id
    JOIN enrollments e ON e.course_id = c.course_id AND e.student_id = a.student_id
    WHERE a.student_id = ? AND e.status = 'approved'
    ORDER BY s.session_date DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$attendance_records = [];
while ($row = $result->fetch_assoc()) {
    $attendance_records[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Attendance System</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <style>
        /* Mobile friendly adjustments */
        @media (max-width: 768px) {
            .nav-container { flex-direction: column; }
            .nav-menu { flex-direction: column; width: 100%; }
            .nav-item { margin: 5px 0; }
            .attendance-table { display: block; overflow-x: auto; }
        }
        
        .attendance-card {
            background: #fff;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 5px solid #ccc;
        }
        .status-present { border-left-color: #2ecc71; }
        .status-absent { border-left-color: #e74c3c; }
        .status-late { border-left-color: #f1c40f; }
        
        .session-type-lab { background-color: #e8f6f3; }
        .session-type-practical { background-color: #fef9e7; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fff; margin: 15% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; position: relative; }
        .close { position: absolute; right: 20px; top: 10px; font-size: 28px; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        
        /* Styles for courses */
        .course-card { background: #fff; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #3498db; }
        .course-card.enrolled { border-left-color: #2ecc71; }
        .course-name { font-size: 1.1em; color: #555; margin: 5px 0; }
        .course-details { display: flex; gap: 15px; flex-wrap: wrap; margin: 10px 0; font-size: 0.9em; color: #666; }
        .enrolled-badge { background: #2ecc71; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.9em; margin-right: 8px; }
        .pending-badge { background: #f39c12; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.9em; margin-right: 8px; }
        .rejected-badge { background: #e74c3c; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.9em; margin-right: 8px; }
        .content-section { display: none; }
        .content-section.active { display: block; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>Attendance System</h2>
                <span class="user-role">Student</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="#courses" class="nav-link" data-section="courses">Available Courses</a></li>
                <li class="nav-item"><a href="#mark" class="nav-link" data-section="mark">Mark Attendance</a></li>
                <li class="nav-item"><a href="#stats" class="nav-link" data-section="stats">Statistics</a></li>
                <li class="nav-item"><a href="#attendance" class="nav-link active" data-section="attendance">History</a></li>
                <li class="nav-item"><button id="report-issue-btn" class="btn-secondary">Report Issue</button></li>
                <li class="nav-item"><button onclick="window.location.href='logout.php'" class="logout-btn">Logout</button></li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <header class="dashboard-header">
            <h1 id="dashboard-title">My Attendance History</h1>
            <div class="user-info">
                <span><?php echo htmlspecialchars($student_name); ?></span>
            </div>
        </header>

        <!-- Courses Section -->
        <section id="courses-section" class="content-section">
            <h2>Available Courses</h2>
            <div id="courses-list"></div>
        </section>

        <!-- Mark Attendance Section -->
        <section id="mark-section" class="content-section">
            <h2>Mark Attendance</h2>
            <div style="max-width: 500px; margin: 20px auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <p style="margin-bottom: 20px; color: #666;">Enter the attendance code provided by your instructor to mark your attendance for today's session.</p>
                <form id="attendance-code-form">
                    <div class="form-group">
                        <label>Attendance Code</label>
                        <input type="text" id="attendance-code-input" placeholder="Enter 6-digit code" maxlength="6" style="text-transform: uppercase; font-size: 1.2em; text-align: center; letter-spacing: 3px;" required>
                    </div>
                    <button type="submit" class="btn-primary" style="width: 100%;">Submit Attendance</button>
                </form>
                <div id="attendance-result" style="margin-top: 20px;"></div>
            </div>
        </section>

        <!-- Statistics Section -->
        <section id="stats-section" class="content-section">
            <h2>Attendance Statistics</h2>
            <div id="stats-container">
                <p>Loading statistics...</p>
            </div>
        </section>

        <section id="attendance-section" class="content-section active">
            <div class="attendance-list">
                <?php if (empty($attendance_records)): ?>
                    <p>No attendance records found.</p>
                <?php else: ?>
                    <?php foreach ($attendance_records as $record): ?>
                        <div class="attendance-card status-<?php echo $record['status']; ?> session-type-<?php echo $record['session_type']; ?>">
                            <div class="card-header">
                                <h3><?php echo htmlspecialchars($record['course_code']); ?></h3>
                                <span class="date"><?php echo htmlspecialchars($record['session_date']); ?></span>
                            </div>
                            <p><strong><?php echo htmlspecialchars($record['course_name']); ?></strong></p>
                            <p>Type: <?php echo ucfirst($record['session_type']); ?></p>
                            <p>Status: <strong><?php echo ucfirst($record['status']); ?></strong></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Report Issue Modal -->
    <div id="issue-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('issue-modal').style.display='none'">&times;</span>
            <h3>Report Attendance Issue</h3>
            <form id="issue-form">
                <input type="hidden" name="action" value="report_issue">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" name="subject" placeholder="e.g., Incorrect marking for Lab 3" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Describe the issue..." required></textarea>
                </div>
                <button type="submit" class="btn-primary">Submit Report</button>
            </form>
        </div>
    </div>

    <script>
        // Modal Logic
        const modal = document.getElementById('issue-modal');
        document.getElementById('report-issue-btn').addEventListener('click', () => {
            modal.style.display = 'block';
        });
        
        window.onclick = function(event) {
            if (event.target == modal) modal.style.display = "none";
        }

        // Form Logic
        document.getElementById('issue-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('actions.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    modal.style.display = 'none';
                    this.reset();
                }
            });
        });
        
        // Navigation switching
        document.querySelectorAll('.nav-link[data-section]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active from all links and sections
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                document.querySelectorAll('.content-section').forEach(s => s.classList.remove('active'));
                
                // Add active to clicked link
                this.classList.add('active');
                
                // Show corresponding section
                const sectionId = this.dataset.section + '-section';
                document.getElementById(sectionId).classList.add('active');
                
                // Update page title
                const titles = {
                    'courses': 'Available Courses',
                    'mark': 'Mark Attendance',
                    'stats': 'Attendance Statistics',
                    'attendance': 'My Attendance History'
                };
                document.getElementById('dashboard-title').textContent = titles[this.dataset.section];
            });
        });
    </script>
    <script src="scripts/student_attendance.js"></script>
    <script src="scripts/enrollment.js"></script>
</body>
</html>
