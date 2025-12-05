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
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
