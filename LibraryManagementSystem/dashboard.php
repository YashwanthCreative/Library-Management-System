<?php
include 'auth.php';
include 'db.php';

// Count total books
$books = mysqli_query($conn, "SELECT COUNT(*) AS total FROM books");
$book_count = mysqli_fetch_assoc($books)['total'];

// Count total students
$students = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
$student_count = mysqli_fetch_assoc($students)['total'];

// Count issued books
$issued = mysqli_query($conn, "SELECT COUNT(*) AS total FROM issues WHERE status='Issued'");
$issued_count = mysqli_fetch_assoc($issued)['total'];

// Count overdue books
$overdue = mysqli_query($conn, "SELECT COUNT(*) AS total FROM issues WHERE status='Issued' AND due_date < CURDATE()");
$overdue_count = mysqli_fetch_assoc($overdue)['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Management System</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="sidebar">
    <h2>Library System</h2>

    <a href="dashboard.php">Dashboard</a>
    <a href="books.php">Manage Books</a>
    <a href="students.php">Manage Students</a>
    <a href="issue_book.php">Issue Books</a>
    <a href="return_book.php">Return Books</a>
    <a href="overdue.php">Overdue Reports</a>
    <a href="change_password.php">Change Password</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <h1>Library Dashboard</h1>
    <p>Welcome to Library Book Issue and Return Management System</p>

    <div class="cards">

        <div class="card">
            <h3>Total Books</h3>
            <h1><?php echo $book_count; ?></h1>
        </div>

        <div class="card">
            <h3>Total Students</h3>
            <h1><?php echo $student_count; ?></h1>
        </div>

        <div class="card">
            <h3>Issued Books</h3>
            <h1><?php echo $issued_count; ?></h1>
        </div>

        <div class="card">
            <h3>Overdue Books</h3>
            <h1><?php echo $overdue_count; ?></h1>
        </div>

    </div>

</div>

</body>
</html>
