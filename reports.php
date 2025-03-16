<?php
session_start();
include 'db_config.php';
if ($_SESSION['role'] != 'admin') header("Location: login.php");

// Fetch data for charts
$class_wise = $conn->query("SELECT s.class, AVG(r.marks) as avg_marks, COUNT(r.marks) as total_students, SUM(CASE WHEN r.marks >= 25 THEN 1 ELSE 0 END) as passed_students FROM results r JOIN students s ON r.student_id = s.id GROUP BY s.class");
$dept_wise = $conn->query("SELECT s.department, AVG(r.marks) as avg_marks, COUNT(r.marks) as total_students, SUM(CASE WHEN r.marks >= 25 THEN 1 ELSE 0 END) as passed_students FROM results r JOIN students s ON r.student_id = s.id GROUP BY s.department");
$overall = $conn->query("SELECT AVG(marks) as avg_marks, COUNT(marks) as total_students, SUM(CASE WHEN marks >= 25 THEN 1 ELSE 0 END) as passed_students FROM results")->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Result Reports</title>
    <style>
        /* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    margin: 0;
    padding: 20px;
    color: #333;
}

h2, h3, h4 {
    color: #2c3e50;
}

h2 {
    font-size: 28px;
    margin-bottom: 20px;
}

h3 {
    font-size: 24px;
    margin-top: 30px;
    margin-bottom: 15px;
}

h4 {
    font-size: 20px;
    margin-top: 10px;
    margin-bottom: 10px;
}

p {
    font-size: 16px;
    line-height: 1.6;
}

/* Flex Container for Charts */
.flex-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
}

.flex-container > div {
    flex: 1 1 calc(50% - 20px);
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
}

/* Canvas (Chart) Styles */
canvas {
    max-width: 100%;
    height: auto !important;
    margin-top: 10px;
}

/* Overall Statistics Section */
.overall-stats {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    padding: 20px;
    margin-top: 20px;
}

.overall-stats p {
    font-size: 18px;
    margin: 10px 0;
}

/* Back Link */
a {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #3498db;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

a:hover {
    background-color: #2980b9;
}

/* Responsive Design */
@media (max-width: 768px) {
    .flex-container > div {
        flex: 1 1 100%;
    }

    h2 {
        font-size: 24px;
    }

    h3 {
        font-size: 20px;
    }

    h4 {
        font-size: 18px;
    }

    p {
        font-size: 14px;
    }
}
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h2>Result Analysis Report</h2>

    <!-- Class-wise Charts -->
    <h3>Class-wise Analysis</h3>
    <div style="display: flex; flex-wrap: wrap;">
        <div style="width: 50%;">
            <h4>Average Marks (Bar Chart)</h4>
            <canvas id="classBarChart"></canvas>
        </div>
        <div style="width: 50%;">
            <h4>Pass Percentage (Pie Chart)</h4>
            <canvas id="classPieChart"></canvas>
        </div>
    </div>

    <!-- Department-wise Charts -->
    <h3>Department-wise Analysis</h3>
    <div style="display: flex; flex-wrap: wrap;">
        <div style="width: 50%;">
            <h4>Average Marks (Line Graph)</h4>
            <canvas id="deptLineChart"></canvas>
        </div>
        <div style="width: 50%;">
            <h4>Pass Percentage (Pie Chart)</h4>
            <canvas id="deptPieChart"></canvas>
        </div>
    </div>

    <!-- Overall Statistics -->
    <h3>Overall Statistics</h3>
    <p>Average Marks: <?php echo number_format($overall['avg_marks'], 2); ?></p>
    <p>Pass Percentage: <?php echo number_format(($overall['passed_students'] / $overall['total_students']) * 100, 2); ?>%</p>

    <script>
        // Class-wise Bar Chart (Average Marks)
        const classBarData = {
            labels: [<?php while ($row = $class_wise->fetch_assoc()) echo "'{$row['class']}',"; ?>],
            datasets: [{
                label: 'Average Marks',
                data: [<?php $class_wise->data_seek(0); while ($row = $class_wise->fetch_assoc()) echo "{$row['avg_marks']},"; ?>],
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };
        new Chart(document.getElementById('classBarChart'), {
            type: 'bar',
            data: classBarData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Average Marks'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Class'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Class-wise Average Marks',
                        font: { size: 16 }
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });

        // Class-wise Pie Chart (Pass Percentage)
        const classPieData = {
            labels: [<?php $class_wise->data_seek(0); while ($row = $class_wise->fetch_assoc()) echo "'{$row['class']}',"; ?>],
            datasets: [{
                label: 'Pass Percentage',
                data: [<?php $class_wise->data_seek(0); while ($row = $class_wise->fetch_assoc()) echo number_format(($row['passed_students'] / $row['total_students']) * 100, 2) . ","; ?>],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        };
        new Chart(document.getElementById('classPieChart'), {
            type: 'pie',
            data: classPieData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Class-wise Pass Percentage',
                        font: { size: 16 }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });

        // Department-wise Line Graph (Average Marks)
        const deptLineData = {
            labels: [<?php while ($row = $dept_wise->fetch_assoc()) echo "'{$row['department']}',"; ?>],
            datasets: [{
                label: 'Average Marks',
                data: [<?php $dept_wise->data_seek(0); while ($row = $dept_wise->fetch_assoc()) echo "{$row['avg_marks']},"; ?>],
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1,
                fill: false
            }]
        };
        new Chart(document.getElementById('deptLineChart'), {
            type: 'line',
            data: deptLineData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Average Marks'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Department'
                        }
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Department-wise Average Marks',
                        font: { size: 16 }
                    },
                    tooltip: {
                        enabled: true,
                        mode: 'index',
                        intersect: false
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });

        // Department-wise Pie Chart (Pass Percentage)
        const deptPieData = {
            labels: [<?php $dept_wise->data_seek(0); while ($row = $dept_wise->fetch_assoc()) echo "'{$row['department']}',"; ?>],
            datasets: [{
                label: 'Pass Percentage',
                data: [<?php $dept_wise->data_seek(0); while ($row = $dept_wise->fetch_assoc()) echo number_format(($row['passed_students'] / $row['total_students']) * 100, 2) . ","; ?>],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        };
        new Chart(document.getElementById('deptPieChart'), {
            type: 'pie',
            data: deptPieData,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Department-wise Pass Percentage',
                        font: { size: 16 }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        });
    </script>

    <a href="admin_dashboard.php">Back</a>
</body>
</html>