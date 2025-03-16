<?php
session_start();
include 'db_config.php';

// Debug session (remove after testing)
echo "<!-- Debug: " . print_r($_SESSION, true) . " -->";

// Validate session and role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'student', 'teacher'])) {
    header("Location: login.php");
    exit();
}

// CSRF token generation (for admin form submission)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle adding schedule (admin only)
$message = '';
if (isset($_POST['add_schedule']) && $_SESSION['role'] === 'admin' && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    $exam_name = $_POST['exam_name'];
    $subject = $_POST['subject'];
    $exam_date = $_POST['exam_date'];
    $class = $_POST['class'];
    $department = $_POST['department'];

    $sql = "INSERT INTO exam_schedule (exam_name, subject, exam_date, class, department) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssss", $exam_name, $subject, $exam_date, $class, $department);
        if ($stmt->execute()) {
            $message = "Schedule added successfully!";
        } else {
            $message = "Error: Failed to add schedule.";
        }
        $stmt->close();
    } else {
        $message = "Error: Database prepare failed.";
    }
}

// Fetch schedules (viewable by all roles)
$schedules = $conn->query("SELECT * FROM exam_schedule");
if ($schedules === false) {
    die("Error fetching schedules: " . $conn->error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam Schedule</title>
    <style>
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
        h2, h3 {
            font-size: 2rem;
            color: #60a5fa;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
            margin-bottom: 30px;
            text-align: center;
        }
        h3 {
            font-size: 1.5rem;
            margin-top: 40px;
        }
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
            margin-bottom: 40px;
        }
        form:hover {
            transform: translateY(-5px);
        }
        input[type="text"],
        input[type="date"] {
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
        input[type="text"]::placeholder {
            color: #b0b0c0;
        }
        input[type="text"]:focus,
        input[type="date"]:focus {
            background: #454570;
            box-shadow: 0 0 8px rgba(96, 165, 250, 0.5);
        }
        input[type="date"] {
            appearance: none;
            cursor: pointer;
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
        table {
            width: 100%;
            max-width: 900px;
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
        a {
            color: #60a5fa;
            text-decoration: none;
            font-size: 1rem;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #3b82f6;
            text-decoration: underline;
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            width: 100%;
            max-width: 450px;
        }
        .success {
            background: #34c759;
            color: #ffffff;
        }
        .error {
            background: #ff4444;
            color: #ffffff;
        }
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
            input[type="date"],
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
    <h2>Exam Schedule</h2>
    <?php if ($message && $_SESSION['role'] === 'admin') { ?>
        <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php } ?>
    <?php if ($_SESSION['role'] === 'admin') { ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value "<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" name="exam_name" placeholder="Exam Name" required>
            <input type="text" name="subject" placeholder="Subject" required>
            <input type="date" name="exam_date" required>
            <input type="text" name="class" placeholder="Class" required>
            <input type="text" name="department" placeholder="Department" required>
            <button type="submit" name="add_schedule">Add Schedule</button>
        </form>
    <?php } ?>
    <h3>Schedule List</h3>
    <table border="1">
        <tr><th>Exam Name</th><th>Subject</th><th>Date</th><th>Class</th><th>Department</th></tr>
        <?php while ($row = $schedules->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['exam_name']); ?></td>
                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                <td><?php echo htmlspecialchars($row['exam_date']); ?></td>
                <td><?php echo htmlspecialchars($row['class']); ?></td>
                <td><?php echo htmlspecialchars($row['department']); ?></td>
            </tr>
        <?php } ?>
    </table>
    <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : ($_SESSION['role'] === 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'); ?>">Back</a>
</body>
</html>