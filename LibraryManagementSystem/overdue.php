
<?php
include 'db.php';
include 'auth.php';

// GET OVERDUE BOOKS
$sql = "SELECT
            issues.issue_id,
            students.name,
            students.roll_no,
            books.title,
            issues.issue_date,
            issues.due_date,
            DATEDIFF(CURDATE(), issues.due_date) AS overdue_days,
            DATEDIFF(CURDATE(), issues.due_date) * 5 AS estimated_fine
        FROM issues
        JOIN students ON issues.student_id = students.student_id
        JOIN books ON issues.book_id = books.book_id
        WHERE issues.status = 'Issued'
        AND issues.due_date < CURDATE()
        ORDER BY issues.due_date ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Overdue Reports</title>
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
    <a href="logout.php">Logout</a>
</div>

<div class="main">

    <h1>Overdue Reports</h1>
    <p>Books that have not been returned before their due date.</p>

    <h2>Overdue Books List</h2>

    <table class="book-table">

        <tr>
            <th>Issue ID</th>
            <th>Student</th>
            <th>Roll Number</th>
            <th>Book</th>
            <th>Issue Date</th>
            <th>Due Date</th>
            <th>Overdue Days</th>
            <th>Estimated Fine</th>
        </tr>

        <?php if (mysqli_num_rows($result) > 0) { ?>

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

            <tr>
                <td><?php echo $row['issue_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo $row['issue_date']; ?></td>
                <td><?php echo $row['due_date']; ?></td>
                <td><?php echo $row['overdue_days']; ?></td>
                <td>₹<?php echo number_format($row['estimated_fine'], 2); ?></td>
            </tr>

            <?php } ?>

        <?php } else { ?>

            <tr>
                <td colspan="8">No overdue books found.</td>
            </tr>

        <?php } ?>

    </table>

</div>

</body>
</html>
