<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic member-type log-in checks
$is_logged_in = !empty($_SESSION['member_id']);
$is_author = !empty($_SESSION['orcid']);
$is_admin = (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true)
                || !empty($_SESSION['admin_id']);

// Matrix verification flag
$is_matrix_verified = (!empty($_SESSION['matrix_verified']) && $_SESSION['matrix_verified'] === true);

// "Fully logged in" = has member_id AND has passed matrix check
$is_fully_logged_in = ($is_logged_in && $is_matrix_verified);

// Messages
$has_unread = !empty($_SESSION['has_unread']);
$unread_count = isset($_SESSION['unread_count']) ? (int)$_SESSION['unread_count'] : 0;

// If unread count is 0, make sure we no longer show (NEW)
if ($unread_count <= 0) {
    $has_unread = false;
}

// Admin role ('super', 'content' or 'financial')
$admin_role = isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : null;

// A check to show "Plagiarism Cases" link
$show_plag_cases_link = false;

if ($is_fully_logged_in && !empty($_SESSION['member_id']) && isset($conn)) {

    $member_id = (int) $_SESSION['member_id'];

    // Case 1: super admin always sees the link
    if ($is_admin && $admin_role === 'super') {
        $show_plag_cases_link = true;
    } else {
        // Case 2: any member who is in an active plagiarism-scope committee
        $sql_plag_scope = "
            SELECT cm.membership_id
            FROM committee_membership cm
            JOIN committee c
                ON cm.committee_id = c.committee_id
            WHERE cm.member_id = $member_id
              AND cm.status = 'active'
              AND c.status = 'active'
              AND c.scope = 'plagiarism'
            LIMIT 1
        ";

        $result_plag_scope = mysqli_query($conn, $sql_plag_scope);

        if ($result_plag_scope && mysqli_num_rows($result_plag_scope) > 0) {
            $show_plag_cases_link = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CFP Website</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <header>
        <h1>CFP Repository</h1>
        <nav>
            <a href="index.php">Home</a> |
            <a href="browse.php">Browse</a> |
            <a href="authors.php">Authors</a> |
            <a href="statistics.php">Statistics</a> |
            <a href="about.php">About</a> |

            <?php if ($is_fully_logged_in): ?>

                <!-- Committees only once fully logged in (after matrix verified) -->
                <a href="committees.php">Committees</a> |

                <?php if ($show_plag_cases_link): ?>
                    <a href="plagiarism_cases.php">Plagiarism Cases</a> |
                <?php endif; ?>

                <!-- Inbox with NEW label if unread -->
                <a href="messages_inbox.php">
                    Inbox<?php if ($has_unread) { echo ' (NEW)'; } ?>
                </a> |

                <?php if ($is_author): ?>
                    <a href="author_dashboard.php">Author Dashboard</a> |
                <?php endif; ?>

                <?php if ($is_admin): ?>
                    <!-- Admin dashboard; internal checks will enforce role/permissions -->
                    <a href="admin_dashboard.php">Admin Dashboard</a> |
                <?php endif; ?>

                <a href="my_account.php">My Account</a> |
                <a href="logout.php">Logout</a> |

            <?php elseif ($is_logged_in): ?>
                <!-- Email/password is verified but matrix not yet verified:
                     "partially logged in" only show Logout. -->
                <a href="logout.php">Logout</a> |

            <?php else: ?>
                <!-- Not logged in at all -->
                <a href="login.php">Login</a> |
                <a href="signup.php">Sign Up</a> |
            <?php endif; ?>
        </nav>
    </header>

    <?php
        // Simple popup if there are unread messages and we haven't shown it yet
        // Only when FULLY logged in (so email + password + matrix = fully logged in)
        if ($is_fully_logged_in && $has_unread && empty($_SESSION['unread_alert_shown'])) {
            $_SESSION['unread_alert_shown'] = true;
            echo "<script>alert('You have new messages in your inbox.');</script>";
        }
    ?>

    <main>