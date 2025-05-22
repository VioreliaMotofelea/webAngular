<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $tourist_targets = filter_input(INPUT_POST, 'tourist_targets', FILTER_SANITIZE_STRING);
    $cost_per_day = filter_input(INPUT_POST, 'cost_per_day', FILTER_VALIDATE_FLOAT);

    if ($location && $country && $cost_per_day) {
        try {
            $stmt = $conn->prepare("INSERT INTO destinations (location, country, description, tourist_targets, cost_per_day) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$location, $country, $description, $tourist_targets, $cost_per_day]);
            header("Location: browse.php");
            exit();
        } catch(PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Please fill in all required fields correctly.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Destination - Vacation Destinations Manager</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Add New Destination</h1>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="add_destination.php">Add Destination</a></li>
                    <li><a href="browse.php">Browse Destinations</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="add_destination.php" id="addDestinationForm">
                <div class="form-group">
                    <label for="location">Location (City/Place)*:</label>
                    <input type="text" id="location" name="location" required>
                </div>

                <div class="form-group">
                    <label for="country">Country*:</label>
                    <input type="text" id="country" name="country" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="tourist_targets">Tourist Targets:</label>
                    <textarea id="tourist_targets" name="tourist_targets" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="cost_per_day">Cost per Day (USD)*:</label>
                    <input type="number" id="cost_per_day" name="cost_per_day" step="0.01" min="0" required>
                </div>

                <button type="submit">Add Destination</button>
            </form>
        </main>
    </div>

    <script src="js/validation.js"></script>
</body>
</html> 