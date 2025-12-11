<?php
/*
- Marck Angelo GELI (40265711)
- Arshdeep SINGH (40286514)
- Muhammad Adnan SHAHZAD (40282531)
- Muhammad RAZA (40284058)
*/

/*
Contributor to this file:
- Marck Angelo GELI (40265711)
*/
session_start();
require 'db.php';
include 'header.php';

// Restrict to author users
if (!isset($_SESSION['orcid'])) {
    echo "<p>You must be an author to access this page.</p>";
    include 'footer.php';
    exit;
}

$author_orcid = mysqli_real_escape_string($conn, $_SESSION['orcid']);

// Get all texts for this author
$sql_author_texts = "
    SELECT 
        t.text_id,
        t.title,
        t.topic,
        t.version,
        t.upload_date,
        t.status
    FROM text t
    WHERE t.author_orcid = '$author_orcid'
    ORDER BY t.upload_date DESC
";

$result_author_texts = mysqli_query($conn, $sql_author_texts);
?>

<div class="dashboard-container">
    <h2 class="centered-title">Author Dashboard</h2>

    <ul class="dashboard-links">
        <li><a href="author_item_new.php">Add a new item</a></li>
    </ul>

    <h3>My Texts</h3>

    <?php
    if ($result_author_texts && mysqli_num_rows($result_author_texts) > 0) {

        echo '
            <table>
                <tr>
                    <th>Title</th>
                    <th>Topic</th>
                    <th>Version</th>
                    <th>Upload Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
        ';

        while ($row = mysqli_fetch_assoc($result_author_texts)) {

            $text_id = (int)$row['text_id'];

            echo '
                <tr>
                    <td>' . htmlspecialchars($row['title']) . '</td>
                    <td>' . htmlspecialchars($row['topic']) . '</td>
                    <td>' . htmlspecialchars($row['version']) . '</td>
                    <td>' . htmlspecialchars($row['upload_date']) . '</td>
                    <td>' . htmlspecialchars($row['status']) . '</td>
                    <td>
                        <form method="post" action="author_item_edit.php" style="display:inline;">
                            <input type="hidden" name="text_id" value="' . $text_id . '">
                            <button type="submit">Edit</button>
                        </form>
                    </td>
                </tr>
            ';
        }

        echo '</table>';

    } else {
        echo '<p>You have not uploaded any texts yet.</p>';
    }
    ?>
</div>

<?php include 'footer.php'; ?>
