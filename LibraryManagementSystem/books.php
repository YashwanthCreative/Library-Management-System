
<?php
include 'db.php';

$message = "";

// ADD OR UPDATE BOOK
if (isset($_POST['save'])) {

    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    $book_id = (int)($_POST['book_id'] ?? 0);

    if ($title === "" || $author === "" || $quantity < 0) {
        $message = "Please enter valid book details.";
    } else {

        
if ($book_id > 0) {

    $stmt = mysqli_prepare($conn,
        "UPDATE books
        SET title=?, author=?, category=?,
        available_quantity = available_quantity + (? - quantity),
        quantity=?
        WHERE book_id=? AND ? >= (quantity - available_quantity)");

    mysqli_stmt_bind_param(
        $stmt, "sssiiii",
        $title, $author, $category,
        $quantity, $quantity, $book_id, $quantity
    );

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) >= 0) {
        $message = "Book updated successfully!";
    } else {
        $message = "Unable to update book.";
    }
}
 else {

            $stmt = mysqli_prepare($conn,
                "INSERT INTO books
                (title, author, category, quantity, available_quantity)
                VALUES (?, ?, ?, ?, ?)");

            mysqli_stmt_bind_param(
                $stmt, "sssii",
                $title, $author, $category, $quantity, $quantity
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Book added successfully!";
            }
        }
    }
}

// DELETE BOOK
if (isset($_POST['delete'])) {

    $book_id = (int)$_POST['book_id'];

    $stmt = mysqli_prepare($conn,
        "DELETE FROM books WHERE book_id=?");

    mysqli_stmt_bind_param($stmt, "i", $book_id);

    if (mysqli_stmt_execute($stmt)) {
        $message = "Book deleted successfully!";
    } else {
        $message = "Cannot delete this book because it has issue records.";
    }
}

// EDIT BOOK - GET EXISTING DETAILS
$edit_book = null;

if (isset($_GET['edit'])) {

    $book_id = (int)$_GET['edit'];

    $stmt = mysqli_prepare($conn,
        "SELECT * FROM books WHERE book_id=?");

    mysqli_stmt_bind_param($stmt, "i", $book_id);
    mysqli_stmt_execute($stmt);

    $edit_book = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// SEARCH BOOKS
$search = trim($_GET['search'] ?? '');

if ($search !== "") {

    $stmt = mysqli_prepare($conn,
        "SELECT * FROM books
        WHERE title LIKE ? OR author LIKE ?
        ORDER BY book_id DESC");

    $term = "%".$search."%";

    mysqli_stmt_bind_param($stmt, "ss", $term, $term);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $result = mysqli_query($conn,
        "SELECT * FROM books ORDER BY book_id DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Books</title>
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

    <h1>Book Management</h1>

    <p>Manage your library book collection.</p>

    <?php if ($message != "") { ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <div class="form-box">

        <h2>
            <?php echo $edit_book ? "Edit Book" : "Add New Book"; ?>
        </h2>

        <form method="POST" action="books.php">

            <input type="hidden" name="book_id"
            value="<?php echo $edit_book['book_id'] ?? 0; ?>">

            <label>Book Title</label>
            <input type="text" name="title"
            value="<?php echo htmlspecialchars($edit_book['title'] ?? ''); ?>"
            required>

            <label>Author</label>
            <input type="text" name="author"
            value="<?php echo htmlspecialchars($edit_book['author'] ?? ''); ?>"
            required>

            <label>Category</label>
            <input type="text" name="category"
            value="<?php echo htmlspecialchars($edit_book['category'] ?? ''); ?>">

            <label>Quantity</label>
            <input type="number" name="quantity" min="0"
            value="<?php echo $edit_book['quantity'] ?? ''; ?>"
            required>

            <button type="submit" name="save">
                <?php echo $edit_book ? "Update Book" : "Add Book"; ?>
            </button>

        </form>

    </div>

    <h2>Book List</h2>

    <form method="GET" action="books.php">
        <input type="text" name="search"
        placeholder="Search by title or author"
        value="<?php echo htmlspecialchars($search); ?>">

        <button type="submit">Search</button>
        <a href="books.php">Show All</a>
    </form>

    <table class="book-table">

        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Category</th>
            <th>Quantity</th>
            <th>Available</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

        <tr>
            <td><?php echo $row['book_id']; ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['author']); ?></td>
            <td><?php echo htmlspecialchars($row['category']); ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td><?php echo $row['available_quantity']; ?></td>

            <td>
                <a href="books.php?edit=<?php echo $row['book_id']; ?>">
                    Edit
                </a>

                <form method="POST" action="books.php"
                onsubmit="return confirm('Delete this book?');">

                    <input type="hidden" name="book_id"
                    value="<?php echo $row['book_id']; ?>">

                    <button type="submit" name="delete">
                        Delete
                    </button>

                </form>
            </td>
        </tr>

        <?php } ?>

    </table>

</div>

</body>
</html>
