<?php
session_start();
require 'db.php';
include 'header.php';

echo '<div class="account-container">';
echo '<h2 class="account-title">My Account</h2>
      <p class="account-intro">TODO: Show member details, download and donation history.</p>
     ';

// Show download success message, if any
if (isset($_SESSION['download_success'])) {
    echo '<div class="flash-success">' . htmlspecialchars($_SESSION['download_success']) . '</div>';
    unset($_SESSION['download_success']);
}

// Show download error message, if any
if (isset($_SESSION['download_failure'])) {
    echo '<div class="flash-error">' . htmlspecialchars($_SESSION['download_failure']) . '</div>';
    unset($_SESSION['download_failure']);
}

// Extract member_id of member logged in
$member_id  = $_SESSION['member_id'];
$admin_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : '';

// Checking if signed in
if (isset($_SESSION['member_id'])) {

    // ================ DISPLAY MEMBER DETAILS =================
    // Join author to fetch bio & orcid (if this member is an author)
    $sql_member_details = "SELECT m.*, a.bio, a.orcid AS author_orcid
                           FROM member m
                           LEFT JOIN author a ON m.member_id = a.member_id
                           WHERE m.member_id = $member_id";

    // Run the query
    $result_member_details = mysqli_query($conn, $sql_member_details);

    // Fetch the data
    $row = mysqli_fetch_assoc($result_member_details);

    // Is this member an author?
    $is_author = (!empty($row['bio']) || !empty($row['author_orcid']));

    // Determine download ability status based on donations in last year
    $sql_recent_donation = "
        SELECT COUNT(*) AS donation_count
        FROM donation
        WHERE member_id = $member_id
          AND date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
    ";
    $result_recent_donation = mysqli_query($conn, $sql_recent_donation);
    $row_donation = mysqli_fetch_assoc($result_recent_donation);
    $has_recent_donation = ($row_donation && $row_donation['donation_count'] > 0);

    if ($has_recent_donation) {
        $download_limit_text = "1 download per day (you donated in the last 12 months)";
    } else {
        $download_limit_text = "1 download per week (no donation in the last 12 months)";
    }

    // Table header
    echo '
    <div class="account-section">
        <h4 class="centered-title">Member Details</h4>

        <table border="1">
            <tr>
                <th>Name</th>
                <th>organization</th>
                <th>Pseudonym</th>
                <th>Address</th>
                <th>Primary Email</th>
                <th>Recovery Email</th>';

    // Only show Bio / ORCID columns if this member is an author
    if ($is_author) {
        echo '
                <th>Bio</th>
                <th>ORCID</th>';
    }

    echo '
                <th>Download Limit</th>';

    // If this member is an admin, show an extra column for the role
    if (!empty($admin_role)) {
        echo '
                <th>Admin Role</th>';
    }

    echo '
            </tr>
    ';

    // Table row
    echo '
            <tr>
                <td>' . htmlspecialchars($row['name']) . '</td>
                <td>' . htmlspecialchars($row['organization']) . '</td>
                <td>' . htmlspecialchars($row['pseudonym']) . '</td>
                <td>' .
                    htmlspecialchars($row['street']) . ', ' .
                    htmlspecialchars($row['city']) . ', ' .
                    htmlspecialchars($row['country']) . ', ' .
                    htmlspecialchars($row['postal_code']) .
                '</td>
                <td>' . htmlspecialchars($row['primary_email']) . '</td>
                <td>' . htmlspecialchars($row['recovery_email']) . '</td>';

    // Only output Bio / ORCID cells if author
    if ($is_author) {
        echo '
                <td>' . htmlspecialchars($row['bio']) . '</td>
                <td>' . htmlspecialchars($row['author_orcid']) . '</td>';
    }

    echo '
                <td>' . htmlspecialchars($download_limit_text) . '</td>';

    // If admin, show the role value in the row
    if (!empty($admin_role)) {
        echo '
                <td>' . htmlspecialchars($admin_role) . '</td>';
    }

    echo '
            </tr>
        ';
    echo '</table>';

    echo '
        <div class="account-actions">
            <a href="edit_profile.php?member_id=' . $row['member_id'] . '">
                <button type="button">Edit Profile</button>
            </a>
        </div>
    </div>
    ';

    // ============= DISPLAY VERIFICATION MATRIX (4x4) ================
    $verification_matrix = $row['verification_matrix'];
    $matrix_expiry_date  = $row['matrix_expiry_date'];

    if (!empty($verification_matrix) && strlen($verification_matrix) == 16) {

        /*
            Example: 16-char verification_matrix string = 'AAAABBBBCCCCDDDD'

                    AAAA
                    BBBB
                    CCCC
                    DDDD
        */
        $verification_matrix_2d = array();
        for ($r = 0; $r < 4; $r++) {
            $verification_matrix_2d[$r] = array();
            for ($c = 0; $c < 4; $c++) {
                $index = $r * 4 + $c; // position in the 16-char string
                $verification_matrix_2d[$r][$c] = $verification_matrix[$index];
            }
        }

        // Matrix card section
        echo '
        <div class="account-section matrix-wrapper">
            <h4 class="centered-title">Verification Matrix</h4>
            <p>Expiry date: ' . htmlspecialchars($matrix_expiry_date) . '</p>
            <table class="matrix-table">
        ';

        for ($r = 0; $r < 4; $r++) {
            echo '<tr>';
            for ($c = 0; $c < 4; $c++) {
                echo '<td>' . htmlspecialchars($verification_matrix_2d[$r][$c]) . '</td>';
            }
            echo '</tr>';
        }

        echo '
            </table><br>
            <button type="button" onclick="copyMyText(\'' . $verification_matrix . '\')">Copy to Clipboard</button>
        </div>
        ';
    }

    // ============= DISPLAY LIST OF DOWNLOADS ================

    $sql_download_details = "SELECT 
                                d.download_date,
                                t.text_id,
                                t.title,
                                t.topic,
                                t.version,
                                t.status,
                                a.orcid AS author_orcid,
                                m.name AS author_name
                             FROM download d
                             JOIN text t
                                ON d.text_id = t.text_id
                             JOIN author a
                                ON t.author_orcid = a.orcid
                             JOIN member m
                                ON a.member_id = m.member_id
                             WHERE d.member_id = $member_id
                             ORDER BY d.download_date DESC";

    // Run the query
    $result_download_details = mysqli_query($conn, $sql_download_details);

    // Table header
    echo '
    <div class="account-section">
        <h4 class="centered-title">Download Details</h4>

        <table border="1">
            <tr>
                <th>Download Date</th>
                <th>Title</th>
                <th>Topic</th>
                <th>Version</th>
                <th>Status</th>
                <th>Author</th>
                <th>Action</th>
            </tr>
    ';

    // Table rows
    while ($row_download = mysqli_fetch_assoc($result_download_details)) {
        echo '
        <tr>
            <td>' . htmlspecialchars($row_download['download_date']) . '</td>
            <td>' . htmlspecialchars($row_download['title']) . '</td>
            <td>' . htmlspecialchars($row_download['topic']) . '</td>
            <td>' . htmlspecialchars($row_download['version']) . '</td>
            <td>' . htmlspecialchars($row_download['status']) . '</td>
            <td>' . htmlspecialchars($row_download['author_name']) . '</td>
            <td>
                <a href="comment_add.php?text_id=' . $row_download['text_id'] . '">
                    <button type="button">Add a Comment</button>
                </a>
            </td>
        </tr>';
    }
    echo '</table>';
    echo '</div>';

    // ============= DISPLAY LIST OF DONATIONS ================

    $sql_donation_details = "SELECT d.date,
                                    d.amount,
                                    d.currency,
                                    d.payment_method,
                                    d.transaction_id,
                                    d.charity_pct,
                                    d.cfp_pct,
                                    d.author_pct,
                                    
                                    t.title AS text_title,
                                    t.topic AS text_topic,
                                    t.version AS text_version,
                                    
                                    c.name AS charity_name,
                                    c.country AS charity_country,
                                    c.description AS charity_description,
                                    c.mission AS charity_mission
                             FROM donation d
                             JOIN text t
                                 ON d.text_id = t.text_id
                             JOIN charity c
                                 ON d.charity_id = c.charity_id
                             WHERE d.member_id = $member_id
                             ORDER BY d.date DESC";

    // Run the query
    $result_donation_details = mysqli_query($conn, $sql_donation_details);

    // Table header
    echo '
    <div class="account-section">
        <h4 class="centered-title">Donation Details</h4>

        <table border="1">
            <tr>
                <th>Donation Date</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Payment Method</th>
                <th>TransactionID</th>
                <th>Charity (%)</th>
                <th>CFP (%)</th>
                <th>Author (%)</th>
                
                <th>Title</th>
                <th>Topic</th>
                <th>Text Version</th>

                <th>Charity Name</th>
                <th>Charity Country</th>
                <th>Charity Description</th>
                <th>Charity Mission</th>
            </tr>
    ';

    // Table rows
    while ($row_donation = mysqli_fetch_assoc($result_donation_details)) {
        echo '
            <tr>
                <td>' . htmlspecialchars($row_donation['date']) . '</td>
                <td>' . htmlspecialchars($row_donation['amount']) . '</td>
                <td>' . htmlspecialchars($row_donation['currency']) . '</td>
                <td>' . htmlspecialchars($row_donation['payment_method']) . '</td>
                <td>' . htmlspecialchars($row_donation['transaction_id']) . '</td>
                <td>' . htmlspecialchars($row_donation['charity_pct']) . '</td>
                <td>' . htmlspecialchars($row_donation['cfp_pct']) . '</td>
                <td>' . htmlspecialchars($row_donation['author_pct']) . '</td>
                
                <td>' . htmlspecialchars($row_donation['text_title']) . '</td>
                <td>' . htmlspecialchars($row_donation['text_topic']) . '</td>
                <td>' . htmlspecialchars($row_donation['text_version']) . '</td>
                
                <td>' . htmlspecialchars($row_donation['charity_name']) . '</td>
                <td>' . htmlspecialchars($row_donation['charity_country']) . '</td>
                <td>' . htmlspecialchars($row_donation['charity_description']) . '</td>
                <td>' . htmlspecialchars($row_donation['charity_mission']) . '</td>
            </tr>';
    }
    echo '</table>';
    echo '</div>';

    // ============= DISPLAY LIST OF COMMITTEES JOINED ================

    $sql_committee_details = "
                            SELECT 
                                c.name,
                                c.purpose,
                                c.scope,
                                c.formation_date,
                                c.member_count,
                                c.status AS committee_status,
                                cm.status AS membership_status
                            FROM committee_membership cm
                            JOIN committee c
                                ON cm.committee_id = c.committee_id
                            WHERE cm.member_id = $member_id
                            ";

    // Run the query
    $result_committee_details = mysqli_query($conn, $sql_committee_details);

    // Table header
    echo '
    <div class="account-section">
        <h4 class="centered-title">Committee(s) Joined Details</h4>

        <table border="1">
            <tr>
                <th>Name</th>
                <th>Purpose</th>
                <th>Scope</th>
                <th>Formation Date</th>
                <th>Committee Status</th>
                <th>Membership Status</th>
                <th># of Member</th>
            </tr>
    ';

    // Table rows
    while ($row_committee = mysqli_fetch_assoc($result_committee_details)) {
        echo '
            <tr>
                <td>' . htmlspecialchars($row_committee['name']) . '</td>
                <td>' . htmlspecialchars($row_committee['purpose']) . '</td>
                <td>' . htmlspecialchars($row_committee['scope']) . '</td>
                <td>' . htmlspecialchars($row_committee['formation_date']) . '</td>
                <td>' . htmlspecialchars($row_committee['committee_status']) . '</td>
                <td>' . htmlspecialchars($row_committee['membership_status']) . '</td>
                <td>' . htmlspecialchars($row_committee['member_count']) . '</td>
            </tr>';
    }
    echo '</table>';
    echo '</div>';

} else {
    header("Location: login.php");
    exit;
}

echo '</div>'; // close .account-container
?>

<script>
    // Function receives the text directly from the button clicked above
    function copyMyText(textToCopy) {
        navigator.clipboard.writeText(textToCopy);
        alert("Copied to clipboard successfully!");
    }
</script>

<?php include 'footer.php'; ?>