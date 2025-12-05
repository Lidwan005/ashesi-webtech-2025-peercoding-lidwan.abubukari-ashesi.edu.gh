<?php
require 'auth_session.php';
require 'db_connect.php';

// Allow both faculty and intern roles
requireLogin();
if ($_SESSION['role'] === 'student') {
    header("Location: student_dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
$user_email = $_SESSION['email'];
$user_role = $_SESSION['role'];

// Fetch Courses
$courses_stmt = $conn->prepare("SELECT * FROM courses WHERE faculty_id = ?");
$courses_stmt->bind_param("i", $user_id);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();
$courses = [];
while ($row = $courses_result->fetch_assoc()) {
    $courses[] = $row;
}

// Fetch Sessions for the faculty's courses
$sessions_query = "SELECT s.*, c.course_code, c.course_name 
                   FROM sessions s
                   JOIN courses c ON s.course_id = c.course_id
                   WHERE c.faculty_id = ?
                   ORDER BY s.session_date DESC, s.start_time DESC";
$sessions_stmt = $conn->prepare($sessions_query);
$sessions_stmt->bind_param("i", $user_id);
$sessions_stmt->execute();
$sessions_result = $sessions_stmt->get_result();
$sessions = [];
while ($row = $sessions_result->fetch_assoc()) {
    $sessions[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard - Attendance System</title>
    <link rel="stylesheet" href="styles/dashboard.css">
    <style>
        /* Add styles for the new modal and form elements */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fff; margin: 15% auto; padding: 20px; border-radius: 8px; width: 50%; max-width: 500px; position: relative; }
        .close { position: absolute; right: 20px; top: 10px; font-size: 28px; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        textarea { resize: vertical; min-height: 80px; }
        
        /* Session cards */
        .session-card { background: #fff; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #3498db; }
        .session-card h4 { margin: 0 0 10px 0; color: #2c3e50; }
        .session-card p { margin: 5px 0; color: #555; }
        
        /* Attendance modal */
        #attendance-modal .modal-content { width: 80%; max-width: 700px; max-height: 80vh; overflow-y: auto; }
        .students-list { margin: 20px 0; }
        .student-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .student-name { font-weight: bold; flex: 1; }
        .student-email { color: #666; flex: 1; font-size: 0.9em; }
        .attendance-select { width: 150px; padding: 5px; }
        
        /* Request cards */
        .request-card { background: #fff; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #f39c12; }
        .request-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .request-info { flex: 1; }
        .request-actions { display: flex; gap: 10px; }
        .btn-approve { background: #2ecc71; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-approve:hover { background: #27ae60; }
        .btn-reject { background: #e74c3c; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-reject:hover { background: #c0392b; }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>Attendance System</h2>
                <span id="user-role" class="user-role"><?php echo ucfirst($user_role); ?></span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="#courses" class="nav-link active" data-section="courses">Course List</a></li>
                <li class="nav-item"><a href="#requests" class="nav-link" data-section="requests">Enrollment Requests</a></li>
                <li class="nav-item"><a href="#sessions" class="nav-link" data-section="sessions">Sessions</a></li>
                <li class="nav-item"><button id="logout-btn" class="logout-btn" onclick="window.location.href='logout.php'">Logout</button></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <header class="dashboard-header">
            <h1 id="dashboard-title">Course List</h1>
            <div class="user-info">
                <span id="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                <span id="user-email" class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
            </div>
        </header>

        <!-- Courses Section -->
        <section id="courses-section" class="content-section active">
            <div class="section-header">
                <h2>My Courses</h2>
                <button class="btn-primary" id="add-course-btn">Add New Course</button>
            </div>
            <div class="courses-grid" id="courses-container">
                <?php if (empty($courses)): ?>
                    <p>No courses found. Add one to get started.</p>
                <?php else: ?>
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card">
                            <h3><?php echo htmlspecialchars($course['course_code']); ?></h3>
                            <p><?php echo htmlspecialchars($course['course_name']); ?></p>
                            <p><?php echo htmlspecialchars($course['semester']); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Enrollment Requests Section -->
        <section id="requests-section" class="content-section">
            <div class="section-header">
                <h2>Enrollment Requests</h2>
            </div>
            <div class="requests-container" id="requests-container">
                <p>Loading requests...</p>
            </div>
        </section>

        <!-- Sessions Section -->
        <section id="sessions-section" class="content-section">
            <div class="section-header">
                <h2>Course Sessions</h2>
                <button class="btn-primary" id="add-session-btn">Schedule Session</button>
            </div>
            <div class="sessions-container" id="sessions-container">
                <?php if (empty($sessions)): ?>
                    <p>No sessions scheduled yet. Add a session to get started.</p>
                <?php else: ?>
                    <?php foreach ($sessions as $session): ?>
                        <div class="session-card">
                            <h4><?php echo htmlspecialchars($session['course_code']); ?> - <?php echo htmlspecialchars($session['course_name']); ?></h4>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($session['session_date']); ?></p>
                            <p><strong>Time:</strong> <?php echo htmlspecialchars($session['start_time']); ?> - <?php echo htmlspecialchars($session['end_time']); ?></p>
                            <p><strong>Type:</strong> <?php echo ucfirst($session['session_type']); ?></p>
                            <?php if (!empty($session['notes'])): ?>
                                <p><strong>Notes:</strong> <?php echo htmlspecialchars($session['notes']); ?></p>
                            <?php endif; ?>
                            <button class="btn-primary" onclick="takeAttendance(<?php echo $session['session_id']; ?>)">Take Attendance</button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Add Course Modal -->
    <div id="course-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('course-modal').style.display='none'">&times;</span>
            <h3>Add New Course</h3>
            <form id="course-form">
                <input type="hidden" name="action" value="add_course">
                <div class="form-group">
                    <label>Course Code</label>
                    <input type="text" name="code" placeholder="e.g., CS101" required>
                </div>
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" name="name" placeholder="Course Name" required>
                </div>
                <div class="form-group">
                    <label>Semester</label>
                    <select name="semester" required>
                        <option value="Fall 2024">Fall 2024</option>
                        <option value="Spring 2024">Spring 2024</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Credits</label>
                    <input type="number" name="credits" min="1" max="6" required>
                </div>
                <button type="submit" class="btn-primary">Add Course</button>
            </form>
        </div>
    </div>

    <!-- Add Session Modal -->
    <div id="session-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('session-modal').style.display='none'">&times;</span>
            <h3>Schedule Session</h3>
            <form id="session-form">
                <input type="hidden" name="action" value="add_session">
                <div class="form-group">
                    <label>Course</label>
                    <select name="course_id" required>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo $course['course_id']; ?>">
                                <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="date" required>
                </div>
                <div class="form-group">
                    <label>Start Time</label>
                    <input type="time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label>End Time</label>
                    <input type="time" name="end_time" required>
                </div>
                <div class="form-group">
                    <label>Session Type</label>
                    <select name="type" required>
                        <option value="lecture">Lecture</option>
                        <option value="lab">Laboratory</option>
                        <option value="practical">Practical</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Notes (Optional)</label>
                    <textarea name="notes" placeholder="e.g., Bring safety goggles"></textarea>
                </div>
                <button type="submit" class="btn-primary">Schedule Session</button>
            </form>
        </div>
    </div>

    <script src="scripts/faculty_dashboard.js"></script>
    <script src="scripts/attendance.js"></script>
    <script src="scripts/enrollment_requests.js"></script>
</body>
</html>
