// Dashboard Functionality with Role-based Access Control
class Dashboard {
    constructor() {
        this.currentUser = null;
        this.currentSection = 'courses';
        this.sampleData = this.getSampleData();
        this.init();
    }

    getSampleData() {
        return {
            courses: [
                {
                    id: 1,
                    code: "CS101",
                    name: "Introduction to Computer Science",
                    semester: "Fall 2024",
                    credits: 3,
                    enrolled_students: 45,
                    total_sessions: 15,
                    description: "Fundamental concepts of computer science and programming",
                    created_by: "faculty@ashesi.edu.gh"
                },
                {
                    id: 2,
                    code: "MATH201", 
                    name: "Calculus II",
                    semester: "Fall 2024",
                    credits: 4,
                    enrolled_students: 38,
                    total_sessions: 12,
                    description: "Advanced calculus topics including integration and series",
                    created_by: "faculty@ashesi.edu.gh"
                }
            ],
            sessions: [
                {
                    id: 1,
                    course_id: 1,
                    title: "Introduction to Programming",
                    date: "2024-01-15",
                    time: "09:00-10:30",
                    location: "Room 101",
                    attendance: 45,
                    created_by: "faculty@ashesi.edu.gh"
                },
                {
                    id: 2,
                    course_id: 1,
                    title: "Data Structures Lecture",
                    date: "2024-01-17", 
                    time: "09:00-10:30",
                    location: "Room 101",
                    attendance: 42,
                    created_by: "intern@ashesi.edu.gh"
                }
            ],
            observers: [
                {
                    id: 1,
                    name: "Dr. Kwame Mensah",
                    email: "k.mensah@ashesi.edu.gh",
                    course_code: "CS101",
                    role: "auditor",
                    added_by: "faculty@ashesi.edu.gh"
                }
            ]
        };
    }

    // Check if current user can manage courses (Faculty or Faculty Intern only)
    canManageCourses() {
        return this.currentUser && (this.currentUser.role === 'faculty' || this.currentUser.role === 'intern');
    }

    // Check if current user is student
    isStudent() {
        return this.currentUser && this.currentUser.role === 'student';
    }

    init() {
        this.loadUserData();
        this.setupNavigation();
        this.setupEventListeners();
        this.loadInitialData();
        this.applyRoleBasedRestrictions();
    }

    loadUserData() {
        const userData = localStorage.getItem('currentUser');
        if (userData) {
            this.currentUser = JSON.parse(userData);
            this.updateUserInterface();
        } else {
            window.location.href = 'index.html';
        }
    }

    updateUserInterface() {
        if (this.currentUser) {
            document.getElementById('user-name').textContent = this.currentUser.name;
            document.getElementById('user-email').textContent = this.currentUser.email;
            document.getElementById('user-role').textContent = this.currentUser.role;
            
            // Update dashboard title based on role
            if (this.isStudent()) {
                document.querySelector('.nav-logo h2').textContent = 'Student Portal';
            }
        }
    }

    // Apply role-based restrictions to UI
    applyRoleBasedRestrictions() {
        const canManage = this.canManageCourses();
        
        // Show/hide management buttons based on role
        const managementButtons = [
            'add-course-btn',
            'add-session-btn', 
            'generate-report-btn',
            'add-observer-btn'
        ];
        
        managementButtons.forEach(buttonId => {
            const button = document.getElementById(buttonId);
            if (button) {
                if (canManage) {
                    button.style.display = 'inline-block';
                } else {
                    button.style.display = 'none';
                }
            }
        });

        // Hide observer management section for students
        const observerForm = document.querySelector('.observer-form');
        if (observerForm && this.isStudent()) {
            observerForm.style.display = 'none';
        }

        // Update section headers based on role
        if (this.isStudent()) {
            const coursesHeader = document.querySelector('#courses-section .section-header h2');
            if (coursesHeader) coursesHeader.textContent = 'My Enrolled Courses';
            
            const reportsHeader = document.querySelector('#reports-section .section-header h2');
            if (reportsHeader) reportsHeader.textContent = 'My Attendance Reports';
        }
    }

