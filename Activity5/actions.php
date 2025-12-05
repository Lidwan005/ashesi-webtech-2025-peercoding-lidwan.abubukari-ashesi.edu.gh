<?php
header('Content-Type: application/json');
require 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // --- REGISTRATION ---
    if ($action == 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = trim($_POST['role'] ?? '');

        // Basic validation
        if (empty($name) || empty($email) || empty($password) || empty($role)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
            exit();
        }

        // Check if email exists
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        if (!$check_stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
            exit();
        }
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
            exit();
        }

        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
            exit();
        }
        $stmt->bind_param("ssss", $name, $email, $password_hash, $role);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Registration successful! Please login.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $conn->error]);
        }
        exit();
    }

    // --- LOGIN ---
    elseif ($action == 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, role FROM users WHERE email = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['full_name'];
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $user['role'];

                $redirect = ($user['role'] == 'student') ? 'student_dashboard.php' : 'faculty_dashboard.php';
                echo json_encode(['status' => 'success', 'redirect' => $redirect]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
        exit();
    }

    // --- ADD COURSE ---
    elseif ($action == 'add_course') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $semester = trim($_POST['semester'] ?? '');
        $credits = (int)($_POST['credits'] ?? 0);
        $faculty_id = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO courses (course_code, course_name, semester, credits, faculty_id) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("sssii", $code, $name, $semester, $credits, $faculty_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Course added successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add course']);
        }
        exit();
    }

    // --- ADD SESSION ---
    elseif ($action == 'add_session') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $course_id = (int)($_POST['course_id'] ?? 0);
        $date = trim($_POST['date'] ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $stmt = $conn->prepare("INSERT INTO sessions (course_id, session_date, start_time, end_time, session_type, notes) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("isssss", $course_id, $date, $start_time, $end_time, $type, $notes);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Session scheduled successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to schedule session']);
        }
        exit();
    }

    // --- REPORT ISSUE ---
    elseif ($action == 'report_issue') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $student_id = $_SESSION['user_id'];
        $subject = trim($_POST['subject'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $session_id = !empty($_POST['session_id']) ? (int)$_POST['session_id'] : NULL;

        $stmt = $conn->prepare("INSERT INTO attendance_issues (student_id, session_id, subject, description) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("iiss", $student_id, $session_id, $subject, $description);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Issue reported successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to report issue']);
        }
        exit();
    }

    // --- ENROLL STUDENT (CREATE REQUEST) ---
    elseif ($action == 'enroll') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Only students can request enrollment']);
            exit();
        }

        $student_id = $_SESSION['user_id'];
        $course_id = (int)($_POST['course_id'] ?? 0);

        // Check if already has a request/enrollment
        $check_stmt = $conn->prepare("SELECT enrollment_id, status FROM enrollments WHERE student_id = ? AND course_id = ?");
        if (!$check_stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $check_stmt->bind_param("ii", $student_id, $course_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $existing = $check_result->fetch_assoc();
            if ($existing['status'] == 'pending') {
                echo json_encode(['status' => 'error', 'message' => 'You already have a pending request for this course']);
            } elseif ($existing['status'] == 'approved') {
                echo json_encode(['status' => 'error', 'message' => 'You are already enrolled in this course']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'You have already requested this course']);
            }
            exit();
        }

        // Create pending enrollment request
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id, status) VALUES (?, ?, 'pending')");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("ii", $student_id, $course_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Enrollment request submitted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit request']);
        }
        exit();
    }

    // --- GET AVAILABLE COURSES ---
    elseif ($action == 'get_available_courses') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $student_id = $_SESSION['user_id'];
        $query = "SELECT c.*, u.full_name as faculty_name,
                  (SELECT status FROM enrollments WHERE course_id = c.course_id AND student_id = ?) as enrollment_status
                  FROM courses c
                  LEFT JOIN users u ON c.faculty_id = u.user_id
                  ORDER BY c.created_at DESC";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'courses' => $courses]);
        exit();
    }

    // --- GET SESSION STUDENTS ---
    elseif ($action == 'get_session_students') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $session_id = (int)($_POST['session_id'] ?? 0);
        
        $query = "SELECT u.user_id, u.full_name, u.email,
                  a.status as attendance_status
                  FROM sessions s
                  JOIN enrollments e ON s.course_id = e.course_id
                  JOIN users u ON e.student_id = u.user_id
                  LEFT JOIN attendance a ON a.session_id = s.session_id AND a.student_id = u.user_id
                  WHERE s.session_id = ?
                  ORDER BY u.full_name";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("i", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'students' => $students]);
        exit();
    }

    // --- RECORD ATTENDANCE ---
    elseif ($action == 'record_attendance') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $session_id = (int)($_POST['session_id'] ?? 0);
        $student_id = (int)($_POST['student_id'] ?? 0);
        $status = trim($_POST['status'] ?? '');

        // Check if attendance already recorded
        $check_stmt = $conn->prepare("SELECT attendance_id FROM attendance WHERE session_id = ? AND student_id = ?");
        if (!$check_stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $check_stmt->bind_param("ii", $session_id, $student_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE attendance SET status = ? WHERE session_id = ? AND student_id = ?");
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Database error']);
                exit();
            }
            $stmt->bind_param("sii", $status, $session_id, $student_id);
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO attendance (session_id, student_id, status) VALUES (?, ?, ?)");
            if (!$stmt) {
                echo json_encode(['status' => 'error', 'message' => 'Database error']);
                exit();
            }
            $stmt->bind_param("iis", $session_id, $student_id, $status);
        }

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Attendance recorded']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to record attendance']);
        }
        exit();
    }

    // --- GET PENDING ENROLLMENT REQUESTS ---
    elseif ($action == 'get_pending_requests') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $faculty_id = $_SESSION['user_id'];
        $query = "SELECT e.enrollment_id, e.enrolled_at, e.status,
                  u.user_id as student_id, u.full_name as student_name, u.email as student_email,
                  c.course_id, c.course_code, c.course_name
                  FROM enrollments e
                  JOIN users u ON e.student_id = u.user_id
                  JOIN courses c ON e.course_id = c.course_id
                  WHERE c.faculty_id = ? AND e.status = 'pending'
                  ORDER BY e.enrolled_at DESC";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $requests = [];
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        
        echo json_encode(['status' => 'success', 'requests' => $requests]);
        exit();
    }

    // --- APPROVE ENROLLMENT REQUEST ---
    elseif ($action == 'approve_request') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $faculty_id = $_SESSION['user_id'];
        $enrollment_id = (int)($_POST['enrollment_id'] ?? 0);

        // Verify this request belongs to faculty's course
        $check_query = "SELECT e.enrollment_id FROM enrollments e
                        JOIN courses c ON e.course_id = c.course_id
                        WHERE e.enrollment_id = ? AND c.faculty_id = ? AND e.status = 'pending'";
        $check_stmt = $conn->prepare($check_query);
        if (!$check_stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $check_stmt->bind_param("ii", $enrollment_id, $faculty_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request or already processed']);
            exit();
        }

        // Update status to approved
        $stmt = $conn->prepare("UPDATE enrollments SET status = 'approved' WHERE enrollment_id = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("i", $enrollment_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Request approved successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to approve request']);
        }
        exit();
    }

    // --- REJECT ENROLLMENT REQUEST ---
    elseif ($action == 'reject_request') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $faculty_id = $_SESSION['user_id'];
        $enrollment_id = (int)($_POST['enrollment_id'] ?? 0);

        // Verify this request belongs to faculty's course
        $check_query = "SELECT e.enrollment_id FROM enrollments e
                        JOIN courses c ON e.course_id = c.course_id
                        WHERE e.enrollment_id = ? AND c.faculty_id = ? AND e.status = 'pending'";
        $check_stmt = $conn->prepare($check_query);
        if (!$check_stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $check_stmt->bind_param("ii", $enrollment_id, $faculty_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid request or already processed']);
            exit();
        }

        // Update status to rejected
        $stmt = $conn->prepare("UPDATE enrollments SET status = 'rejected' WHERE enrollment_id = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("i", $enrollment_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Request rejected']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to reject request']);
        }
        exit();
    }

    // --- GENERATE ATTENDANCE CODE ---
    elseif ($action == 'generate_attendance_code') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $session_id = (int)($_POST['session_id'] ?? 0);
        $faculty_id = $_SESSION['user_id'];

        // Verify this session belongs to faculty's course
        $check_query = "SELECT s.session_id, s.session_date, s.start_time, s.end_time 
                        FROM sessions s
                        JOIN courses c ON s.course_id = c.course_id
                        WHERE s.session_id = ? AND c.faculty_id = ?";
        $check_stmt = $conn->prepare($check_query);
        if (!$check_stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $check_stmt->bind_param("ii", $session_id, $faculty_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Session not found or unauthorized']);
            exit();
        }

        $session = $result->fetch_assoc();

        // Generate unique 6-character code
        $code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

        // Set expiration to end of session + 30 minutes buffer
        $session_datetime = $session['session_date'] . ' ' . $session['end_time'];
        $expires_at = date('Y-m-d H:i:s', strtotime($session_datetime . ' +30 minutes'));

        // Update session with code
        $update_stmt = $conn->prepare("UPDATE sessions SET attendance_code = ?, code_expires_at = ? WHERE session_id = ?");
        if (!$update_stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $update_stmt->bind_param("ssi", $code, $expires_at, $session_id);

        if ($update_stmt->execute()) {
            // Also insert into attendance_codes table for history
            $insert_stmt = $conn->prepare("INSERT INTO attendance_codes (session_id, code, expires_at) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("iss", $session_id, $code, $expires_at);
            $insert_stmt->execute();

            echo json_encode([
                'status' => 'success', 
                'message' => 'Attendance code generated',
                'code' => $code,
                'expires_at' => $expires_at
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to generate code']);
        }
        exit();
    }

    // --- MARK ATTENDANCE WITH CODE ---
    elseif ($action == 'mark_attendance_with_code') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Only students can mark attendance']);
            exit();
        }

        $student_id = $_SESSION['user_id'];
        $code = strtoupper(trim($_POST['code'] ?? ''));

        if (empty($code)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter an attendance code']);
            exit();
        }

        // Find session with this code
        $query = "SELECT s.session_id, s.course_id, s.code_expires_at, c.course_name, c.course_code
                  FROM sessions s
                  JOIN courses c ON s.course_id = c.course_id
                  WHERE s.attendance_code = ? AND s.code_expires_at > NOW()";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid or expired attendance code']);
            exit();
        }

        $session = $result->fetch_assoc();
        $session_id = $session['session_id'];
        $course_id = $session['course_id'];

        // Check if student is enrolled in this course (and approved)
        $enroll_check = $conn->prepare("SELECT enrollment_id FROM enrollments WHERE student_id = ? AND course_id = ? AND status = 'approved'");
        $enroll_check->bind_param("ii", $student_id, $course_id);
        $enroll_check->execute();
        
        if ($enroll_check->get_result()->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'You are not enrolled in this course']);
            exit();
        }

        // Check if already marked attendance
        $att_check = $conn->prepare("SELECT attendance_id FROM attendance WHERE session_id = ? AND student_id = ?");
        $att_check->bind_param("ii", $session_id, $student_id);
        $att_check->execute();
        
        if ($att_check->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'You have already marked attendance for this session']);
            exit();
        }

        // Mark attendance as present
        $insert_stmt = $conn->prepare("INSERT INTO attendance (session_id, student_id, status) VALUES (?, ?, 'present')");
        $insert_stmt->bind_param("ii", $session_id, $student_id);

        if ($insert_stmt->execute()) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Attendance marked successfully!',
                'course' => $session['course_code'] . ' - ' . $session['course_name']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to mark attendance']);
        }
        exit();
    }

    // --- GET STUDENT ATTENDANCE STATS ---
    elseif ($action == 'get_student_attendance_stats') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $student_id = $_SESSION['user_id'];

        // Get attendance statistics per course
        $query = "SELECT 
                    c.course_id,
                    c.course_code,
                    c.course_name,
                    COUNT(DISTINCT s.session_id) as total_sessions,
                    COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.session_id END) as present_count,
                    COUNT(DISTINCT CASE WHEN a.status = 'late' THEN a.session_id END) as late_count,
                    COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN a.session_id END) as absent_count,
                    COUNT(DISTINCT CASE WHEN a.status = 'excused' THEN a.session_id END) as excused_count
                  FROM enrollments e
                  JOIN courses c ON e.course_id = c.course_id
                  LEFT JOIN sessions s ON s.course_id = c.course_id
                  LEFT JOIN attendance a ON a.session_id = s.session_id AND a.student_id = e.student_id
                  WHERE e.student_id = ? AND e.status = 'approved'
                  GROUP BY c.course_id, c.course_code, c.course_name
                  ORDER BY c.course_code";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $total = (int)$row['total_sessions'];
            $present = (int)$row['present_count'];
            $late = (int)$row['late_count'];
            
            // Calculate attendance percentage (present + late)
            $attended = $present + $late;
            $percentage = $total > 0 ? round(($attended / $total) * 100, 1) : 0;
            
            $row['attended_sessions'] = $attended;
            $row['attendance_percentage'] = $percentage;
            $stats[] = $row;
        }

        echo json_encode(['status' => 'success', 'stats' => $stats]);
        exit();
    }

    // --- GET COURSE ATTENDANCE REPORT ---
    elseif ($action == 'get_course_attendance_report') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $student_id = $_SESSION['user_id'];
        $course_id = (int)($_POST['course_id'] ?? 0);

        // Verify enrollment
        $enroll_check = $conn->prepare("SELECT enrollment_id FROM enrollments WHERE student_id = ? AND course_id = ? AND status = 'approved'");
        $enroll_check->bind_param("ii", $student_id, $course_id);
        $enroll_check->execute();
        
        if ($enroll_check->get_result()->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Not enrolled in this course']);
            exit();
        }

        // Get detailed attendance records
        $query = "SELECT 
                    s.session_id,
                    s.session_date,
                    s.start_time,
                    s.end_time,
                    s.session_type,
                    s.notes,
                    COALESCE(a.status, 'not_marked') as attendance_status,
                    a.recorded_at
                  FROM sessions s
                  LEFT JOIN attendance a ON a.session_id = s.session_id AND a.student_id = ?
                  WHERE s.course_id = ?
                  ORDER BY s.session_date DESC, s.start_time DESC";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("ii", $student_id, $course_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }

        echo json_encode(['status' => 'success', 'records' => $records]);
        exit();
    }

    // --- UPDATE SESSION ---
    elseif ($action == 'update_session') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $faculty_id = $_SESSION['user_id'];
        $session_id = (int)($_POST['session_id'] ?? 0);
        $date = trim($_POST['date'] ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        // Verify this session belongs to faculty's course
        $check_query = "SELECT s.session_id FROM sessions s
                        JOIN courses c ON s.course_id = c.course_id
                        WHERE s.session_id = ? AND c.faculty_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $session_id, $faculty_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Session not found or unauthorized']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE sessions SET session_date = ?, start_time = ?, end_time = ?, session_type = ?, notes = ? WHERE session_id = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("sssssi", $date, $start_time, $end_time, $type, $notes, $session_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Session updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update session']);
        }
        exit();
    }

    // --- DELETE SESSION ---
    elseif ($action == 'delete_session') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $faculty_id = $_SESSION['user_id'];
        $session_id = (int)($_POST['session_id'] ?? 0);

        // Verify this session belongs to faculty's course
        $check_query = "SELECT s.session_id FROM sessions s
                        JOIN courses c ON s.course_id = c.course_id
                        WHERE s.session_id = ? AND c.faculty_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $session_id, $faculty_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Session not found or unauthorized']);
            exit();
        }

        // Delete attendance records first (foreign key constraint)
        $del_att = $conn->prepare("DELETE FROM attendance WHERE session_id = ?");
        $del_att->bind_param("i", $session_id);
        $del_att->execute();

        // Delete attendance codes
        $del_codes = $conn->prepare("DELETE FROM attendance_codes WHERE session_id = ?");
        $del_codes->bind_param("i", $session_id);
        $del_codes->execute();

        // Delete session
        $stmt = $conn->prepare("DELETE FROM sessions WHERE session_id = ?");
        $stmt->bind_param("i", $session_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Session deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete session']);
        }
        exit();
    }

    // --- GET STUDENT ISSUES ---
    elseif ($action == 'get_student_issues') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $faculty_id = $_SESSION['user_id'];

        // Get all issues from students enrolled in faculty's courses
        $query = "SELECT DISTINCT
                    ai.issue_id,
                    ai.subject,
                    ai.description,
                    ai.status,
                    ai.created_at,
                    u.full_name as student_name,
                    u.email as student_email,
                    s.session_date,
                    s.start_time,
                    c.course_code,
                    c.course_name
                  FROM attendance_issues ai
                  JOIN users u ON ai.student_id = u.user_id
                  LEFT JOIN sessions s ON ai.session_id = s.session_id
                  LEFT JOIN courses c ON s.course_id = c.course_id
                  WHERE c.faculty_id = ? OR ai.session_id IS NULL
                  ORDER BY ai.created_at DESC";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $issues = [];
        while ($row = $result->fetch_assoc()) {
            $issues[] = $row;
        }

        echo json_encode(['status' => 'success', 'issues' => $issues]);
        exit();
    }

    // --- RESOLVE ISSUE ---
    elseif ($action == 'resolve_issue') {
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'student') {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit();
        }

        $issue_id = (int)($_POST['issue_id'] ?? 0);
        $new_status = trim($_POST['new_status'] ?? 'resolved');

        $stmt = $conn->prepare("UPDATE attendance_issues SET status = ? WHERE issue_id = ?");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit();
        }
        $stmt->bind_param("si", $new_status, $issue_id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Issue status updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update issue']);
        }
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
