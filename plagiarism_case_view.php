<?php
session_start();
require 'db.php';
include 'header.php';

// ================== ONLY ALLOW LOGGED-IN MEMBER ==================
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

// Storing the role of the current user (easier checks using these variables in if-statements)
$member_id = (int) $_SESSION['member_id'];
$is_admin  = isset($_SESSION['admin_id']);

$error_msg = "";
$case_status_msg = "";
$committee_role_for_case = "";

// If any case status messages, show them once
if (isset($_SESSION['case_status_msg'])) {
    $case_status_msg = $_SESSION['case_status_msg'];
    unset($_SESSION['case_status_msg']);
}

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
            SELECT cm.membership_id, cm.role
            FROM committee_membership cm
            WHERE cm.member_id = $member_id
              AND cm.committee_id = $committee_id_for_case
              AND cm.status = 'active'
            LIMIT 1
        ";

        $result_check_member = mysqli_query($conn, $sql_check_member);

        if ($result_check_member && mysqli_num_rows($result_check_member) > 0) {
            $row_check_member = mysqli_fetch_assoc($result_check_member);
            $authorized = true;
            $committee_role_for_case = $row_check_member['role'];
        }
    }

    if (!$authorized) {
        $error_msg = "You are not authorized to view this plagiarism case.";
    }
}

// ================== DETERMINE WHO CAN MANAGE CASE STATUS (ADMIN OR CHAIR) ==================
$is_chair_for_case = false;
$can_manage_case_status = false;

if ($error_msg === "" && $case_row !== null && $authorized) {

    if ($is_admin) {
        $can_manage_case_status = true;
    } else {
        if ($committee_role_for_case === 'chair') {
            $is_chair_for_case = true;
            $can_manage_case_status = true;
        }
    }
}

// ================== HANDLE CASE STATUS MANAGEMENT (ADMIN OR COMMITTEE CHAIR) ==================
if ($error_msg === "" && $authorized && $case_row !== null && $can_manage_case_status &&
    $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['case_action'])) {

    $case_action = $_POST['case_action'];
    $sql_update_case = "";

    if ($case_action === 'start_voting') {

        $sql_update_case = "
            UPDATE plagiarism_case
            SET status = 'voting'
            WHERE case_id = $case_id
        ";

    } else if ($case_action === 'close_plagiarized') {

        $sql_update_case = "
            UPDATE plagiarism_case
            SET status = 'closed',
                resolution = 'plagiarized',
                closed_date = CURDATE()
            WHERE case_id = $case_id
        ";

    } else if ($case_action === 'close_not_plagiarized') {

        $sql_update_case = "
            UPDATE plagiarism_case
            SET status = 'closed',
                resolution = 'not_plagiarized',
                closed_date = CURDATE()
            WHERE case_id = $case_id
        ";
    }

    if ($sql_update_case !== "") {
        $result_update_case = mysqli_query($conn, $sql_update_case);

        if ($result_update_case) {
            $_SESSION['case_status_msg'] = "Case status updated successfully.";
        } else {
            $_SESSION['case_status_msg'] = "Failed to update case status.";
        }

        header("Location: plagiarism_case_view.php?case_id=" . $case_id);
        exit;
    }
}

// ================== HANDLE VOTING ELIGIBILITY AND SUBMISSION ==================
$vote_error_msg = "";
$vote_success_msg = "";
$can_vote = false;
$has_already_voted = false;

