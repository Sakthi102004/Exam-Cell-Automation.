<?php
session_start();
include 'db_config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Add single student
if (isset($_POST['add_student'])) {
    $roll_number = $_POST['roll_number'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $department = $_POST['department'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password, role, name) VALUES (?, ?, 'student', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $name);
    $stmt->execute();
    $user_id = $conn->insert_id;

    $sql = "INSERT INTO students (roll_number, name, class, department) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $roll_number, $name, $class, $department);
    $stmt->execute();
}

// Edit student
if (isset($_POST['edit_student'])) {
    $id = $_POST['id'];
    $roll_number = $_POST['roll_number'];
    $name = $_POST['name'];
    $class = $_POST['class'];
    $department = $_POST['department'];
    $username = $_POST['username'];
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    $sql = "UPDATE students SET roll_number = ?, name = ?, class = ?, department = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $roll_number, $name, $class, $department, $id);
    $stmt->execute();

    $sql = "UPDATE users SET username = ?, name = ?" . ($password ? ", password = ?" : "") . " WHERE username = (SELECT roll_number FROM students WHERE id = ?)";
    $stmt = $conn->prepare($sql);
    if ($password) {
        $stmt->bind_param("sssi", $username, $name, $password, $id);
    } else {
        $stmt->bind_param("ssi", $username, $name, $id);
    }
    $stmt->execute();
}

// Bulk add via CSV
if (isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, "r");
    fgetcsv($handle); // Skip header
    while (($data = fgetcsv($handle)) !== false) {
        $roll_number = $data[0];
        $name = $data[1];
        $class = $data[2];
        $department = $data[3];
        $username = $data[4];
        $password = password_hash($data[5], PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password, role, name) VALUES (?, ?, 'student', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $password, $name);
        $stmt->execute();

        $sql = "INSERT INTO students (roll_number, name, class, department) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $roll_number, $name, $class, $department);
        $stmt->execute();
    }
    fclose($handle);
}

// Delete student
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $sql = "DELETE FROM users WHERE username = (SELECT roll_number FROM students WHERE id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

$students = $conn->query("SELECT s.*, u.username FROM students s JOIN users u ON s.roll_number = u.username");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <style>
        /* Reset default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1e1e2f, #2a2a4a);
            color: #ffffff;
            min-height: 100vh;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Header styling */
        h2, h3 {
            font-size: 2rem;
            color: #60a5fa;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
            margin-bottom: 20px;
            text-align: center;
        }

        h3 {
            font-size: 1.5rem;
            margin-top: 40px;
        }

        /* Form styling */
        form {
            background: #2a2a4a;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 450px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }

        form:hover {
            transform: translateY(-5px);
        }

        input[type="text"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 8px;
            background: #3b3b5a;
            color: #ffffff;
            font-size: 1rem;
            outline: none;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: #b0b0c0;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="file"]:focus {
            background: #454570;
            box-shadow: 0 0 8px rgba(96, 165, 250, 0.5);
        }

        input[type="file"]::-webkit-file-upload-button {
            background: #60a5fa;
            color: #1e1e2f;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="file"]::-webkit-file-upload-button:hover {
            background: #3b82f6;
        }

        button[type="submit"] {
            padding: 12px;
            background: #60a5fa;
            color: #1e1e2f;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        button[type="submit"]:hover {
            background: #3b82f6;
            transform: scale(1.05);
        }

        button[type="submit"]:active {
            transform: scale(0.98);
        }

        small {
            color: #b0b0c0;
            font-size: 0.9rem;
            text-align: center;
        }

        /* Table styling */
        table {
            width: 100%;
            max-width: 1000px;
            border-collapse: collapse;
            background: #2a2a4a;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border: 1px solid #3b3b5a;
        }

        th {
            background: #60a5fa;
            color: #1e1e2f;
            font-weight: bold;
            text-transform: uppercase;
        }

        td {
            color: #d1d5db;
        }

        tr:nth-child(even) {
            background: #3b3b5a;
        }

        tr:hover {
            background: #454570;
            transition: background 0.3s ease;
        }

        /* Links in table and back link */
        a {
            color: #60a5fa;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #3b82f6;
            text-decoration: underline;
        }

        .back-link {
            margin-top: 20px;
            font-size: 1rem;
            display: inline-block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            form {
                max-width: 100%;
            }
            table {
                font-size: 0.9rem;
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            th, td {
                padding: 10px;
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.8rem;
            }
            h3 {
                font-size: 1.3rem;
            }
            form {
                padding: 20px;
            }
            input[type="text"],
            input[type="password"],
            input[type="file"],
            button[type="submit"] {
                font-size: 0.9rem;
                padding: 10px;
            }
            table {
                font-size: 0.8rem;
            }
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <h2>Manage Students</h2>
    <h3>Add Single Student</h3>
    <form method="POST">
        <input type="text" name="roll_number" placeholder="Roll Number" required>
        <input type="text" name="name" placeholder="Name" required>
        <input type="text" name="class" placeholder="Class" required>
        <input type="text" name="department" placeholder="Department" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="add_student">Add Student</button>
    </form>

    <h3>Bulk Add via CSV</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="csv_file" accept=".csv" required>
        <small>CSV Format: roll_number,name,class,department,username,password</small>
        <button type="submit">Upload CSV</button>
    </form>

    <h3>Student List</h3>
    <table border="1">
        <tr>
            <th>Roll Number</th>
            <th>Name</th>
            <th>Class</th>
            <th>Department</th>
            <th>Username</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $students->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['roll_number']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['class']; ?></td>
                <td><?php echo $row['department']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td>
                    <a href="?edit=<?php echo $row['id']; ?>">Edit</a> |
                    <a href="?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <?php if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $student = $conn->query("SELECT s.*, u.username FROM students s JOIN users u ON s.roll_number = u.username WHERE s.id = $id")->fetch_assoc();
    ?>
        <h3>Edit Student</h3>
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
            <input type="text" name="roll_number" value="<?php echo $student['roll_number']; ?>" required>
            <input type="text" name="name" value="<?php echo $student['name']; ?>" required>
            <input type="text" name="class" value="<?php echo $student['class']; ?>" required>
            <input type="text" name="department" value="<?php echo $student['department']; ?>" required>
            <input type="text" name="username" value="<?php echo $student['username']; ?>" required>
            <input type="password" name="password" placeholder="New Password (leave blank to keep current)">
            <button type="submit" name="edit_student">Update Student</button>
        </form>
    <?php } ?>
    <a href="admin_dashboard.php" class="back-link">Back</a>
</body>
</html>