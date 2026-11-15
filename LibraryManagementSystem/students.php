
<?php
include 'db.php';
include 'auth.php';
$message = "";

// ADD OR UPDATE STUDENT
if (isset($_POST['save'])) {

    $student_id = (int)$_POST['student_id'];
    $name = trim($_POST['name']);
    $roll_no = trim($_POST['roll_no']);
    $department = trim($_POST['department']);
    $phone = trim($_POST['phone']);

    if ($name == "" || $roll_no == "" || $department == "") {
        $message = "Please fill all required fields.";
    } else {

        if ($student_id > 0) {

            $stmt = mysqli_prepare($conn,
                "UPDATE students
                 SET name=?, roll_no=?, department=?, phone=?
                 WHERE student_id=?");

            mysqli_stmt_bind_param(
                $stmt, "ssssi",
                $name, $roll_no, $department, $phone, $student_id
            );

        } else {

            $stmt = mysqli_prepare($conn,
                "INSERT INTO students (name, roll_no, department, phone)
                 VALUES (?, ?, ?, ?)");

            mysqli_stmt_bind_param(
                $stmt, "ssss",
                $name, $roll_no, $department, $phone
            );
        }

        if (mysqli_stmt_execute($stmt)) {
            $message = "Student saved successfully!";
        } else {
            $message = "Unable to save student. Check whether the roll number already exists.";
        }

        mysqli_stmt_close($stmt);
    }
}

// DELETE STUDENT
if (isset($_POST['delete'])) {

    $student_id = (int)$_POST['student_id'];

    $stmt = mysqli_prepare($conn,
        "DELETE FROM students WHERE student_id=?");

    mysqli_stmt_bind_param($stmt, "i", $student_id);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Student deleted successfully!";
    } else {
        $message = "Cannot delete this student because they have issue records.";
    }

    mysqli_stmt_close($stmt);
}

// EDIT STUDENT
$edit_id = 0;
$name = "";
$roll_no = "";
$department = "";
$phone = "";

if (isset($_GET['edit'])) {

    $edit_id = (int)$_GET['edit'];

    $stmt = mysqli_prepare($conn,
        "SELECT * FROM students WHERE student_id=?");

    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);

    if ($student) {
        $name = $student['name'];
        $roll_no = $student['roll_no'];
        $department = $student['department'];
        $phone = $student['phone'];
    }

    mysqli_stmt_close($stmt);
}

// SEARCH STUDENTS
$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

$stmt = mysqli_prepare($conn,
    "SELECT * FROM students
     WHERE name LIKE ?
     OR roll_no LIKE ?
     OR department LIKE ?
     ORDER BY student_id DESC");

$search_value = "%" . $search . "%";

mysqli_stmt_bind_param(
    $stmt, "sss",
    $search_value, $search_value, $search_value
);

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
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
</div>

<div class="main">

    <h1>Manage Students</h1>
    <p>Add, edit, delete and search student details.</p>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <div class="form-box">

        <h2>
            <?php echo $edit_id > 0 ? "Edit Student" : "Add Student"; ?>
        </h2>

        <form method="POST" action="students.php">

            <input type="hidden" name="student_id"
                   value="<?php echo $edit_id; ?>">

            <label>Student Name</label>
            <input type="text" name="name"
                   value="<?php echo htmlspecialchars($name); ?>"
                   required>

            <label>Roll Number</label>
            <input type="text" name="roll_no"
                   value="<?php echo htmlspecialchars($roll_no); ?>"
                   required>

            <label>Department</label>
            <input type="text" name="department"
                   value="<?php echo htmlspecialchars($department); ?>"
                   required>

            <label>Phone Number</label>
            <input type="text" name="phone"
                   value="<?php echo htmlspecialchars($phone); ?>">

            <button type="submit" name="save">
                <?php echo $edit_id > 0 ? "Update Student" : "Add Student"; ?>
            </button>

        </form>

    </div>

    <h2>Student List</h2>

    <form method="GET" action="students.php">
        <input type="text" name="search"
               placeholder="Search students..."
               value="<?php echo htmlspecialchars($search); ?>">

        <button type="submit">Search</button>
        <a href="students.php">Show All</a>
    </form>

    <table class="book-table">

        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Roll Number</th>
            <th>Department</th>
            <th>Phone</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

        <tr>
            <td><?php echo $row['student_id']; ?></td>

            <td><?php echo htmlspecialchars($row['name']); ?></td>

            <td><?php echo htmlspecialchars($row['roll_no']); ?></td>

            <td><?php echo htmlspecialchars($row['department']); ?></td>

            <td><?php echo htmlspecialchars($row['phone'] ?? ''); ?></td>

            <td>
                <a href="students.php?edit=<?php echo $row['student_id']; ?>">
                    Edit
                </a>

                <form method="POST" action="students.php"
                      onsubmit="return confirm('Delete this student?');">

                    <input type="hidden" name="student_id"
                           value="<?php echo $row['student_id']; ?>">

                    <button type="submit" name="delete">Delete</button>

                </form>
            </td>
        </tr>

        <?php } ?>

    </table>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>
