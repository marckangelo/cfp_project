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
if (!isset($_GET['text_id'])) {
    $errors[] = "text_id is missing from GET";
}

// DISPLAY TEXT DETAILS (the one receiving the donation)
    $sql_text_details = "SELECT title 
                         FROM text
                         WHERE text_id = " . (int) $_GET['text_id'];
    
    // Run the query
    $result_text_title = mysqli_query($conn, $sql_text_details);

    // Fetch the data
    $row = mysqli_fetch_assoc($result_text_title);

echo "<h2>Donate for: " . $row['title'] . "</h2>";
?>

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