
<?php
include 'auth.php';
include 'db.php';

$message = "";

if (isset($_POST['change'])) {

    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $username = $_SESSION['admin'];

    // Get current admin password
    $stmt = mysqli_prepare($conn,
        "SELECT password FROM admin WHERE username=?");

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$admin || !password_verify($current, $admin['password'])) {

        $message = "Current password is incorrect.";

    } elseif (strlen($new) < 8) {

        $message = "New password must contain at least 8 characters.";

    } elseif ($new !== $confirm) {

        $message = "New passwords do not match.";

    } else {

        $hashed_password = password_hash($new, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn,
            "UPDATE admin SET password=? WHERE username=?");

        mysqli_stmt_bind_param(
            $stmt, "ss", $hashed_password, $username
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "Password changed successfully!";
        } else {
            $message = "Unable to change password.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Admin Password</title>
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

    <h1>Change Admin Password</h1>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <div class="form-box">

        <form method="POST">

            <label>Current Password</label>
            <input type="password" name="current_password" required>

            <label>New Password</label>
            <input type="password" name="new_password" required>

            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="change">
                Change Password
            </button>

        </form>

    </div>

</div>

</body>
</html>
