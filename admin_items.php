<?php
session_start();
require 'db.php';
include 'header.php';

// ================== ENFORCING ONLY ADMINS WITH 'content' ROLE (OR 'super') ==================

if (empty($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

$admin_role = $_SESSION['admin_role'] ?? null;

if ($admin_role !== 'super' && $admin_role !== 'content') {
    echo "<p>You do not have permission to manage items.</p>";
    include 'footer.php';
    exit;
}

$admin_id = isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : 0;

// ================== PROCESS MODERATOR ACTIONS ON TEXT_VERSION (APPROVE or REJECT) ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['version_id']) &&
    isset($_POST['version_action'])) {

    $version_id     = (int) $_POST['version_id'];
    $version_action = $_POST['version_action'];
    $new_v_status   = '';

    if ($version_action === 'approve') {
        $new_v_status = 'approved';
    } elseif ($version_action === 'reject') {
        $new_v_status = 'rejected';
    }

    if ($new_v_status !== '' && $version_id > 0 && $admin_id > 0) {

        $new_v_status_sql = mysqli_real_escape_string($conn, $new_v_status);
        $today            = date('Y-m-d');

        $sql_update_version = "
            UPDATE text_version
            SET status      = '$new_v_status_sql',
                review_date = '$today',
                moderator_id = $admin_id
            WHERE version_id = $version_id
        ";

        $result_update_version = mysqli_query($conn, $sql_update_version);

        if ($result_update_version) {
            $_SESSION['admin_version_success'] = "Version #$version_id has been $new_v_status.";
        } else {
            $_SESSION['admin_version_error'] = "Failed to update version status.";
        }
    }

    header("Location: admin_items.php");
    exit;
}

// ================== PROCESS ADMIN ACTIONS ON ITEMS/TEXTS (STATUS CHANGES) ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['text_id']) &&
    isset($_POST['item_action']) &&
    !isset($_POST['version_id']) // make sure this branch is only for text status, not version actions
) {

    $text_id     = (int) $_POST['text_id'];
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
        // In schema, "archived" status represents blacklisted/removed texts
        $new_status = 'archived';
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

// ================== ONLY ALLOW LOGGED-IN MEMBER (SHOULD BE TRUE FOR ANY ADMIN) ==================
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

if (isset($_SESSION['admin_version_success'])) {
    echo '<div style="color: green;">' . $_SESSION['admin_version_success'] . '</div>';
    unset($_SESSION['admin_version_success']);
}

if (isset($_SESSION['admin_version_error'])) {
    echo '<div style="color: red;">' . $_SESSION['admin_version_error'] . '</div>';
    unset($_SESSION['admin_version_error']);
}

// ================== DISPLAY TEXT TABLES FOR A GIVEN STATUS ==================
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
                // Admin can either publish directly or archive/blacklist.
                echo '
                        <form method="post" action="admin_items.php" style="display:inline;">
                            <input type="hidden" name="text_id" value="' . $text_id . '">
                            <button type="submit" name="item_action" value="publish">Publish</button>
                        </form>

                        <form method="post" action="admin_items.php" style="display:inline;">
                            <input type="hidden" name="text_id" value="' . $text_id . '">
                            <button type="submit" name="item_action" value="blacklist">Archive / Blacklist</button>
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
                // Fallback for any other status (e.g., archived = blacklisted)
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

// ================== DISPLAY TEXTS THAT ARE PENDING VERSIONS TABLE ==================
function display_pending_versions($conn) {

    $sql_pending = "
        SELECT 
            tv.version_id,
            tv.text_id,
            tv.submitted_date,
            tv.change_summary,
            tv.status,
            t.title,
            t.status AS text_status,
            a.orcid,
            m.name AS author_name
        FROM text_version tv
        JOIN text t
            ON tv.text_id = t.text_id
        JOIN author a
            ON t.author_orcid = a.orcid
        JOIN member m
            ON a.member_id = m.member_id
        WHERE tv.status = 'pending'
        ORDER BY tv.submitted_date DESC, tv.version_id DESC
    ";

    $result_pending = mysqli_query($conn, $sql_pending);

    echo '<h3>Pending Versions for Moderation</h3>';

    if ($result_pending && mysqli_num_rows($result_pending) > 0) {

        echo '
            <table border="1">
                <tr>
                    <th>Version ID</th>
                    <th>Text Title</th>
                    <th>Author</th>
                    <th>Submitted Date</th>
                    <th>Current Text Status</th>
                    <th>Change Summary</th>
                    <th>Action</th>
                </tr>
        ';

        while ($row = mysqli_fetch_assoc($result_pending)) {

            $version_id  = (int)$row['version_id'];
            $text_id     = (int)$row['text_id'];

            echo '
                <tr>
                    <td>' . $version_id . '</td>
                    <td>' . htmlspecialchars($row['title']) . '</td>
                    <td>' . htmlspecialchars($row['author_name']) . '</td>
                    <td>' . htmlspecialchars($row['submitted_date']) . '</td>
                    <td>' . htmlspecialchars($row['text_status']) . '</td>
                    <td>' . htmlspecialchars($row['change_summary']) . '</td>
                    <td>
                        <form method="post" action="admin_items.php" style="display:inline;">
                            <input type="hidden" name="version_id" value="' . $version_id . '">
                            <button type="submit" name="version_action" value="approve">Approve</button>
                        </form>

                        <form method="post" action="admin_items.php" style="display:inline;">
                            <input type="hidden" name="version_id" value="' . $version_id . '">
                            <button type="submit" name="version_action" value="reject">Reject</button>
                        </form>
                    </td>
                </tr>
            ';
        }

        echo '</table><br>';

    } else {
        echo '<p>No pending versions at the moment.</p>';
    }
}

?>

<h2>Admin - Items</h2>
<p>List and manage items by status, and review pending versions submitted by authors.</p>

<?php
// First: show pending versions for moderation
display_pending_versions($conn);

// Then: show text tables per status (overall lifecycle)
display_items_table($conn, 'under_review', 'Texts - Under Review');
display_items_table($conn, 'draft', 'Texts - Draft');
display_items_table($conn, 'published', 'Texts - Published');
display_items_table($conn, 'archived', 'Texts - Archived / Blacklisted');

include 'footer.php';
?>
