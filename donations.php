<?php
/*
- Marck Angelo GELI (40265711)
- Arshdeep SINGH (40286514)
- Muhammad Adnan SHAHZAD (40282531)
- Muhammad RAZA (40284058)
*/
/*
Contributor to this file:
- Arshdeep SINGH (40286514)
*/
session_start();
require 'db.php';
// include 'header.php';

// ================== ENFORCE PERMISSIONS (ADMIN OR FINANCE COMMITTEE MEMBER) ==================

// Must be logged in at least
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = (int) $_SESSION['member_id'];

$is_admin = (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true)
            || !empty($_SESSION['admin_id']);
$admin_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : null;

$authorized = false;

// Case 1: admin with proper role
if ($is_admin && ($admin_role === 'super' || $admin_role === 'financial')) {
    $authorized = true;
}

// Case 2: member who has joined a committee with scope = 'finance'
if (!$authorized) {

    $sql_finance_scope = "
        SELECT cm.membership_id
        FROM committee_membership cm
        JOIN committee c
            ON cm.committee_id = c.committee_id
        WHERE cm.member_id = $member_id
          AND cm.status = 'active'
          AND c.status = 'active'
          AND c.scope = 'finance'
        LIMIT 1
    ";

    $result_finance_scope = mysqli_query($conn, $sql_finance_scope);

    if ($result_finance_scope && mysqli_num_rows($result_finance_scope) > 0) {
        $authorized = true;
    }
}

// If still not authorized, block access
if (!$authorized) {
    echo "<p>You do not have permission to view donations.</p>";
    include 'footer.php';
    exit;
}

// ================== GET CHARITY ID FROM URL ==================

if (!isset($_GET['charity_id'])) {
    echo "<p style='color:red;'>No charity selected.</p>";
    include 'footer.php';
    exit;
}

$charity_id = (int) $_GET['charity_id'];

if ($charity_id <= 0) {
    echo "<p style='color:red;'>Invalid charity.</p>";
    include 'footer.php';
    exit;
}

// ================== LOAD CHARITY INFO ==================

$sql_charity = "
    SELECT name
    FROM charity
    WHERE charity_id = $charity_id
    LIMIT 1
";

$result_charity = mysqli_query($conn, $sql_charity);

if (!$result_charity || mysqli_num_rows($result_charity) === 0) {
    echo "<p style='color:red;'>Charity not found.</p>";
    include 'footer.php';
    exit;
}

$row_charity = mysqli_fetch_assoc($result_charity);
$charity_name = $row_charity['name'];

// ================== LOAD DONATIONS FOR THIS CHARITY ==================

$sql_donations = "
    SELECT
        d.donation_id,
        d.amount,
        d.date,
        d.currency,
        d.payment_method,
        d.transaction_id,
        d.charity_pct,
        d.cfp_pct,
        d.author_pct,
        m.name  AS member_name,
        t.title AS text_title
    FROM donation d
    JOIN member m
        ON d.member_id = m.member_id
    JOIN text t
        ON d.text_id = t.text_id
    WHERE d.charity_id = $charity_id
    ORDER BY d.date DESC, d.donation_id DESC
";

$result_donations = mysqli_query($conn, $sql_donations);
include 'header.php';
?>

<h2>Donations for Charity</h2>
<p>
    <strong>Charity:</strong> <?php echo htmlspecialchars($charity_name); ?>
    (ID: <?php echo $charity_id; ?>)
</p>

<?php
if ($result_donations && mysqli_num_rows($result_donations) > 0) {

    echo '
        <table border="1">
            <tr>
                <th>Donor Name</th>
                <th>Text Title</th>
                <th>Amount</th>
                <th>Currency</th>
                <th>Transaction ID</th>
                <th>Payment Method</th>
                <th>Date</th>
                <th>Charity %</th>
                <th>CFP %</th>
                <th>Author %</th>
            </tr>
    ';

    while ($row = mysqli_fetch_assoc($result_donations)) {

        echo '
            <tr>
                <td>' . htmlspecialchars($row['member_name']) . '</td>
                <td>' . htmlspecialchars($row['text_title']) . '</td>
                <td>' . htmlspecialchars($row['amount']) . '</td>
                <td>' . htmlspecialchars($row['currency']) . '</td>
                <td>' . htmlspecialchars($row['transaction_id']) . '</td>
                <td>' . htmlspecialchars($row['payment_method']) . '</td>
                <td>' . htmlspecialchars($row['date']) . '</td>
                <td>' . htmlspecialchars($row['charity_pct']) . '</td>
                <td>' . htmlspecialchars($row['cfp_pct']) . '</td>
                <td>' . htmlspecialchars($row['author_pct']) . '</td>
            </tr>
        ';
    }

    echo '</table><br>';

} else {
    echo '<p>No donations found for this charity.</p>';
}

include 'footer.php';
?>