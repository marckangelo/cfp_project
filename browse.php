<?php
session_start();
require 'db.php';
include 'header.php';

$search = '';
$filter = '';
//gets search, filter and author
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

if (isset($_GET['filter'])) {
    $filter = $_GET['filter'];
}

if (isset($_GET['author'])) {
    $author = $_GET['author'];
}

//sql 
$query = "SELECT 
            t.text_id, 
            t.title, 
            t.upload_date, 
            t.avg_rating, 
            t.download_count, 
            a.orcid, 
            m.name
          FROM text t, member m, author a
          WHERE t.author_orcid = a.orcid 
            AND a.member_id = m.member_id
            AND t.status = 'published' ";
// Filter by author if provided
if ($search != '') {
    $searched_prompt = addslashes($search);
    $query .= " AND (t.title LIKE '%$searched_prompt%' 
                 OR m.name LIKE '%$searched_prompt%')";
}
// Apply sorting based on filter
// Most Downloaded
if ($filter == 'most_downloaded') {
    $query .= " ORDER BY t.download_count DESC";
} 
//highest rated
elseif ($filter == 'highest_rated') {
    $query .= " ORDER BY t.avg_rating DESC";
} 
//newest
elseif ($filter == 'newest') {
    $query .= " ORDER BY t.upload_date DESC";
}
//result
$result = mysqli_query($conn, $query);
?>

<h2 class="centered-title">Browse Items</h2>
<form method="GET" action="browse.php" class="centered-form">
    <input type="text" name="search" placeholder="Search by title or author" value="<?php echo htmlspecialchars($search); ?>">
    <select name="filter">
        <option value="">-- Select Filter --</option>
        <option value="most_downloaded" <?php if ($filter == 'most_downloaded') echo 'selected'; ?>>Most Downloaded</option>
        <option value="highest_rated" <?php if ($filter == 'highest_rated') echo 'selected'; ?>>Highest Rated</option>
        <option value="newest" <?php if ($filter == 'newest') echo 'selected'; ?>>Newest</option>
    </select>
    <button type="submit">Apply</button>
</form>

<table>
    <tr>
        <th>Title</th>
        <th>Author</th>
        <th>Upload Date</th>
        <th>Average Rating</th>
        <th>Download Count</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><a href="item.php?text_id=<?php echo $row['text_id']; ?>"><?php echo htmlspecialchars($row['title']); ?></a></td>
        <td><a href="author_info.php?orcid=<?php echo $row['orcid']; ?>"><?php echo htmlspecialchars($row['name']); ?></a></td>
        <td><?php echo htmlspecialchars($row['upload_date']); ?></td>
        <td><?php echo htmlspecialchars($row['avg_rating']); ?></td>
        <td><?php echo htmlspecialchars($row['download_count']); ?></td>
    </tr>
    <?php } ?>

<?php include 'footer.php'; ?>
