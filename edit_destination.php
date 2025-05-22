<?php
require_once 'db_connect.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: browse.php');
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE id = ?");
    $stmt->execute([$id]);
    $destination = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$destination) {
        header('Location: browse.php');
        exit;
    }
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $country = filter_input(INPUT_POST, 'country', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $tourist_targets = filter_input(INPUT_POST, 'tourist_targets', FILTER_SANITIZE_STRING);
    $cost_per_day = filter_input(INPUT_POST, 'cost_per_day', FILTER_VALIDATE_FLOAT);

    if ($location && $country && $cost_per_day) {
        try {
            $stmt = $conn->prepare("UPDATE destinations SET location = ?, country = ?, description = ?, tourist_targets = ?, cost_per_day = ? WHERE id = ?");
            $stmt->execute([$location, $country, $description, $tourist_targets, $cost_per_day, $id]);
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
    <title>Edit Destination - Vacation Destinations Manager</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Destination</h1>
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

            <form method="POST" action="edit_destination.php?id=<?php echo $id; ?>" id="editDestinationForm">
                <div class="form-group">
                    <label for="location">Location (City/Place)*:</label>
                    <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($destination['location']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="country">Country*:</label>
                    <input type="text" id="country" name="country" value="<?php echo htmlspecialchars($destination['country']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($destination['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tourist_targets">Tourist Targets:</label>
                    <textarea id="tourist_targets" name="tourist_targets" rows="4"><?php echo htmlspecialchars($destination['tourist_targets']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="cost_per_day">Cost per Day (USD)*:</label>
                    <input type="number" id="cost_per_day" name="cost_per_day" step="0.01" min="0" value="<?php echo htmlspecialchars($destination['cost_per_day']); ?>" required>
                </div>

                <button type="submit">Update Destination</button>
            </form>
        </main>
    </div>

    <script src="js/validation.js"></script>
</body>
</html> 