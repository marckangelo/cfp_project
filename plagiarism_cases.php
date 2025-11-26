<?php
session_start();
require 'db.php';
include 'header.php';

// ================== ONLY ALLOW LOGGED-IN MEMBER ==================
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = (int) $_SESSION['member_id'];
$is_admin  = isset($_SESSION['admin_id']); // Optional: if you later wire real admin login

// ================== SHOW STATUS MESSAGES (OPEN CASE, ETC.) ==================
if (isset($_SESSION['plagiarism_case_success'])) {
    echo '<div style="color: green;">' . $_SESSION['plagiarism_case_success'] . '</div>';
    unset($_SESSION['plagiarism_case_success']);
}

if (isset($_SESSION['plagiarism_case_error'])) {
    echo '<div style="color: red;">' . $_SESSION['plagiarism_case_error'] . '</div>';
    unset($_SESSION['plagiarism_case_error']);
}

// ================== LOAD COMMITTEES FOR THIS MEMBER ==================
$member_committees = array();

$sql_committees = "
    SELECT c.committee_id,
           c.name,
           c.scope,
           cm.role,
           cm.status
    FROM committee_membership cm
    JOIN committee c
        ON cm.committee_id = c.committee_id
    WHERE cm.member_id = $member_id
      AND cm.status = 'active'
";

$result_committees = mysqli_query($conn, $sql_committees);

if ($result_committees) {
    while ($row = mysqli_fetch_assoc($result_committees)) {
        $member_committees[] = $row;
    }
}

// ================== LOAD PLAGIARISM CASES (ADMIN = ALL, COMMITTEE = OWN ONLY) ==================
$result_cases = null;

if ($is_admin) {

    // Admins can see all plagiarism cases across all committees
    $sql_cases = "
        SELECT pc.case_id,
               pc.text_id,
               pc.committee_id,
               pc.opened_date,
               pc.status,
               pc.resolution,
               pc.closed_date,
               t.title AS text_title,
               c.name AS committee_name,
               c.scope AS committee_scope
        FROM plagiarism_case pc
        JOIN text t
            ON pc.text_id = t.text_id
        JOIN committee c
            ON pc.committee_id = c.committee_id
        ORDER BY pc.opened_date DESC, pc.case_id DESC
    ";

    $result_cases = mysqli_query($conn, $sql_cases);

} else if (count($member_committees) > 0) {

    // Committee members see cases for their own committees only
    $committee_ids = array();
    foreach ($member_committees as $mc) {
        $committee_ids[] = (int) $mc['committee_id'];
    }

    // Build IN (...) list
    $committee_ids_list = implode(',', $committee_ids);

    $sql_cases = "
        SELECT pc.case_id,
               pc.text_id,
               pc.committee_id,
               pc.opened_date,
               pc.status,
               pc.resolution,
               pc.closed_date,
               t.title AS text_title,
               c.name AS committee_name,
               c.scope AS committee_scope
        FROM plagiarism_case pc
        JOIN text t
            ON pc.text_id = t.text_id
        JOIN committee c
            ON pc.committee_id = c.committee_id
        WHERE pc.committee_id IN ($committee_ids_list)
        ORDER BY pc.opened_date DESC, pc.case_id DESC
    ";

    $result_cases = mysqli_query($conn, $sql_cases);
}

?>

<h2>Plagiarism Cases</h2>

<?php
// Simple info for committee members
if (!$is_admin && count($member_committees) > 0) {

    echo '<p>You are currently an active member of the following committees:</p>';

    echo '
        <ul>
    ';

    foreach ($member_committees as $mc) {
        echo '<li>'
            . htmlspecialchars($mc['name'])
            . ' (scope: ' . htmlspecialchars($mc['scope'])
            . ', role: ' . htmlspecialchars($mc['role'])
            . ')</li>';
    }

    echo '</ul>';
}

// If no committees and not admin → you basically won’t see any cases
if (!$is_admin && count($member_committees) === 0) {
    echo '<p>You are not an active member of any plagiarism/appeals committee. '
       . 'If you believe a text is plagiarized, go to the item page and use the '
       . '"Report for Plagiarism" button so a committee can review it.</p>';
}

// ================== DISPLAY CASES TABLE ==================
if ($result_cases && mysqli_num_rows($result_cases) > 0) {

    echo '
        <h4>List of Plagiarism Cases</h4>

        <table border="1">
            <tr>
                <th>Case ID</th>
                <th>Text Title</th>
                <th>Committee</th>
                <th>Scope</th>
                <th>Opened Date</th>
                <th>Status</th>
                <th>Resolution</th>
                <th>Closed Date</th>
                <th>Action</th>
            </tr>
    ';

    while ($row = mysqli_fetch_assoc($result_cases)) {

        $case_id = (int) $row['case_id'];
        $text_title = $row['text_title'];
        $committee_name = $row['committee_name'];
        $committee_scope = $row['committee_scope'];
        $opened_date = $row['opened_date'];
        $status = $row['status'];
        $resolution = $row['resolution'];
        $closed_date = $row['closed_date'];

        // Handle possible NULL resolution/closed_date for display
        if ($resolution === null || $resolution === '') {
            $resolution = '-';
        }
        if ($closed_date === null || $closed_date === '') {
            $closed_date = '-';
        }

        echo '
            <tr>
                <td>' . $case_id . '</td>
                <td>' . htmlspecialchars($text_title) . '</td>
                <td>' . htmlspecialchars($committee_name) . '</td>
                <td>' . htmlspecialchars($committee_scope) . '</td>
                <td>' . htmlspecialchars($opened_date) . '</td>
                <td>' . htmlspecialchars($status) . '</td>
                <td>' . htmlspecialchars($resolution) . '</td>
                <td>' . htmlspecialchars($closed_date) . '</td>
                <td>
                    <form method="get" action="plagiarism_case_view.php">
                        <input type="hidden" name="case_id" value="' . $case_id . '">
                        <button type="submit">View / Manage</button>
                    </form>
                </td>
            </tr>
        ';
    }

    echo '</table>';

} else {

    // If logged in as admin or committee member but query returned no rows
    if ($is_admin || count($member_committees) > 0) {
        echo '<p>No plagiarism cases found for your role/committees.</p>';
    }
}

include 'footer.php';
?>
