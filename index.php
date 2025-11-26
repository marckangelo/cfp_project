<?php
session_start();
require 'db.php';
include 'header.php';
//search term
$search = '';

//popular items (most downloaded)
$query_popular_items = "SELECT t.title, count(*) as download_count FROM download d, text t 
WHERE d.text_id = t.text_id GROUP BY d.text_id ORDER BY download_count DESC LIMIT 5";
$query_result_popular_items = mysqli_query($conn, $query_popular_items);

//new items (recent uploads)
$query_new_items = "SELECT t.title, t.upload_date FROM text t ORDER BY t.upload_date DESC LIMIT 5";
$query_result_new_items = mysqli_query($conn, $query_new_items);

//all authors
$query_all_authors = "SELECT m.name FROM author a, member m WHERE a.member_id = m.member_id";
$query_result_all_authors = mysqli_query($conn, $query_all_authors);

//keywords and topics for browsing
$query_keywords = "SELECT DISTINCT keyword FROM text_keyword";
$query_result_keywords = mysqli_query($conn, $query_keywords);

$query_topics = "SELECT DISTINCT topic FROM text";
$query_result_topics = mysqli_query($conn, $query_topics);

?>
<h2>Welcome to the CFP Repository</h2>
<h3>Who are we and what is our mission?</h3>
<p>
    CopyForward Publishing (CFP) is a non-profit, online repository that offers
    free access to academic and educational texts. It aims to reduce the high
    cost of textbooks by providing open, legal distribution of works while
    ensuring that authors are recognized and can receive voluntary financial
    support. Donations associated with downloaded items help sustain CFP’s
    operations and support selected charities and authors.
</p>

<h3>Search</h3>
<form method="GET" action="index.php">
    <input type="text" name="search" placeholder="Search by topic, author or keyword" value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Search</button>
</form>

<h3>Popular Items</h3>
<table border="1">
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
<table border="1">
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
<table border="1">
    <tr>
        <th>Author Name</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($query_result_all_authors)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
    </tr>
    <?php } ?>
</table>

<h3>Browse by Keyword</h3>
<ul>
    <?php while ($row = mysqli_fetch_assoc($query_result_keywords)) { ?>
    <li><?php echo htmlspecialchars($row['keyword']); ?></li>
    <?php } ?>
</ul>

<h3>Browse by Topics</h3>
<ul>
    <?php while ($row = mysqli_fetch_assoc($query_result_topics)) { ?>
    <li><?php echo htmlspecialchars($row['topic']); ?></li>
    <?php } ?>
</ul>


<h3>View our Statistics</h3>
<p>
    <a href="statistics.php">Click here to view detailed statistics about downloads and usage.</a>
</p>


<?php include 'footer.php'; ?>
