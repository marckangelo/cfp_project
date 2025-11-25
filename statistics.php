<?php
session_start();
require 'db.php';
include 'header.php';
//top 5 most downloaded titles
$query_top_titles = "SELECT title, count(*) as download_count FROM download d, text t 
WHERE d.text_id = t.text_id GROUP BY d.text_id ORDER BY download_count DESC LIMIT 5";
$query_result_top_titles = mysqli_query($conn, $query_top_titles);


//top 5 most downloaded authors
$query_top_authors = "SELECT a.name, count(*) as download_count FROM download d, authors a, text t 
WHERE d.text_id = t.text_id AND t.author_orcid = a.orcid GROUP BY a.name ORDER BY download_count DESC LIMIT 5";
$query_result_top_authors = mysqli_query($conn, $query_top_authors);

//annual usage statistics
$query_annual_usage = "SELECT YEAR(download_date) as year, count(*) as download_count FROM download  GROUP BY year ORDER BY year DESC";
$query_result_annual_usage = mysqli_query($conn, $query_annual_usage);

//annual access by country
$query_access_by_country = "SELECT country, count(*) as download_count FROM download GROUP BY country ORDER BY download_count DESC LIMIT 5";
$query_result_access_by_country = mysqli_query($conn, $query_access_by_country);

//growth over time (uploads)
$query_growth_over_time = "SELECT YEAR(upload_date) as year, count(*) as upload_count FROM titles GROUP BY year ORDER BY year DESC";
$query_result_growth_over_time = mysqli_query($conn, $query_growth_over_time);

//annual downloads for a specific author
$query_annual_for_author = "SELECT YEAR(d.download_date), a.name, a.orcid, count(*) as download_count FROM download d, text t, authors a 
WHERE d.text_id = t.text_id AND t.author_orcid = a.orcid GROUP BY year, a.orcid ORDER BY year DESC, download_count DESC";
$query_result_annual_for_author = mysqli_query($conn, $query_annual_for_author);

?>
<h2>Statistics</h2>
<h3>Top 5 Most Downloaded Titles</h3>
<table border="1">
    <tr>
        <th>Title</th>
        <th>Download Count</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($query_result_top_titles)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['title']); ?></td>
        <td><?php echo htmlspecialchars($row['download_count']); ?></td>
    </tr>
    <?php } ?>
</table>

<h3>Top 5 Most Downloaded Authors</h3>
<table border="1">
    <tr>
        <th>Author Name</th>
        <th>Download Count</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($query_result_top_authors)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['download_count']); ?></td>
    </tr>
    <?php } ?>
</table>

<h3>Annual Usage Statistics</h3>
<table border="1">
    <tr>
        <th>Year</th>
        <th>Download Count</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($query_result_annual_usage)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['year']); ?></td>
        <td><?php echo htmlspecialchars($row['download_count']); ?></td>
    </tr>
    <?php } ?>
</table>

<h3>Annual Access by Country (Top 5)</h3>
<table border="1">
    <tr>
        <th>Country</th>
        <th>Download Count</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($query_result_access_by_country)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['country']); ?></td>
        <td><?php echo htmlspecialchars($row['download_count']); ?></td>
    </tr>
    <?php } ?>
</table>

<h3>Growth Over Time (Uploads)</h3>
<table border="1">
    <tr>
        <th>Year</th>
        <th>Upload Count</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($query_result_growth_over_time)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['year']); ?></td>
        <td><?php echo htmlspecialchars($row['upload_count']); ?></td>
    </tr>
    <?php } ?>
</table>

<h3>Annual Downloads for a Specific Author </h3>
<table border="1">
    <tr>
        <th>Year</th>
        <th>Author Name</th>
        <th>ORCID</th>
        <th>Download Count</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($query_result_annual_for_author)) { ?>
    <tr>
        <td><?php echo htmlspecialchars($row['year']); ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['orcid']); ?></td>
        <td><?php echo htmlspecialchars($row['download_count']); ?></td>
    </tr>
    <?php } ?>
</table>
<?php include 'footer.php'; ?>
