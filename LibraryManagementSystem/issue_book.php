
<?php
include 'db.php';
include 'auth.php';

$message = "";

// ISSUE BOOK
if (isset($_POST['issue'])) {

    $student_id = (int)$_POST['student_id'];
    $book_id = (int)$_POST['book_id'];
    $issue_date = $_POST['issue_date'];
    $due_date = $_POST['due_date'];

    if ($due_date < $issue_date) {
        $message = "Due date cannot be before issue date.";
    } else {

        mysqli_begin_transaction($conn);

        try {

            // Check available quantity
            $stmt = mysqli_prepare($conn,
                "SELECT available_quantity FROM books
                 WHERE book_id=? FOR UPDATE");

            mysqli_stmt_bind_param($stmt, "i", $book_id);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $book = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if (!$book || $book['available_quantity'] <= 0) {
                throw new Exception("Book is not available.");
            }

            // Insert issue record
            $stmt = mysqli_prepare($conn,
                "INSERT INTO issues
                 (student_id, book_id, issue_date, due_date, status)
                 VALUES (?, ?, ?, ?, 'Issued')");

            mysqli_stmt_bind_param(
                $stmt, "iiss",
                $student_id, $book_id, $issue_date, $due_date
            );

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            // Decrease available quantity
            $stmt = mysqli_prepare($conn,
                "UPDATE books
                 SET available_quantity = available_quantity - 1
                 WHERE book_id=?");

            mysqli_stmt_bind_param($stmt, "i", $book_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            mysqli_commit($conn);

            $message = "Book issued successfully!";

        } catch (Exception $e) {

            mysqli_rollback($conn);
            $message = $e->getMessage();
        }
    }
}

// GET STUDENTS
$students = mysqli_query($conn,
    "SELECT student_id, name, roll_no FROM students");

// GET AVAILABLE BOOKS
$books = mysqli_query($conn,
    "SELECT book_id, title, available_quantity
     FROM books WHERE available_quantity > 0");

// GET ISSUED BOOKS
$issued = mysqli_query($conn,
    "SELECT issues.issue_id, students.name,
            books.title, issues.issue_date,
            issues.due_date, issues.status
     FROM issues
     JOIN students ON issues.student_id = students.student_id
     JOIN books ON issues.book_id = books.book_id
     ORDER BY issues.issue_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Issue Books</title>
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

    <h1>Issue Books</h1>
    <p>Issue books to registered students.</p>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <div class="form-box">

        <h2>Issue New Book</h2>

        <form method="POST">

            <label>Select Student</label>
            <select name="student_id" required>
                <option value="">Choose Student</option>

                <?php while ($student = mysqli_fetch_assoc($students)) { ?>
                    <option value="<?php echo $student['student_id']; ?>">
                        <?php echo htmlspecialchars($student['name']); ?>
                        (<?php echo htmlspecialchars($student['roll_no']); ?>)
                    </option>
                <?php } ?>

            </select>

            <label>Select Book</label>
            <select name="book_id" required>
                <option value="">Choose Book</option>

                <?php while ($book = mysqli_fetch_assoc($books)) { ?>
                    <option value="<?php echo $book['book_id']; ?>">
                        <?php echo htmlspecialchars($book['title']); ?>
                        (Available: <?php echo $book['available_quantity']; ?>)
                    </option>
                <?php } ?>

            </select>

            <label>Issue Date</label>
            <input type="date" name="issue_date"
                   value="<?php echo date('Y-m-d'); ?>" required>

            <label>Due Date</label>
            <input type="date" name="due_date"
                   value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>"
                   required>

            <button type="submit" name="issue">Issue Book</button>

        </form>

    </div>

    <h2>Issued Books List</h2>

    <table class="book-table">
        <tr>
            <th>Issue ID</th>
            <th>Student</th>
            <th>Book</th>
            <th>Issue Date</th>
            <th>Due Date</th>
            <th>Status</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($issued)) { ?>
        <tr>
            <td><?php echo $row['issue_id']; ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo $row['issue_date']; ?></td>
            <td><?php echo $row['due_date']; ?></td>
            <td><?php echo $row['status']; ?></td>
        </tr>
        <?php } ?>

    </table>

</div>

</body>
</html>
