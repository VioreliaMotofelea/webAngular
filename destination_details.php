<?php
require_once 'db_connect.php';

if (!isset($_GET['id'])) {
    header('Location: browse.php');
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM destinations WHERE id = :id");
    $stmt->execute([':id' => $_GET['id']]);
    $destination = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$destination) {
        header('Location: browse.php');
        exit;
    }
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($destination['location']); ?> - Travel Destinations</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Travel Destinations</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="add_destination.php">Add Destination</a>
                <a href="browse.php">Browse Destinations</a>
            </nav>
        </header>

        <main>
            <div class="destination-details">
                <h2><?php echo htmlspecialchars($destination['location']); ?>, <?php echo htmlspecialchars($destination['country']); ?></h2>
                
                <?php if (isset($destination['image_url']) && !empty($destination['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($destination['image_url']); ?>" alt="<?php echo htmlspecialchars($destination['location']); ?>" class="destination-image">
                <?php endif; ?>

                <div class="destination-info">
                    <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($destination['description'])); ?></p>
                    <p><strong>Tourist Targets:</strong> <?php echo nl2br(htmlspecialchars($destination['tourist_targets'])); ?></p>
                    <p><strong>Cost per day:</strong> $<?php echo number_format($destination['cost_per_day'], 2); ?></p>
                </div>

                <div class="reviews-section">
                    <h3>Reviews</h3>
                    <div id="reviews-list"></div>

                    <div class="add-review">
                        <h4>Add a Review</h4>
                        <form id="review-form">
                            <input type="hidden" name="destination_id" value="<?php echo $destination['id']; ?>">
                            <div>
                                <label for="reviewer_name">Your Name:</label>
                                <input type="text" id="reviewer_name" name="reviewer_name" required>
                            </div>
                            <div>
                                <label for="rating">Rating:</label>
                                <select id="rating" name="rating" required>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                            <div>
                                <label for="comment">Comment:</label>
                                <textarea id="comment" name="comment" required></textarea>
                            </div>
                            <button type="submit">Submit Review</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function loadReviews() {
            fetch(`api/get_reviews.php?destination_id=<?php echo $destination['id']; ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const reviewsList = document.getElementById('reviews-list');
                        reviewsList.innerHTML = data.reviews.map(review => `
                            <div class="review">
                                <h4>${review.reviewer_name} - ${review.rating} Stars</h4>
                                <p>${review.comment}</p>
                                <small>Posted on ${new Date(review.created_at).toLocaleDateString()}</small>
                            </div>
                        `).join('');
                    }
                })
                .catch(error => console.error('Error loading reviews:', error));
        }

        document.getElementById('review-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                destination_id: <?php echo $destination['id']; ?>,
                reviewer_name: document.getElementById('reviewer_name').value,
                rating: document.getElementById('rating').value,
                comment: document.getElementById('comment').value
            };

            fetch('api/add_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Review added successfully!');
                    document.getElementById('review-form').reset();
                    loadReviews();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => console.error('Error submitting review:', error));
        });

        loadReviews();
    </script>
</body>
</html> 