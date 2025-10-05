<?php
include 'config.php';
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    
    $sql = "INSERT INTO students (name, email, phone, course, year) 
            VALUES ('$name', '$email', '$phone', '$course', '$year')";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Student added successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error adding student: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get all students
$students = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System - Part 1</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f5f5f5; color: #333; line-height: 1.6; }
        .container { width: 90%; max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background-color: #2c3e50; color: white; padding: 1rem 0; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        nav { background-color: #34495e; padding: 0.5rem 0; }
        nav ul { display: flex; list-style: none; justify-content: center; }
        nav ul li { margin: 0 15px; }
        nav ul li a { color: white; text-decoration: none; padding: 5px 10px; border-radius: 4px; transition: background-color 0.3s; }
        nav ul li a:hover, nav ul li a.active { background-color: #1abc9c; }
        .card { background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .tabs { display: flex; margin-bottom: 20px; border-bottom: 1px solid #ddd; }
        .tab { padding: 10px 20px; cursor: pointer; border-bottom: 3px solid transparent; }
        .tab.active { border-bottom: 3px solid #1abc9c; font-weight: bold; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        button { background-color: #1abc9c; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-size: 1rem; transition: background-color 0.3s; }
        button:hover { background-color: #16a085; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:hover { background-color: #f9f9f9; }
        .alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        footer { text-align: center; padding: 20px 0; margin-top: 40px; color: #7f8c8d; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Student Management System - Part 1 (Create & Read)</h1>
        </div>
    </header>
    
    <nav>
        <div class="container">
            <ul>
                <li><a href="index.php" class="active">Student Dashboard</a></li>
                <li><a href="management.php">Management Dashboard (Part 2)</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="container">
        <!-- Display Messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Student Dashboard</h2>
            
            <div class="tabs">
                <div class="tab active" data-tab="view-students">View Students</div>
                <div class="tab" data-tab="add-student">Add Student</div>
            </div>
            
            <!-- View Students Tab -->
            <div id="view-students" class="tab-content active">
                <h3>Student List</h3>
                <?php if (mysqli_num_rows($students) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Course</th>
                                <th>Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($student = mysqli_fetch_assoc($students)): ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
                                    <td><?php echo htmlspecialchars($student['name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['email']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($student['course']); ?></td>
                                    <td><?php echo $student['year']; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No students found. Add some students to get started.</p>
                <?php endif; ?>
            </div>
            
            <!-- Add Student Tab -->
            <div id="add-student" class="tab-content">
                <h3>Add New Student</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="course">Course *</label>
                        <select id="course" name="course" required>
                            <option value="">Select Course</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Business Administration">Business Administration</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Medicine">Medicine</option>
                            <option value="Law">Law</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="year">Year *</label>
                        <select id="year" name="year" required>
                            <option value="">Select Year</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <button type="submit">Add Student</button>
                </form>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>Student Management System - Part 1 (Create & Read) &copy; 2023</p>
        </div>
    </footer>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Update active tab
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Show target tab content, hide others
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>