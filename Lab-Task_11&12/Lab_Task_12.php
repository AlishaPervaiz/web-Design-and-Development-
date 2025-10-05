<?php
include 'config.php';
session_start();

// Handle delete student
if (isset($_GET['delete_id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_id']);
    $sql = "DELETE FROM students WHERE id = '$id'";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Student deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting student: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: management.php");
    exit();
}

// Handle update student
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year = mysqli_real_escape_string($conn, $_POST['year']);
    
    $sql = "UPDATE students SET name='$name', email='$email', phone='$phone', 
            course='$course', year='$year' WHERE id='$id'";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Student updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating student: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: management.php");
    exit();
}

// Get all students
$students = mysqli_query($conn, "SELECT * FROM students ORDER BY id DESC");

// Get student for editing
$edit_student = null;
if (isset($_GET['edit_id'])) {
    $edit_id = mysqli_real_escape_string($conn, $_GET['edit_id']);
    $result = mysqli_query($conn, "SELECT * FROM students WHERE id = '$edit_id'");
    $edit_student = mysqli_fetch_assoc($result);
}

// Get statistics
$total_students = mysqli_query($conn, "SELECT COUNT(*) as total FROM students");
$total_students = mysqli_fetch_assoc($total_students)['total'];

$cs_students = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE course = 'Computer Science'");
$cs_students = mysqli_fetch_assoc($cs_students)['total'];

$eng_students = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE course = 'Engineering'");
$eng_students = mysqli_fetch_assoc($eng_students)['total'];

$bus_students = mysqli_query($conn, "SELECT COUNT(*) as total FROM students WHERE course = 'Business Administration'");
$bus_students = mysqli_fetch_assoc($bus_students)['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System - Part 2</title>
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
        .dashboard { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-top: 20px; }
        .stat-card { background-color: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; text-align: center; transition: transform 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { font-size: 2.5rem; margin: 10px 0; color: #1abc9c; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        button { background-color: #1abc9c; color: white; border: none; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-size: 1rem; transition: background-color 0.3s; }
        button:hover { background-color: #16a085; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:hover { background-color: #f9f9f9; }
        .action-buttons { display: flex; gap: 5px; }
        .btn-edit { background-color: #3498db; }
        .btn-delete { background-color: #e74c3c; }
        .alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .modal { display: <?php echo $edit_student ? 'block' : 'none'; ?>; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; }
        .modal-content { background-color: white; margin: 5% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 600px; }
        footer { text-align: center; padding: 20px 0; margin-top: 40px; color: #7f8c8d; border-top: 1px solid #eee; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Student Management System - Part 2 (Update & Delete)</h1>
        </div>
    </header>
    
    <nav>
        <div class="container">
            <ul>
                <li><a href="index.php">Student Dashboard (Part 1)</a></li>
                <li><a href="management.php" class="active">Management Dashboard</a></li>
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

        <h2>Management Dashboard</h2>
        
        <div class="dashboard">
            <div class="stat-card">
                <h3><?php echo $total_students; ?></h3>
                <p>Total Students</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $cs_students; ?></h3>
                <p>Computer Science</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $eng_students; ?></h3>
                <p>Engineering</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $bus_students; ?></h3>
                <p>Business</p>
            </div>
        </div>
        
        <div class="card">
            <h3>Manage Students</h3>
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
                            <th>Actions</th>
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
                                <td class="action-buttons">
                                    <a href="management.php?edit_id=<?php echo $student['id']; ?>" class="btn-edit" style="text-decoration: none; padding: 5px 10px; border-radius: 3px;">Edit</a>
                                    <a href="management.php?delete_id=<?php echo $student['id']; ?>" class="btn-delete" style="text-decoration: none; padding: 5px 10px; border-radius: 3px;" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No students found. <a href="index.php">Add some students</a> to get started.</p>
            <?php endif; ?>
        </div>
        
        <!-- Edit Student Modal -->
        <?php if ($edit_student): ?>
        <div class="modal" id="editModal">
            <div class="modal-content">
                <h3>Edit Student</h3>
                <form method="POST" action="">
                    <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
                    <input type="hidden" name="update" value="1">
                    <div class="form-group">
                        <label for="edit-name">Full Name *</label>
                        <input type="text" id="edit-name" name="name" value="<?php echo htmlspecialchars($edit_student['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email">Email *</label>
                        <input type="email" id="edit-email" name="email" value="<?php echo htmlspecialchars($edit_student['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-phone">Phone *</label>
                        <input type="tel" id="edit-phone" name="phone" value="<?php echo htmlspecialchars($edit_student['phone']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-course">Course *</label>
                        <select id="edit-course" name="course" required>
                            <option value="">Select Course</option>
                            <option value="Computer Science" <?php echo $edit_student['course'] == 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                            <option value="Business Administration" <?php echo $edit_student['course'] == 'Business Administration' ? 'selected' : ''; ?>>Business Administration</option>
                            <option value="Engineering" <?php echo $edit_student['course'] == 'Engineering' ? 'selected' : ''; ?>>Engineering</option>
                            <option value="Medicine" <?php echo $edit_student['course'] == 'Medicine' ? 'selected' : ''; ?>>Medicine</option>
                            <option value="Law" <?php echo $edit_student['course'] == 'Law' ? 'selected' : ''; ?>>Law</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-year">Year *</label>
                        <select id="edit-year" name="year" required>
                            <option value="">Select Year</option>
                            <option value="1" <?php echo $edit_student['year'] == '1' ? 'selected' : ''; ?>>1st Year</option>
                            <option value="2" <?php echo $edit_student['year'] == '2' ? 'selected' : ''; ?>>2nd Year</option>
                            <option value="3" <?php echo $edit_student['year'] == '3' ? 'selected' : ''; ?>>3rd Year</option>
                            <option value="4" <?php echo $edit_student['year'] == '4' ? 'selected' : ''; ?>>4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit">Update Student</button>
                        <a href="management.php" style="padding: 10px 15px; background-color: #95a5a6; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <div class="container">
            <p>Student Management System - Part 2 (Update & Delete) &copy; 2023</p>
        </div>
    </footer>

    <script>
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                window.location.href = 'management.php';
            }
        }
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>