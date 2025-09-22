<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "book";

// always define variables to avoid warnings
$title = $author = $genre = $pub_year = "";
$title_errors = $author_errors = $genre_errors = $pub_year_errors = "";
$books = [];

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = htmlspecialchars(trim($_POST["title"]));
        $author = htmlspecialchars(trim($_POST["author"]));
        $genre = htmlspecialchars(trim($_POST["genre"]));
        $pub_year = htmlspecialchars(trim($_POST["pub_year"]));

        if (empty($author)) $author_errors = "This field is required";
        if (empty($title)) $title_errors = "This field is required";
        if (empty($genre)) $genre_errors = "This field is required";
        if (empty($pub_year)) {
            $pub_year_errors = "This field is required";
        } elseif ($pub_year > 2025) {
            $pub_year_errors = "Publication year must not be in the future";
        }

        if (empty($title_errors) && empty($author_errors) && empty($genre_errors) && empty($pub_year_errors)) {
            $stmt = $conn->prepare("INSERT INTO books (title, author, genre, pub_year) VALUES (?,?,?,?)");
            $stmt->execute([$title, $author, $genre, $pub_year]);

            // redirect to avoid duplicate form submission on refresh
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }

    $stmt = $conn->query("SELECT * FROM books ORDER BY id ASC");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p style='color:red'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add and View Books</title>
</head>
<body>

<h2>Add Book</h2>
<form method="POST" action="">
    <label for="title">Title</label><br>
    <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>">
    <span style="color:red;"><?= $title_errors ?></span><br><br>

    <label for="author">Author</label><br>
    <input type="text" name="author" id="author" value="<?= htmlspecialchars($author) ?>">
    <span style="color:red;"><?= $author_errors ?></span><br><br>

    <label for="genre">Genre</label><br>
    <select name="genre" id="genre">
        <option value="">--SELECT--</option>
        <option value="history" <?= $genre=="history"?"selected":"" ?>>History</option>
        <option value="science" <?= $genre=="science"?"selected":"" ?>>Science</option>
        <option value="fiction" <?= $genre=="fiction"?"selected":"" ?>>Fiction</option>
    </select>
    <span style="color:red;"><?= $genre_errors ?></span><br><br>

    <label for="pub_year">Publication Year</label><br>
    <input type="number" name="pub_year" id="pub_year" value="<?= htmlspecialchars($pub_year) ?>">
    <span style="color:red;"><?= $pub_year_errors ?></span><br><br>

    <input type="submit" value="Add Book">
</form>

<hr>

<h2>View Books</h2>
<?php if (count($books) > 0): ?>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th><th>Title</th><th>Author</th><th>Genre</th><th>Publication Year</th>
    </tr>
    <?php foreach ($books as $book): ?>
    <tr>
        <td><?= $book['id'] ?></td>
        <td><?= htmlspecialchars($book['title']) ?></td>
        <td><?= htmlspecialchars($book['author']) ?></td>
        <td><?= htmlspecialchars($book['genre']) ?></td>
        <td><?= htmlspecialchars($book['pub_year']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No books found.</p>
<?php endif; ?>

</body>
</html>
