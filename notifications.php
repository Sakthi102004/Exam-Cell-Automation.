<?php
session_start();
include 'db_config.php';
if (!isset($_SESSION['role'])) header("Location: login.php");

if (isset($_POST['send_notification']) && $_SESSION['role'] == 'admin') {
    $message = $_POST['message'];
    $sent_by = $_SESSION['user_id'];
    
    $sql = "INSERT INTO notifications (message, sent_by) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $message, $sent_by);
    $stmt->execute();
}

$notifications = $conn->query("SELECT n.message, n.sent_at, u.name FROM notifications n JOIN users u ON n.sent_by = u.id ORDER BY n.sent_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
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
        h2 {
            font-size: 2rem;
            color: #60a5fa;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
            margin-bottom: 30px;
            text-align: center;
        }

        /* Form styling (for admin) */
        form {
            background: #2a2a4a;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            transition: transform 0.3s ease;
            margin-bottom: 40px;
        }

        form:hover {
            transform: translateY(-5px);
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            background: #3b3b5a;
            color: #ffffff;
            font-size: 1rem;
            outline: none;
            resize: vertical;
            min-height: 120px;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }

        textarea::placeholder {
            color: #b0b0c0;
        }

        textarea:focus {
            background: #454570;
            box-shadow: 0 0 8px rgba(96, 165, 250, 0.5);
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

        /* Table styling */
        table {
            width: 100%;
            max-width: 800px;
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

        /* Back link */
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
            form {
                padding: 20px;
            }
            textarea, button[type="submit"] {
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
    <?php if ($_SESSION['role'] == 'admin') { ?>
        <h2>Send Notification</h2>
        <form method="POST">
            <textarea name="message" placeholder="Notification Message" required></textarea>
            <button type="submit" name="send_notification">Send</button>
        </form>
    <?php } ?>
    <h2>Notifications</h2>
    <table border="1">
        <tr><th>Message</th><th>Sent By</th><th>Date</th></tr>
        <?php while ($notif = $notifications->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $notif['message']; ?></td>
                <td><?php echo $notif['name']; ?></td>
                <td><?php echo $notif['sent_at']; ?></td>
            </tr>
        <?php } ?>
    </table>
    <a href="<?php echo $_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : ($_SESSION['role'] == 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'); ?>">Back</a>
</body>
</html>