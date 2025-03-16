<?php
session_start();
include 'db_config.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        /* Reset default styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: url('background.jpeg') no-repeat center center/cover;
            color: #f5f7fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(20, 40, 80, 0.1), rgba(10, 20, 38, 0.9));
            z-index: -1;
        }

        /* Header with Hamburger */
        h2 {
            font-size: 1.9rem;
            color:rgb(0, 206, 52);
            padding: 40px 40px 20px;
            text-align: left;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-shadow: 0 0 8px rgba(212, 175, 55, 0.4);
            display: flex;
            justify-content: space-between;
            align-items: center;
            opacity: 0;
            animation: fadeIn 0.5s ease forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        .hamburger {
            background: #1e40af;
            color: #f5f7fa;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 1.5rem;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.5);
        }

        .hamburger:hover {
            background: #3b82f6;
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
        }

        /* Menu Panel */
        .menu-panel {
            position: fixed;
            top: 0;
            left: -300px;
            width: 300px;
            height: 100%;
            background: rgba(15, 23, 42, 0.95);
            padding: 100px 35px 35px;
            border-radius: 0 20px 20px 0;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
            transition: left 0.4s ease;
            z-index: 1000;
        }

        .menu-panel.active {
            left: 0;
        }

        .menu-panel a {
            display: flex;
            align-items: center;
            padding: 14px 25px;
            color: #cbd5e1;
            text-decoration: none;
            font-size: 1.15rem;
            font-weight: 500;
            border-radius: 12px;
            margin-bottom: 18px;
            opacity: 0;
            animation: fadeInUp 0.4s ease forwards;
            animation-delay: calc(0.1s * var(--i));
        }

        .menu-panel a:nth-child(1) { --i: 1; }
        .menu-panel a:nth-child(2) { --i: 2; }
        .menu-panel a:nth-child(3) { --i: 3; }
        .menu-panel a:nth-child(4) { --i: 4; }
        .menu-panel a:nth-child(5) { --i: 5; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .menu-panel a:hover {
            background: #1e40af;
            color: #f5f7fa;
            padding-left: 30px;
            box-shadow: 0 4px 15px rgba(30, 64, 175, 0.5);
        }

        .menu-panel a::before {
            content: '✦';
            margin-right: 15px;
            font-size: 1rem;
            color: #d4af37;
            transition: transform 0.3s ease;
        }

        .menu-panel a:hover::before {
            color: #f5f7fa;
            transform: scale(1.2);
        }

        /* Main Content */
        .main-content {
            width: 100%;
            max-width: 1440px;
            margin: 0 auto;
            padding: 0 40px 40px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
            transition: margin-left 0.4s ease;
        }

        .main-content.shifted {
            margin-left: 300px;
        }

        .card {
            background: rgba(15, 23, 42, 0.95);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.6);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            opacity: 0;
            animation: fadeInUp 0.5s ease forwards;
            animation-delay: calc(0.1s * var(--i));
        }

        .card:nth-child(1) { --i: 1; }
        .card:nth-child(2) { --i: 2; }
        .card:nth-child(3) { --i: 3; }
        .card:nth-child(4) { --i: 4; }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.7);
        }

        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(30, 64, 175, 0.15), transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .card:hover::before {
            opacity: 1;
        }

        .card h3 {
            font-size: 1.6rem;
            color: #d4af37;
            margin-bottom: 20px;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-shadow: 0 0 6px rgba(212, 175, 55, 0.3);
        }

        .card p {
            color: #cbd5e1;
            font-size: 1.05rem;
            margin-bottom: 12px;
        }

        .card .stat {
            font-size: 2rem;
            color: #1e40af;
            font-weight: 700;
            margin: 20px 0;
            padding: 10px 20px;
            background: rgba(30, 64, 175, 0.15);
            border-radius: 10px;
            display: inline-block;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .card:hover .stat {
            background: rgba(30, 64, 175, 0.25);
            transform: scale(1.05);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .main-content {
                padding: 0 30px 30px;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
            .menu-panel {
                width: 260px;
            }
            .main-content.shifted {
                margin-left: 260px;
            }
        }

        @media (max-width: 768px) {
            h2 {
                padding: 30px 20px 15px;
            }
            .menu-panel {
                width: 100%;
                border-radius: 0;
                padding: 80px 25px 25px;
            }
            .main-content {
                padding: 0 20px 20px;
                grid-template-columns: 1fr;
            }
            .main-content.shifted {
                margin-left: 0;
            }
            .menu-panel.active {
                left: 0;
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.6rem;
                padding: 20px 15px 10px;
            }
            .hamburger {
                width: 35px;
                height: 35px;
                font-size: 1.2rem;
            }
            .menu-panel {
                padding: 70px 20px 20px;
            }
            .menu-panel a {
                font-size: 1.05rem;
                padding: 12px 20px;
            }
            .main-content {
                padding: 0 15px 15px;
            }
            .card {
                padding: 25px;
            }
            .card h3 {
                font-size: 1.4rem;
            }
            .card p {
                font-size: 1rem;
            }
            .card .stat {
                font-size: 1.8rem;
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>
    <h2>Welcome, Student <button class="hamburger" onclick="toggleMenu()">☰</button></h2>
    <div class="menu-panel" id="menuPanel">
        <a href="exam_schedule.php">View Exam Schedule</a>
        <a href="notifications.php">View Notifications</a>
        <a href="feedback.php">Submit Feedback</a>
        <a href="view_results.php">View Results</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="main-content" id="mainContent">
        <div class="card">
            <h3>Upcoming Exams</h3>
            <p>Next scheduled exam</p>
            <div class="stat">March 20, 2025</div>
            <p>Subject: Mathematics</p>
        </div>
        <div class="card">
            <h3>Notifications</h3>
            <p>Latest update from admin</p>
            <div class="stat">2 New</div>
            <p>Check for timetable changes</p>
        </div>
        <div class="card">
            <h3>Recent Results</h3>
            <p>Last published result</p>
            <div class="stat">85%</div>
            <p>Science - Feb 2025</p>
        </div>
        <div class="card">
            <h3>Feedback Status</h3>
            <p>Your last feedback</p>
            <div class="stat">Submitted</div>
            <p>Mar 10, 2025</p>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const menuPanel = document.getElementById('menuPanel');
            const mainContent = document.getElementById('mainContent');
            menuPanel.classList.toggle('active');
            mainContent.classList.toggle('shifted');
        }
    </script>
</body>
</html>