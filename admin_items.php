<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: Admin-only items management (mark plagiarized/blacklisted, etc.)

// ================== PROCESS ADMIN ACTIONS ON ITEMS/TEXTS ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['text_id']) &&
    isset($_POST['item_action'])) {

    $text_id    = (int) $_POST['text_id'];
    $item_action = $_POST['item_action'];
    $new_status  = '';

    // Map actions to new status
    if ($item_action === 'publish') {
        $new_status = 'published';
    } else if ($item_action === 'draft') {
        $new_status = 'draft';
    } else if ($item_action === 'under_review') {
        $new_status = 'under_review';
    } else if ($item_action === 'blacklist') {
        $new_status = 'blacklisted';
    }

    if ($new_status !== '') {
        $new_status_sql = mysqli_real_escape_string($conn, $new_status);

        $sql_update_status = "
            UPDATE text
            SET status = '$new_status_sql'
            WHERE text_id = $text_id
        ";

        $result_update_status = mysqli_query($conn, $sql_update_status);

        if ($result_update_status) {
            $_SESSION['admin_item_success'] = "Item status has been updated successfully.";
        } else {
            $_SESSION['admin_item_error'] = "Failed to update item status.";
        }
    }

    header("Location: admin_items.php");
    exit;
}

// ================== ONLY ALLOW LOGGED-IN MEMBER *** FOR NOW *** (IDEALLY ADMIN) ==================
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// ================== SHOW STATUS MESSAGES ==================
if (isset($_SESSION['admin_item_success'])) {
    echo '<div style="color: green;">' . $_SESSION['admin_item_success'] . '</div>';
    unset($_SESSION['admin_item_success']);
}

if (isset($_SESSION['admin_item_error'])) {
    echo '<div style="color: red;">' . $_SESSION['admin_item_error'] . '</div>';
    unset($_SESSION['admin_item_error']);
}

// Helper function to display one table for a given status
function display_items_table($conn, $status_filter, $title_label) {

    $status_sql = mysqli_real_escape_string($conn, $status_filter);

    $sql_text_details = "
        SELECT *
        FROM text
        WHERE status = '$status_sql'
    ";

    $result_text_details = mysqli_query($conn, $sql_text_details);

    if ($result_text_details && mysqli_num_rows($result_text_details) > 0) {

        echo '
        <h4>' . htmlspecialchars($title_label) . '</h4>

            <table border="1">
                <tr>
                    <th>Title</th>
                    <th>Abstract</th>
                    <th>Topic</th>
                    <th>Version</th>
                    <th>Upload Date</th>
                    <th>Status</th>
                    <th>Download Count</th>
                    <th>Total Donations ($)</th>
                    <th>Average Rating</th>
                    <th>Admin Action</th>
                </tr>
        ';

        // Table rows
        while ($row = mysqli_fetch_assoc($result_text_details)) {

            $text_id = (int)$row['text_id'];

            echo '
                <tr>
                    <td>' . htmlspecialchars($row['title']) . '</td>
                    <td>' . htmlspecialchars($row['abstract']) . '</td>
                    <td>' . htmlspecialchars($row['topic']) . '</td>
                    <td>' . htmlspecialchars($row['version']) . '</td>
                    <td>' . htmlspecialchars($row['upload_date']) . '</td>
                    <td>' . htmlspecialchars($row['status']) . '</td> 
                    <td>' . htmlspecialchars($row['download_count']) . '</td> 
                    <td>' . htmlspecialchars($row['total_donations']) . '</td>
                    <td>' . htmlspecialchars($row['avg_rating']) . '</td>
                    <td>
            ';

            // Different actions depending on current status
            if ($status_filter === 'under_review') {
                echo '
                        <form method="post" action="admin_items.php" style="display:inline;">
                            <input type="hidden" name="text_id" value="' . $text_id . '">
                            <button type="submit" name="item_action" value="publish">Publish</button>
                        </form>

                        <form method="post" action="admin_items.php" style="display:inline;">
                            <input type="hidden" name="text_id" value="' . $text_id . '">
                            <button type="submit" name="item_action" value="blacklist">Blacklist</button>
                        </form>
                ';
            } else if ($status_filter === 'draft') {
                echo '
                        <form method="post" action="admin_items.php" style="display:inline;">
                            <input type="hidden" name="text_id" value="' . $text_id . '">
                            <button type="submit" name="item_action" value="under_review">Send to Review</button>
                        </form>

                        <form method="post" action="admin_items.php" style="display:inline;">
                            <input type="hidden" name="text_id" value="' . $text_id . '">
                            <button type="submit" name="item_action" value="publish">Publish</button>
                        </form>
                ';
            } else if ($status_filter === 'published') {
                echo '
                        <form method="post" action="admin_items.php" style="display:inline;">
                            <input type="hidden" name="text_id" value="' . $text_id . '">
                            <button type="submit" name="item_action" value="under_review">Send Back to Review</button>
                        </form>

                        <form method="post" action="admin_items.php" style="display:inline;">
                            <input type="hidden" name="text_id" value="' . $text_id . '">
                            <button type="submit" name="item_action" value="blacklist">Blacklist</button>
                        </form>
                ';
            } else {
                // Fallback for any other status (e.g., blacklisted)
                echo '
                        <em>No actions available.</em>
                ';
            }

            echo '
                    </td>
                </tr>
            ';
        }

        echo '</table><br>'; // Close this status table
    } else {
        echo '<h4>' . htmlspecialchars($title_label) . '</h4>';
        echo '<p>No items found for this status.</p>';
    }
}

?>

<h2>Admin - Items</h2>
<p>List and manage items by status (draft, under review, published).</p>

<?php
// Displaying tables for each status
display_items_table($conn, 'under_review', 'Texts - Under Review');
display_items_table($conn, 'draft', 'Texts - Draft');
display_items_table($conn, 'published', 'Texts - Published');

include 'footer.php';
?>