    setupNavigation() {
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = link.getAttribute('data-section');
                this.switchSection(section);
            });
        });

        document.getElementById('logout-btn').addEventListener('click', () => {
            localStorage.removeItem('currentUser');
            window.location.href = 'index.html';
        });
    }

    switchSection(section) {
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-section="${section}"]`).classList.add('active');

        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(`${section}-section`).classList.add('active');

        // Update dashboard title based on role and section
        let title = section.charAt(0).toUpperCase() + section.slice(1);
        if (this.isStudent()) {
            if (section === 'courses') title = 'My Courses';
            if (section === 'reports') title = 'My Reports';
        }
        document.getElementById('dashboard-title').textContent = title;

        this.currentSection = section;
        this.loadSectionData(section);
    }

    setupEventListeners() {
        const courseModal = document.getElementById('course-modal');
        const addCourseBtn = document.getElementById('add-course-btn');
        const closeBtn = document.querySelector('.close');

        // Only setup management event listeners if user has permission
        if (this.canManageCourses()) {
            addCourseBtn.addEventListener('click', () => {
                courseModal.style.display = 'block';
            });

            document.getElementById('course-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.addNewCourse();
            });

            document.getElementById('add-observer-form').addEventListener('submit', (e) => {
                e.preventDefault();
                this.addObserver();
            });

            document.getElementById('apply-filters-btn').addEventListener('click', () => {
                this.generateReport();
            });
        }

        closeBtn.addEventListener('click', () => {
            courseModal.style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === courseModal) {
                courseModal.style.display = 'none';
            }
        });
    }

    loadInitialData() {
        this.loadCourses();
        this.loadObservers();
        this.populateCourseSelects();
    }

    loadCourses() {
        // For students, show only enrolled courses
        if (this.isStudent()) {
            const enrolledCourses = this.sampleData.courses.filter(course => 
                course.code === 'CS101' // Simulating student is enrolled in CS101
            );
            this.displayCourses(enrolledCourses);
        } else {
            this.displayCourses(this.sampleData.courses);
        }
    }

    displayCourses(courses) {
        const container = document.getElementById('courses-container');
        
        if (courses.length === 0) {
            if (this.isStudent()) {
                container.innerHTML = '<div class="loading">You are not enrolled in any courses yet.</div>';
            } else {
                container.innerHTML = '<div class="loading">No courses found. Add your first course!</div>';
            }
            return;
        }

        container.innerHTML = courses.map(course => `
            <div class="course-card">
                <h3>${course.name}</h3>
                <div class="course-code">${course.code}</div>
                <p>${course.description}</p>
                <div class="course-meta">
                    <span>Semester: ${course.semester}</span>
                    <span>Credits: ${course.credits}</span>
                </div>
                <div class="course-meta">
                    <span>Students: ${course.enrolled_students}</span>
                    <span>Sessions: ${course.total_sessions}</span>
                </div>
                ${this.isStudent() ? `<div class="attendance-status">Your Attendance: 85%</div>` : ''}
            </div>
        `).join('');
    }

    addNewCourse() {
        // Check permission
        if (!this.canManageCourses()) {
            alert('Access denied. Only faculty and faculty interns can add courses.');
            return;
        }

        const code = document.getElementById('course-code').value;
        const name = document.getElementById('course-name').value;
        const semester = document.getElementById('course-semester').value;
        const credits = parseInt(document.getElementById('course-credits').value);

        const newCourse = {
            id: this.sampleData.courses.length + 1,
            code,
            name,
            semester,
            credits,
            enrolled_students: 0,
            total_sessions: 0,
            description: "New course description",
            created_by: this.currentUser.email
        };

        this.sampleData.courses.push(newCourse);
        
        document.getElementById('course-modal').style.display = 'none';
        document.getElementById('course-form').reset();
        
        this.loadCourses();
        this.populateCourseSelects();
        
        alert('Course added successfully!');
    }

    loadObservers() {
        // For students, don't show observers management
        if (this.isStudent()) {
            document.getElementById('observers-list').innerHTML = 
                '<div class="loading">Observer management is not available for students.</div>';
            return;
        }
        this.displayObservers(this.sampleData.observers);
    }

    displayObservers(observers) {
        const container = document.getElementById('observers-list');
        
        if (observers.length === 0) {
            container.innerHTML = '<div class="loading">No observers found. Add your first observer!</div>';
            return;
        }

        container.innerHTML = observers.map(observer => `
            <div class="observer-item">
                <div>
                    <strong>${observer.name}</strong>
                    <div>${observer.email}</div>
                    <small>Course: ${observer.course_code} • Role: ${observer.role}</small>
                </div>
                ${this.canManageCourses() ? 
                    `<button class="btn-secondary" onclick="dashboard.removeObserver(${observer.id})">Remove</button>` : 
                    `<span class="view-only">View Only</span>`
                }
            </div>
        `).join('');
    }

    addObserver() {
        // Check permission
        if (!this.canManageCourses()) {
            alert('Access denied. Only faculty and faculty interns can add observers.');
            return;
        }

        const course_code = document.getElementById('observer-course-select').value;
        const email = document.getElementById('observer-email').value;
        const role = document.getElementById('observer-role').value;

        const newObserver = {
            id: this.sampleData.observers.length + 1,
            name: email.split('@')[0],
            email,
            course_code,
            role,
            added_by: this.currentUser.email
        };

        this.sampleData.observers.push(newObserver);
        document.getElementById('add-observer-form').reset();
        this.loadObservers();
        
        alert('Observer added successfully!');
    }

    removeObserver(observerId) {
        // Check permission
        if (!this.canManageCourses()) {
            alert('Access denied. Only faculty and faculty interns can remove observers.');
            return;
        }

        this.sampleData.observers = this.sampleData.observers.filter(obs => obs.id !== observerId);
        this.loadObservers();
        alert('Observer removed successfully!');
    }

    populateCourseSelects() {
        const courses = this.sampleData.courses.map(course => course.code);
        const selects = document.querySelectorAll('select[id$="course-select"]');
        
        selects.forEach(select => {
            while (select.options.length > 1) {
                select.remove(1);
            }
            
            courses.forEach(course => {
                const option = document.createElement('option');
                option.value = course;
                option.textContent = course;
                select.appendChild(option);
            });
        });
    }

    generateReport() {
        // Check permission for detailed reports
        if (this.isStudent()) {
            this.displayStudentReport();
            return;
        }

        const course = document.getElementById('report-course-select').value;
        const type = document.getElementById('report-type-select').value;
        this.displayReport(course, type);
    }

    displayStudentReport() {
        const container = document.getElementById('report-content');
        container.innerHTML = `
            <div class="report-summary">
                <h3>My Attendance Summary</h3>
                <div class="report-stats">
                    <div>Enrolled Courses: 1</div>
                    <div>Overall Attendance: 85%</div>
                    <div>Total Sessions Attended: 12/15</div>
                    <div>Current Streak: 5 sessions</div>
                </div>
                <div class="report-chart">
                    <p><strong>CS101 Attendance:</strong></p>
                    <p>• Week 1-5: 100%</p>
                    <p>• Week 6-10: 80%</p>
                    <p>• Week 11-15: 75%</p>
                </div>
                <p><em>Contact your faculty for detailed reports.</em></p>
            </div>
        `;
    }

    displayReport(course, type) {
        const container = document.getElementById('report-content');
        const courseData = this.sampleData.courses.find(c => c.code === course);
        
        container.innerHTML = `
            <div class="report-summary">
                <h3>${type.charAt(0).toUpperCase() + type.slice(1)} Report for ${course || 'All Courses'}</h3>
                <div class="report-stats">
                    <div>Total Sessions: ${courseData ? courseData.total_sessions : 37}</div>
                    <div>Average Attendance: ${courseData ? '85%' : '82%'}</div>
                    <div>Total Students: ${courseData ? courseData.enrolled_students : 135}</div>
                    <div>Most Attended Session: Lecture 5 (95%)</div>
                    <div>Least Attended Session: Tutorial 3 (72%)</div>
                </div>
                <div class="report-chart">
                    <p><strong>Attendance Trend:</strong> Consistent improvement over the semester</p>
                    <p><strong>Top Performers:</strong> 15 students with 100% attendance</p>
                    <p><strong>Areas for Improvement:</strong> Tutorial sessions show lower attendance</p>
                </div>
            </div>
        `;
    }

    loadSectionData(section) {
        switch(section) {
            case 'sessions':
                this.loadSessions();
                break;
            case 'reports':
                this.populateCourseSelects();
                if (this.isStudent()) {
                    this.displayStudentReport();
                }
                break;
            case 'observers':
                this.loadObservers();
                break;
        }
    }

    loadSessions() {
        // For students, show only sessions for enrolled courses
        if (this.isStudent()) {
            const studentSessions = this.sampleData.sessions.filter(session => 
                session.course_id === 1 // Simulating student is enrolled in course ID 1 (CS101)
            );
            this.displaySessions(studentSessions);
        } else {
            this.displaySessions(this.sampleData.sessions);
        }
    }

    displaySessions(sessions) {
        const container = document.getElementById('sessions-container');
        
        if (sessions.length === 0) {
            if (this.isStudent()) {
                container.innerHTML = '<div class="loading">No sessions scheduled for your courses.</div>';
            } else {
                container.innerHTML = '<div class="loading">No sessions scheduled. Add your first session!</div>';
            }
            return;
        }

        container.innerHTML = sessions.map(session => {
            const course = this.sampleData.courses.find(c => c.id === session.course_id);
            return `
                <div class="session-item">
                    <div>
                        <strong>${session.title}</strong>
                        <div>Course: ${course ? course.code : 'Unknown'} • Date: ${session.date}</div>
                        <small>Time: ${session.time} • Location: ${session.location}</small>
                    </div>
                    <div>
                        <span class="attendance-badge">${session.attendance} attended</span>
                        ${this.isStudent() ? `<span class="my-attendance">You attended: ✓</span>` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }
}

let dashboard;
document.addEventListener('DOMContentLoaded', () => {
    dashboard = new Dashboard();
});