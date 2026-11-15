
<?php
include 'db.php';
include 'auth.php';

$message = "";

// RETURN BOOK
if (isset($_POST['return'])) {

    $issue_id = (int)$_POST['issue_id'];
    $return_date = date('Y-m-d');
    $fine_per_day = 5;

    mysqli_begin_transaction($conn);

    try {

        // Get issue details
        $stmt = mysqli_prepare($conn,
            "SELECT book_id, due_date, status
             FROM issues
             WHERE issue_id=? FOR UPDATE");

        mysqli_stmt_bind_param($stmt, "i", $issue_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $issue = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$issue || $issue['status'] != 'Issued') {
            throw new Exception("Invalid or already returned book.");
        }

        // Calculate fine
        $due = new DateTime($issue['due_date']);
        $returned = new DateTime($return_date);

        $late_days = 0;

        if ($returned > $due) {
            $late_days = $due->diff($returned)->days;
        }

        $fine = $late_days * $fine_per_day;

        // Insert return record
        $stmt = mysqli_prepare($conn,
            "INSERT INTO returns (issue_id, return_date, fine)
             VALUES (?, ?, ?)");

        mysqli_stmt_bind_param(
            $stmt, "isd", $issue_id, $return_date, $fine
        );

        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Update issue status
        $stmt = mysqli_prepare($conn,
            "UPDATE issues SET status='Returned'
             WHERE issue_id=?");

        mysqli_stmt_bind_param($stmt, "i", $issue_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Increase available quantity
        $stmt = mysqli_prepare($conn,
            "UPDATE books
             SET available_quantity = available_quantity + 1
             WHERE book_id=?");

        mysqli_stmt_bind_param($stmt, "i", $issue['book_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($conn);

        $message = "Book returned successfully! Fine: ₹" . number_format($fine, 2);

    } catch (Exception $e) {

        mysqli_rollback($conn);
        $message = $e->getMessage();
    }
}

// GET CURRENTLY ISSUED BOOKS
$issued = mysqli_query($conn,
    "SELECT issues.issue_id, students.name,
            books.title, issues.issue_date, issues.due_date
     FROM issues
     JOIN students ON issues.student_id = students.student_id
     JOIN books ON issues.book_id = books.book_id
     WHERE issues.status='Issued'
     ORDER BY issues.issue_id DESC");

// GET RETURN HISTORY
$returns = mysqli_query($conn,
    "SELECT returns.return_id, students.name,
            books.title, returns.return_date, returns.fine
     FROM returns
     JOIN issues ON returns.issue_id = issues.issue_id
     JOIN students ON issues.student_id = students.student_id
     JOIN books ON issues.book_id = books.book_id
     ORDER BY returns.return_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Return Books</title>
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

    <h1>Return Books</h1>
    <p>Manage book returns and fines.</p>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <h2>Currently Issued Books</h2>

    <table class="book-table">
        <tr>
            <th>Issue ID</th>
            <th>Student</th>
            <th>Book</th>
            <th>Issue Date</th>
            <th>Due Date</th>
            <th>Action</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($issued)) { ?>
        <tr>
            <td><?php echo $row['issue_id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo $row['issue_date']; ?></td>
            <td><?php echo $row['due_date']; ?></td>

            <td>
                <form method="POST"
                      onsubmit="return confirm('Return this book?');">

                    <input type="hidden" name="issue_id"
                           value="<?php echo $row['issue_id']; ?>">

                    <button type="submit" name="return">
                        Return
                    </button>

                </form>
            </td>
        </tr>
        <?php } ?>

    </table>

    <h2 style="margin-top:30px;">Return History</h2>

    <table class="book-table">
        <tr>
            <th>Return ID</th>
            <th>Student</th>
            <th>Book</th>
            <th>Return Date</th>
            <th>Fine</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($returns)) { ?>
        <tr>
            <td><?php echo $row['return_id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo $row['return_date']; ?></td>
            <td>₹<?php echo number_format($row['fine'], 2); ?></td>
        </tr>
        <?php } ?>

    </table>

</div>

</body>
</html>
