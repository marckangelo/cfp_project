<?php
session_start();
require 'db.php';
include 'header.php';

// Only logged-in members can open a plagiarism case
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id   = (int) $_SESSION['member_id'];
$error_msg   = "";
$success_msg = "";

// Initialize for both GET and POST so they exist for the form
$text_id      = 0;
$committee_id = 0;
$text_title   = "";

// ================== PROCESS FORM ONLY IF SUBMITTED (THROUGH POST) ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get values from the form (your usual style)
    $text_id      = (int) $_POST['text_id'];
    $committee_id = (int) $_POST['committee_id'];
    $description  = trim($_POST['description']);

    // Basic validation
    if ($text_id <= 0 || $committee_id <= 0) {
        $error_msg = "Invalid text or committee.";
    } elseif ($description === "") {
        $error_msg = "Please provide a short description of the plagiarism concern.";
    } else {

        // Check that this member is an ACTIVE member of this ACTIVE plagiarism committee
        $sql_check_member = "
            SELECT cm.membership_id
            FROM committee_membership cm
            JOIN committee c
                ON cm.committee_id = c.committee_id
            WHERE cm.member_id   = $member_id
              AND cm.committee_id = $committee_id
              AND cm.status      = 'active'
              AND c.status       = 'active'
              AND c.scope        = 'plagiarism'
            LIMIT 1
        ";

        $result_check_member = mysqli_query($conn, $sql_check_member);

        if (!$result_check_member || mysqli_num_rows($result_check_member) === 0) {
            $error_msg = "You are not authorized to open a plagiarism case for this committee.";
        } else {

            // Optional: check if an active case already exists for this text
            $sql_existing_case = "
                SELECT case_id
                FROM plagiarism_case
                WHERE text_id = $text_id
                  AND status IN ('open', 'under_review', 'voting')
                LIMIT 1
            ";

            $result_existing_case = mysqli_query($conn, $sql_existing_case);

            if ($result_existing_case && mysqli_num_rows($result_existing_case) > 0) {
                $error_msg = "There is already an active plagiarism case for this text.";
            } else {
                // Insert new case
                $description_sql = mysqli_real_escape_string($conn, $description);

                $sql_insert_case = "
                    INSERT INTO plagiarism_case
                        (committee_id, text_id, opened_date, description, status, resolution)
                    VALUES
                        ($committee_id, $text_id, CURDATE(), '$description_sql', 'open', NULL)
                ";

                $result_insert_case = mysqli_query($conn, $sql_insert_case);

                if ($result_insert_case) {
                    $success_msg = "Plagiarism case opened successfully.";

                    // **** NOTE: Maybe redirect to a list of cases later: ****
                    // header("Location: plagiarism_cases.php");
                    // exit;
                } else {
                    $error_msg = "Failed to open plagiarism case. Please try again.";
                }
            }
        }
    }

    // Try to fetch the text title for display (even if there was an error)
    if ($text_id > 0) {
        $sql_text = "SELECT title FROM text WHERE text_id = $text_id";
        $result_text = mysqli_query($conn, $sql_text);
        //Checking if query ran well and if there is at least 1 row
        if ($result_text && mysqli_num_rows($result_text) > 0) {
            $row_text   = mysqli_fetch_assoc($result_text);
            $text_title = $row_text['title'];
        }
    }

// ================== INITIAL PAGE LOAD (GET) ==================
} else {

    if (!isset($_GET['text_id']) || !isset($_GET['committee_id'])) {
        // If missing parameters, just go back to main page (or item list)
        header("Location: item.php");
        exit;
    }

    $text_id      = (int) $_GET['text_id'];
    $committee_id = (int) $_GET['committee_id'];

    if ($text_id <= 0 || $committee_id <= 0) {
        $error_msg = "Invalid text or committee.";
    } else {
        // Fetch text title for display
        $sql_text = "SELECT title FROM text WHERE text_id = $text_id";
        $result_text = mysqli_query($conn, $sql_text);
        if ($result_text && mysqli_num_rows($result_text) > 0) {
            $row_text   = mysqli_fetch_assoc($result_text);
            $text_title = $row_text['title'];
        }

        // (Optional) quick check that the user is in this plagiarism committee
        $sql_plag_committee = "
            SELECT c.committee_id
            FROM committee_membership cm
            JOIN committee c
                ON cm.committee_id = c.committee_id
            WHERE cm.member_id = $member_id
              AND cm.committee_id = $committee_id
              AND cm.status = 'active'
              AND c.status = 'active'
              AND c.scope = 'plagiarism'
            LIMIT 1
        ";
        $result_plag_committee = mysqli_query($conn, $sql_plag_committee);
        if (!$result_plag_committee || mysqli_num_rows($result_plag_committee) === 0) {
            $error_msg = "You are not authorized to open a plagiarism case for this committee.";
        }
    }
}

?>

<h2>Open Plagiarism Case</h2>

<?php
// Display error or success messages
if ($error_msg !== "") {
    echo '<div style="color: red;">' . htmlspecialchars($error_msg) . '</div>';
}
if ($success_msg !== "") {
    echo '<div style="color: green;">' . htmlspecialchars($success_msg) . '</div>';
}
?>

<?php if ($success_msg === ""): ?>

    <?php if ($text_title !== ""): ?>
        <p><strong>Text:</strong> <?php echo htmlspecialchars($text_title); ?></p>
    <?php else: ?>
        <p><strong>Text ID:</strong> <?php echo (int)$text_id; ?></p>
    <?php endif; ?>

    <form method="post" action="plagiarism_case_open.php">

        <input type="hidden" name="text_id" value="<?php echo (int)$text_id; ?>">
        <input type="hidden" name="committee_id" value="<?php echo (int)$committee_id; ?>">

        <label for="description">Describe the plagiarism concern:</label><br>
        <textarea id="description" name="description" rows="5" cols="60" required></textarea><br><br>

        <button type="submit" name="submit_open_case">Open Case</button>
    </form>

<?php endif; ?>

<?php include 'footer.php'; ?>