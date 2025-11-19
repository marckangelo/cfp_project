<?php
session_start();
require 'db.php';
include 'header.php';

// TODO: On POST, validate allocation and insert donation
$errors = array();
$success = "";

echo "<h2>Donate</h2>";
echo "<p>";
//TODO: Implement donation form (amount and allocation to charity/CFP/author);

// Check if user is logged in
// if (isset($_SESSION['member_id']) == false) {
//     echo "You must be logged in to make a donation.";
//     include 'footer.php';
//     exit();
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //retreive from data
    $member_id = $_SESSION['member_id'];
    $text_id = trim($_POST['text_id']);
    $charity_id = trim($_POST['charity_id']);
    $amount = trim($_POST['amount']);
    $date = trim($_POST['date']);
    $currency = trim($_POST['currency']);
    $payment_method = trim($_POST['payment_method']);
    $transaction_id = trim($_POST['transaction_id']);

    // Basic validation
    if ($text_id == "" || !is_numeric($text_id)) {
        $errors[] = "Text ID must be a number if provided.";
    }

    if ($charity_id == "" || !is_numeric($charity_id)) {
        $errors[] = "Charity ID must be a number if provided.";
    }

    if ($amount == "" || !is_numeric($amount) || $amount <= 0) {
        $errors[] = "An amount is required and must be a positive number.";
    }

    // Set date to today if not provided
    if ($date  == "") {
        $date = date('Y-m-d');
    }

    $currency = ($currency == "") ? "NULL" : $currency;
    $payment_method = ($payment_method == "") ? "" : $payment_method;
    $transaction_id = ($transaction_id == "") ? "" : $transaction_id;

    //if no errors, insert donation
    if (count($errors) == 0) {
        $query = "INSERT INTO donation (member_id, text_id, charity_id, amount, date, currency, payment_method, transaction_id, charity_pct, cfp_pct, author_pct)
                  VALUES ($member_id, $text_id, $charity_id, $amount, '$date', '$currency', '$payment_method', '$transaction_id', $charity_pct, $cfp_pct, $author_pct)";
        if (mysqli_query($conn, $query)) {
            $success = "Donation recorded successfully.";
        } else {
            $errors[] = "Error recording donation: " . mysqli_error($conn);
        }
        if (count($errors) > 0) {
            echo '<div style="color:red;"><ul>';
            for ($i = 0; $i < count($errors); $i++) {
                echo '<li>' . $errors[$i] . '</li>';
            }
            echo '</ul></div>';
        } else {
            echo '<div style="color:green;">' . $success . '</div>';
        }
    }
} ?>

<h2>Donate for: <?php echo htmlspecialchars($text['title']); ?></h2>

<form action="donate_process.php" method="post">
    <!-- hidden context -->
    <input type="hidden" name="text_id" value="<?php echo $text_id; ?>">

    <label>Amount ($):</label>
    <input type="number" name="amount" min="1" step="0.01" required><br>

    <label>Currency ($):</label>
    <select name="payment_method">
        <option value="CAD">CAD (simulated)</option>
        <option value="USD">USD (simulated)</option>
        <option value="EUR">EUR (simulated)</option>
    </select><br>

    <label>Charity:</label>
    <select name="charity_id" required>
        <?php foreach ($charities as $c): ?>
            <option value="<?php echo $c['charity_id']; ?>">
                <?php echo htmlspecialchars($c['name']); ?>
            </option>
        <?php endforeach; ?>
    </select><br>

    <label>Charity % (min 60):</label>
    <input type="number" name="charity_pct" min="60" max="100" required><br>

    <label>CFP %:</label>
    <input type="number" name="cfp_pct" min="0" max="40" required>

    <label>Author %:</label>
    <input type="number" name="author_pct" min="0" max="40" required><br>

    <!-- optional: payment method, currency -->
    <label>Payment method:</label>
    <select name="payment_method">
        <option value="card">Credit Card (simulated)</option>
        <option value="paypal">PayPal (simulated)</option>
    </select><br>

    <button type="submit">Donate</button>
</form>

<?php
include 'footer.php';
?>