if ($error_msg === "" && $authorized && $case_row !== null) {

    $text_id_for_case = (int) $case_row['text_id'];
    $opened_date_case = $case_row['opened_date'];
    $status_case = $case_row['status'];

    // Check if this member has downloaded this text
    $has_downloaded = false;

    $sql_download_check = "
        SELECT download_id
        FROM download
        WHERE member_id = $member_id
          AND text_id = $text_id_for_case
        LIMIT 1
    ";

    $result_download_check = mysqli_query($conn, $sql_download_check);

    if ($result_download_check && mysqli_num_rows($result_download_check) > 0) {
        $has_downloaded = true;
    }

    // Check 14-day voting window from opened_date
    $today = date('Y-m-d');
    $diff_seconds = strtotime($today) - strtotime($opened_date_case);
    $days_since_open = (int) floor($diff_seconds / (60 * 60 * 24));
    $within_14_days = ($days_since_open >= 0 && $days_since_open <= 14);

    // Check if this member has already voted on this case
    $sql_vote_check = "
        SELECT vote_id
        FROM vote
        WHERE case_id = $case_id
          AND member_id = $member_id
        LIMIT 1
    ";

    $result_vote_check = mysqli_query($conn, $sql_vote_check);

    if ($result_vote_check && mysqli_num_rows($result_vote_check) > 0) {
        $has_already_voted = true;
    }

    // Final eligibility to vote:
    // - case must be in 'voting' status
    // - member downloaded the text
    // - within 14 days of case opening
    // - has not voted yet
    if ($status_case === 'voting' && $has_downloaded && $within_14_days && !$has_already_voted) {
        $can_vote = true;
    }

    // Handle vote submission (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vote'])) {

        if (!$can_vote) {
            $vote_error_msg = "You are not allowed to vote on this case.";
        } else {

            $vote_choice = $_POST['vote_choice'];
            $rationale = trim($_POST['rationale']);

            if ($vote_choice !== 'plagiarized' && $vote_choice !== 'not_plagiarized' && $vote_choice !== 'abstain') {
                $vote_error_msg = "Invalid vote choice.";
            } else {

                $vote_choice_sql = mysqli_real_escape_string($conn, $vote_choice);
                $rationale_sql = mysqli_real_escape_string($conn, $rationale);

                $sql_insert_vote = "
                    INSERT INTO vote (case_id, member_id, vote, date, rationale)
                    VALUES ($case_id, $member_id, '$vote_choice_sql', CURDATE(), '$rationale_sql')
                ";

                $result_insert_vote = mysqli_query($conn, $sql_insert_vote);

                if ($result_insert_vote) {
                    $vote_success_msg = "Your vote has been recorded.";
                    $has_already_voted = true;
                    $can_vote = false; // prevent another vote
                } else {
                    $vote_error_msg = "Failed to record your vote. Please try again.";
                }
            }
        }
    }
}

// ================== LOAD VOTES FOR THIS CASE (FOR DISPLAY) ==================
$votes = array();
$vote_counts = array();
$vote_counts['plagiarized'] = 0;
$vote_counts['not_plagiarized'] = 0;
$vote_counts['abstain'] = 0;

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

// Case status message (if any)
if ($case_status_msg !== "") {
    echo '<div style="color: green;">' . htmlspecialchars($case_status_msg) . '</div>';
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

<?php
// Show vote messages and voting form (if user is eligible to vote)
if ($authorized) {

    if ($vote_error_msg !== "") {
        echo '<div style="color: red;">' . htmlspecialchars($vote_error_msg) . '</div>';
    }

    if ($vote_success_msg !== "") {
        echo '<div style="color: green;">' . htmlspecialchars($vote_success_msg) . '</div>';
    }

    if ($can_vote) {

        echo '
        <h3>Cast Your Vote</h3>
        <form method="post" action="plagiarism_case_view.php?case_id=' . $case_id . '">
            <p>
                <label>
                    <input type="radio" name="vote_choice" value="plagiarized" required>
                    Plagiarized
                </label><br>
                <label>
                    <input type="radio" name="vote_choice" value="not_plagiarized">
                    Not plagiarized
                </label><br>
                <label>
                    <input type="radio" name="vote_choice" value="abstain">
                    Abstain
                </label>
            </p>
            <p>
                <label for="rationale">Rationale (optional):</label><br>
                <textarea id="rationale" name="rationale" rows="4" cols="60"></textarea>
            </p>
            <button type="submit" name="submit_vote">Submit Vote</button>
        </form>
        ';

    } else if ($vote_success_msg === "" && $has_already_voted) {

        echo '<p>You have already voted on this case.</p>';

    } else if ($vote_success_msg === "" && !$has_already_voted) {
        // Not eligible to vote (if e.g.: did not download, outside 14-day window, or case not in "voting")
        echo '<p>You are currently not eligible to vote on this case.</p>';
    }
}
?>

<br>

<?php
// Case management section: admin or committee chair can change status
if ($authorized && $can_manage_case_status) {

    echo '<h3>Case Management</h3>';

    if ($status === 'open' || $status === 'under_review') {

        echo '
        <form method="post" action="plagiarism_case_view.php?case_id=' . $case_id . '">
            <input type="hidden" name="case_action" value="start_voting">
            <button type="submit">Set Status to "voting"</button>
        </form>
        ';
    }

    if ($status === 'voting') {

        echo '
        <form method="post" action="plagiarism_case_view.php?case_id=' . $case_id . '">
            <input type="hidden" name="case_action" value="close_plagiarized">
            <button type="submit">Close as "plagiarized"</button>
        </form>
        ';

        echo '
        <form method="post" action="plagiarism_case_view.php?case_id=' . $case_id . '">
            <input type="hidden" name="case_action" value="close_not_plagiarized">
            <button type="submit">Close as "not plagiarized"</button>
        </form>
        ';
    }
}
?>

<!--
    TODO (later):
    - Also add logic on closing to apply the 2/3 rule for blacklisting the text/author.
-->

<?php include 'footer.php'; ?>
