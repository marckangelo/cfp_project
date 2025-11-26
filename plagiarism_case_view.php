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
$is_admin  = isset($_SESSION['admin_id']);

$error_msg = "";

// ================== GET CASE ID FROM URL ==================
if (!isset($_GET['case_id'])) {
    header("Location: plagiarism_cases.php");
    exit;
}

$case_id = (int) $_GET['case_id'];

if ($case_id <= 0) {
    $error_msg = "Invalid plagiarism case.";
}

// ================== LOAD CASE DETAILS ==================
$case_row = null;

if ($error_msg === "") {

    $sql_case = "
        SELECT pc.case_id,
               pc.text_id,
               pc.committee_id,
               pc.opened_date,
               pc.description,
               pc.status,
               pc.resolution,
               pc.closed_date,
               t.title AS text_title,
               t.author_orcid AS text_author_orcid,
               c.name AS committee_name,
               c.scope AS committee_scope
        FROM plagiarism_case pc
        JOIN text t
            ON pc.text_id = t.text_id
        JOIN committee c
            ON pc.committee_id = c.committee_id
        WHERE pc.case_id = $case_id
        LIMIT 1
    ";

    $result_case = mysqli_query($conn, $sql_case);

    if ($result_case && mysqli_num_rows($result_case) > 0) {
        $case_row = mysqli_fetch_assoc($result_case);
    } else {
        $error_msg = "Plagiarism case not found.";
    }
}

// ================== PERMISSION CHECK (ADMIN OR COMMITTEE MEMBER ONLY) ==================
$authorized = false;

if ($error_msg === "" && $case_row !== null) {

    $committee_id_for_case = (int) $case_row['committee_id'];

    if ($is_admin) {
        // Admins can see all cases
        $authorized = true;
    } else {
        // Check if this member is an active member of the committee for this case
        $sql_check_member = "
            SELECT cm.membership_id
            FROM committee_membership cm
            WHERE cm.member_id = $member_id
              AND cm.committee_id = $committee_id_for_case
              AND cm.status = 'active'
            LIMIT 1
        ";

        $result_check_member = mysqli_query($conn, $sql_check_member);

        if ($result_check_member && mysqli_num_rows($result_check_member) > 0) {
            $authorized = true;
        }
    }

    if (!$authorized) {
        $error_msg = "You are not authorized to view this plagiarism case.";
    }
}

// ================== LOAD VOTES FOR THIS CASE (FOR DISPLAY) ==================
$votes = array();
$vote_counts = array(
    'plagiarized' => 0,
    'not_plagiarized'=> 0,
    'abstain' => 0
);

if ($error_msg === "" && $authorized) {

    $sql_votes = "
        SELECT v.vote_id,
               v.vote,
               v.date AS vote_date,
               v.rationale,
               m.name AS member_name
        FROM vote v
        JOIN member m
            ON v.member_id = m.member_id
        WHERE v.case_id = $case_id
        ORDER BY v.date ASC
    ";

    $result_votes = mysqli_query($conn, $sql_votes);

    if ($result_votes) {
        while ($row_vote = mysqli_fetch_assoc($result_votes)) {
            $votes[] = $row_vote;

            // Count votes by type
            $vote_type = $row_vote['vote'];
            if (isset($vote_counts[$vote_type])) {
                $vote_counts[$vote_type]++;
            }
        }
    }
}

?>

<h2>Plagiarism Case Details</h2>

<?php
// Error message (if any)
if ($error_msg !== "") {
    echo '<div style="color: red;">' . htmlspecialchars($error_msg) . '</div>';
    include 'footer.php';
    exit;
}
?>

<p>
    <a href="plagiarism_cases.php"><-- Back to Plagiarism Cases</a>
</p>

<?php
// Basic case info
$case_id = (int) $case_row['case_id'];
$text_id = (int) $case_row['text_id'];
$text_title = $case_row['text_title'];
$committee_name = $case_row['committee_name'];
$committee_scope  = $case_row['committee_scope'];
$opened_date = $case_row['opened_date'];
$description = $case_row['description'];
$status = $case_row['status'];
$resolution = $case_row['resolution'];
$closed_date = $case_row['closed_date'];

// For display if null/empty
if ($resolution === null || $resolution === '') {
    $resolution = '-';
}
if ($closed_date === null || $closed_date === '') {
    $closed_date = '-';
}
?>

<table border="1">
    <tr>
        <th>Case ID</th>
        <td><?php echo $case_id; ?></td>
    </tr>
    <tr>
        <th>Text</th>
        <td><?php echo htmlspecialchars($text_title); ?> (ID: <?php echo $text_id; ?>)</td>
    </tr>
    <tr>
        <th>Committee</th>
        <td><?php echo htmlspecialchars($committee_name); ?> (scope: <?php echo htmlspecialchars($committee_scope); ?>)</td>
    </tr>
    <tr>
        <th>Opened Date</th>
        <td><?php echo htmlspecialchars($opened_date); ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><?php echo htmlspecialchars($status); ?></td>
    </tr>
    <tr>
        <th>Resolution</th>
        <td><?php echo htmlspecialchars($resolution); ?></td>
    </tr>
    <tr>
        <th>Closed Date</th>
        <td><?php echo htmlspecialchars($closed_date); ?></td>
    </tr>
    <tr>
        <th>Description</th>
        <td><?php echo nl2br(htmlspecialchars($description)); ?></td>
    </tr>
</table>

<br>

<h3>Votes</h3>

<?php
$total_votes = $vote_counts['plagiarized'] + $vote_counts['not_plagiarized'] + $vote_counts['abstain'];

if ($total_votes > 0) {

    echo '
        <p>Current tally:</p>
        <ul>
            <li>Plagiarized: '    . $vote_counts['plagiarized']     . '</li>
            <li>Not plagiarized: ' . $vote_counts['not_plagiarized'] . '</li>
            <li>Abstain: '        . $vote_counts['abstain']         . '</li>
            <li>Total votes: '    . $total_votes                    . '</li>
        </ul>
    ';

    echo '
        <table border="1">
            <tr>
                <th>Voter</th>
                <th>Vote</th>
                <th>Date</th>
                <th>Rationale</th>
            </tr>
    ';

    foreach ($votes as $v) {

        echo '
            <tr>
                <td>' . htmlspecialchars($v['member_name']) . '</td>
                <td>' . htmlspecialchars($v['vote']) . '</td>
                <td>' . htmlspecialchars($v['vote_date']) . '</td>
                <td>' . nl2br(htmlspecialchars($v['rationale'])) . '</td>
            </tr>
        ';
    }

    echo '</table>';

} else {
    echo '<p>No votes have been recorded yet for this case.</p>';
}
?>

<br>

<!--
    TODO (later):
    - Add voting form here, restricted to members who:
      * downloaded this text, AND
      * are within the 14-day voting window, AND
      * have not already voted on this case.
    - Also add admin/committee controls to move case status
      (e.g., from "under_review" -> "voting" -> "closed").
-->

<?php include 'footer.php'; ?>
