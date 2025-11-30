<?php
session_start();
require 'db.php';

// search term
$search = '';

if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

$query_search = "SELECT DISTINCT t.topic, k.keyword, m.name FROM text t 
JOIN author a ON t.author_orcid = a.orcid
JOIN member m ON a.member_id = m.member_id
LEFT JOIN text_keyword k ON k.text_id = t.text_id
WHERE t.status = 'published' ";

if (!empty($search)) {
    $search_escaped = mysqli_real_escape_string($conn, $search);
    $query_search .= " AND (t.topic LIKE '%$search_escaped%' OR k.keyword LIKE '%$search_escaped%' OR m.name LIKE '%$search_escaped%')";
}
$result_search = mysqli_query($conn, $query_search);

// popular items (most downloaded)
$query_popular_items = "SELECT t.title, count(*) as download_count FROM download d, text t 
WHERE d.text_id = t.text_id GROUP BY d.text_id ORDER BY download_count DESC LIMIT 5";
$query_result_popular_items = mysqli_query($conn, $query_popular_items);

// new items (recent uploads)
$query_new_items = "SELECT t.title, t.upload_date FROM text t WHERE t.status != 'draft' ORDER BY t.upload_date DESC LIMIT 5 ";
$query_result_new_items = mysqli_query($conn, $query_new_items);

// all authors
$query_all_authors = "SELECT m.name, a.bio FROM author a, member m WHERE a.member_id = m.member_id";
$query_result_all_authors = mysqli_query($conn, $query_all_authors);

// top 5 popular topics
$query_topics = "SELECT topic, count(*) as topic_count FROM text WHERE topic IS NOT NULL AND topic <> '' GROUP BY topic ORDER BY topic_count DESC";
$query_result_topics = mysqli_query($conn, $query_topics);

include 'header.php';
?>

<h2>Welcome to the CFP Repository</h2>

<h3 class="centered-title">Search</h3>
<form method="GET" action="index.php" class="centered-form">
    <input type="text" name="search" placeholder="Search by topic, keyword or author" value="<?php echo htmlspecialchars($search); ?>" style="width: 300px;">
    <button type="submit">Search</button>
</form>
<?php if (!empty($search)) { ?>
    <h4>Search Results for "<?php echo htmlspecialchars($search); ?>"</h4>
    <?php if (mysqli_num_rows($result_search) > 0) { ?>
        <table class="table-centered">
            <tr>
                <th>Topic</th>
                <th>Keyword</th>
                <th>Author Name</th>
            </tr>
            <?php while ($row = mysqli_fetch_assoc($result_search)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['topic']); ?></td>
                <td><?php echo htmlspecialchars($row['keyword']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
            </tr>
            <?php } ?>
        </table>
    <?php } else { ?>
        <p>No results found.</p>
    <?php } ?>
<?php } ?>

<h3>Popular Items</h3>
<table class="table-centered">
    <tr>
        <th>Title</th>
        <th>Download Count</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($query_result_popular_items)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['title']); ?></td>
        <td><?php echo htmlspecialchars($row['download_count']); ?></td>
    </tr>
    <?php } ?>
</table>

<h3>New Additions</h3>
<table class="table-centered">
    <tr>
        <th>Title</th>
        <th>Upload Date</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($query_result_new_items)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['title']); ?></td>
        <td><?php echo htmlspecialchars($row['upload_date']); ?></td>
    </tr>
    <?php } ?>
</table>

<h3>All Authors</h3>
<table class="table-centered">
    <tr>
        <th>Author Name</th>
        <th>Bio</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($query_result_all_authors)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['bio']); ?></td>
    </tr>
    <?php } ?>
</table>

<h3>Top 5 Popular Topics</h3>
<table class="table-centered">
    <tr>
        <th>Topic</th>
        <th>Count</th>
    </tr>
    <?php 
    $count = 0;
    while ($row = mysqli_fetch_assoc($query_result_topics)) { 
        if ($count >= 5) break;
        $count++;
    ?>
    <tr>
        <td><?php echo htmlspecialchars($row['topic']); ?></td>
        <td><?php echo htmlspecialchars($row['topic_count']); ?></td>
    </tr>
    <?php } ?>
</table>

<h3>View our Statistics</h3>
<p>
    <a href="statistics.php">Click here to view detailed statistics about downloads and usage.</a>
</p>

<?php include 'footer.php'; ?>