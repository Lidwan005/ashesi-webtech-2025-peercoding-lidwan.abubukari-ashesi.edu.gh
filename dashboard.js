// Load default section
window.onload = function () {
  loadOverview();
};

// ================= OVERVIEW =================
function loadOverview() {
  const content = document.getElementById('contentArea');
  content.innerHTML = `
    <h2>Dashboard Overview</h2>
    <div class="summary-cards">
      <div class="card"><h3>Total Courses</h3><p>4</p></div>
      <div class="card"><h3>Attendance Rate</h3><p>92%</p></div>
      <div class="card"><h3>Upcoming Sessions</h3><p>3</p></div>
    </div>
    <p style="margin-top:1.5rem;">Welcome to your Attendance Management Dashboard. Use the buttons above to explore your courses, sessions, and reports.</p>
  `;
}

// ================= COURSES =================
function loadCourses() {
  const courses = [
    { code: "CS101", name: "Introduction to Programming", semester: "Fall 2025" },
    { code: "CS205", name: "Web Technologies", semester: "Fall 2025" },
    { code: "CS307", name: "Data Structures & Algorithms", semester: "Fall 2025" },
    { code: "CS310", name: "Database Systems", semester: "Fall 2025" }
  ];

  const content = document.getElementById('contentArea');
  content.innerHTML = `
    <h2>My Courses</h2>
    <div class="summary-cards">
      <div class="card"><h3>Total Courses</h3><p>${courses.length}</p></div>
      <div class="card"><h3>Active Sessions</h3><p>5</p></div>
      <div class="card"><h3>Attendance Rate</h3><p>92%</p></div>
    </div>
    <h3 style="margin-top:1.5rem;">Course List</h3>
    <ul>
      ${courses.map(c => `<li><strong>${c.code}</strong> — ${c.name} (${c.semester})</li>`).join('')}
    </ul>
  `;
}

// ================= SESSIONS =================
function loadSessions() {
  const sessions = [
    { course: "CS101", date: "2025-10-10", topic: "Introduction to Python", status: "Completed" },
    { course: "CS205", date: "2025-10-12", topic: "HTML5 & Accessibility", status: "Ongoing" },
    { course: "CS310", date: "2025-10-14", topic: "SQL Joins", status: "Upcoming" },
    { course: "CS307", date: "2025-10-18", topic: "Algorithm Efficiency", status: "Upcoming" }
  ];

  const statusColors = {
    "Completed": "#4CAF50",
    "Ongoing": "#2196F3",
    "Upcoming": "#FF9800"
  };

  const content = document.getElementById('contentArea');
  content.innerHTML = `
    <h2>Class Sessions</h2>
    <p>Here is a list of your recent and upcoming sessions.</p>
    <table class="attendance-table">
      <thead>
        <tr><th>Course</th><th>Date</th><th>Topic</th><th>Status</th></tr>
      </thead>
      <tbody>
        ${sessions.map(s => `
          <tr>
            <td>${s.course}</td>
            <td>${s.date}</td>
            <td>${s.topic}</td>
            <td style="color:${statusColors[s.status]}; font-weight:bold;">${s.status}</td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;
}

// ================= REPORT =================
function loadReport() {
  const reports = [
    { course: "CS101", total: 10, attended: 9 },
    { course: "CS205", total: 8, attended: 7 },
    { course: "CS307", total: 9, attended: 9 },
    { course: "CS310", total: 10, attended: 9 }
  ];

  const overallRate = Math.round(
    (reports.reduce((sum, r) => sum + (r.attended / r.total), 0) / reports.length) * 100
  );

  const content = document.getElementById('contentArea');
  content.innerHTML = `
    <h2>Attendance Report</h2>
    <div class="summary-cards">
      <div class="card"><h3>Courses Monitored</h3><p>${reports.length}</p></div>
      <div class="card"><h3>Overall Attendance Rate</h3><p>${overallRate}%</p></div>
      <div class="card"><h3>Sessions Missed</h3><p>${reports.reduce((sum, r) => sum + (r.total - r.attended), 0)}</p></div>
    </div>

    <h3 style="margin-top:1.5rem;">Detailed Attendance by Course</h3>
    <table class="attendance-table">
      <thead>
        <tr><th>Course</th><th>Total Classes</th><th>Attended</th><th>Attendance %</th></tr>
      </thead>
      <tbody>
        ${reports.map(r => `
          <tr>
            <td>${r.course}</td>
            <td>${r.total}</td>
            <td>${r.attended}</td>
            <td>${((r.attended / r.total) * 100).toFixed(1)}%</td>
          </tr>
        `).join('')}
      </tbody>
    </table>
  `;
}

// ================= LOGOUT =================
function logout() {
  alert("You have been logged out.");
  window.location.href = "index.html";
}

