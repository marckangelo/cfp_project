<?php
session_start();
require 'db.php';
include 'header.php';

$errors = array();
$success = "";

// TODO: On POST, validate allocation and insert donation

echo "<h2>Donate</h2>";
echo "<p>";

// Check data
if (!isset($_POST['text_id'])) {
    echo "<p style='color:red;'>Missing text reference for donation.</p>";
    include 'footer.php';
    exit;
}
// DISPLAY TEXT DETAILS (the one receiving the donation)
$text_id = (int) $_POST['text_id'];

// Percentage validation
if (isset($_POST['charity_pct'], $_POST['cfp_pct'], $_POST['author_pct'])) {
    $charity_pct = (int)$_POST['charity_pct'];
    $cfp_pct = (int)$_POST['cfp_pct'];
    $author_pct = (int)$_POST['author_pct'];
    $total_pct = (int)$_POST['charity_pct'] + (int)$_POST['cfp_pct'] + (int)$_POST['author_pct'];
    //min charity pct 60%
    if ($charity_pct < 60) {
        $errors[] = "Charity percentage must be at least 60%.";
        echo $errors[count($errors)-1];
    }
    //total must be 100%
    if ($total_pct !== 100) {
        $errors[] = "The total percentage allocation must equal 100%. Currently it equals $total_pct%.";
        echo $errors[count($errors)-1];
    }
} else {
    $errors[] = "Percentage allocations are missing.";
    echo $errors[count($errors)-1];
}

// Build SQL
$sql_text_title = "SELECT title 
                   FROM text
                   WHERE text_id = $text_id";

$sql_charity = "SELECT charity_id, name 
                FROM charity
                WHERE status = 'active'";

// Run query
$result_text_title = mysqli_query($conn, $sql_text_title);
// Check results
if (!$result_text_title || mysqli_num_rows($result_text_title) === 0) {
    echo "<p style='color:red;'>Text not found.</p>";
    include 'footer.php';
    exit;
}

$result_charities = mysqli_query($conn, $sql_charity);


while($c = mysqli_fetch_assoc($result_charities)) {
    $charities[] = $c;
};

// Fetch the data
if ($result_text_title) {
    $row = mysqli_fetch_assoc($result_text_title);
}

echo "<h2>Donate for: " . $row['title'] . "</h2>";
?>

<form action="donate_process.php" method="post">
    <!-- hidden context -->
    <input type="hidden" name="text_id" value="<?php echo $text_id; ?>">

    <label>Amount ($):</label>
    <input type="number" name="amount" min="1" step="0.01" required><br>

    <label>Currency ($):</label>
    <select name="currency">
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
    
    <input type="hidden" name="title" value="<?php echo htmlspecialchars($row['title']);?>">
    <button type="submit">Donate</button>
</form>

<?php
include 'footer.php';
?>