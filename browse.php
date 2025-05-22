<?php
require_once 'db_connect.php';

$items_per_page = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$country = isset($_GET['country']) ? $_GET['country'] : '';
$min_cost = isset($_GET['min_cost']) ? (float)$_GET['min_cost'] : 0;
$max_cost = (isset($_GET['max_cost']) && $_GET['max_cost'] !== '') ? (float)$_GET['max_cost'] : PHP_FLOAT_MAX;

$query = "SELECT * FROM destinations WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (location LIKE :search OR description LIKE :search OR tourist_targets LIKE :search)";
    $params[':search'] = "%$search%";
}

if ($country) {
    $query .= " AND country = :country";
    $params[':country'] = $country;
}

if ($min_cost > 0) {
    $query .= " AND cost_per_day >= :min_cost";
    $params[':min_cost'] = $min_cost;
}

if ($max_cost < PHP_FLOAT_MAX) {
    $query .= " AND cost_per_day <= :max_cost";
    $params[':max_cost'] = $max_cost;
}

$count_query = str_replace("SELECT *", "SELECT COUNT(*)", $query);

$stmt = $conn->prepare($count_query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$total_items = $stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

if ($page > $total_pages) {
    $page = $total_pages;
} elseif ($page < 1) {
    $page = 1;
}

$query .= " ORDER BY created_at DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($query);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', ($page - 1) * $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);

$stmt->execute();
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->query("SELECT DISTINCT country FROM destinations ORDER BY country");
$countries = $stmt->fetchAll(PDO::FETCH_COLUMN);

function render_pagination($page, $total_pages, $search, $country, $min_cost, $max_cost) {
    if ($total_pages > 1) {
        echo '<div class="pagination">';
        if ($page > 1) {
            echo '<a href="?page=' . ($page - 1) . '&search=' . urlencode($search) . '&country=' . urlencode($country) . '&min_cost=' . $min_cost . '&max_cost=' . $max_cost . '">Previous</a>';
        }
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = $i === $page ? 'active' : '';
            echo '<a href="?page=' . $i . '&search=' . urlencode($search) . '&country=' . urlencode($country) . '&min_cost=' . $min_cost . '&max_cost=' . $max_cost . '" class="' . $active . '">' . $i . '</a>';
        }
        if ($page < $total_pages) {
            echo '<a href="?page=' . ($page + 1) . '&search=' . urlencode($search) . '&country=' . urlencode($country) . '&min_cost=' . $min_cost . '&max_cost=' . $max_cost . '">Next</a>';
        }
        echo '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Destinations - Travel Destinations</title>
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
            <div class="search-filter">
                <form method="GET" action="browse.php" id="filter-form">
                    <div>
                        <label for="search">Search:</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search destinations...">
                    </div>
                    <div>
                        <label for="country">Country:</label>
                        <select id="country" name="country">
                            <option value="">All Countries</option>
                            <?php foreach ($countries as $c): ?>
                            <option value="<?php echo htmlspecialchars($c); ?>" <?php echo $country === $c ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($c); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="min_cost">Min Cost per Day:</label>
                        <input type="number" id="min_cost" name="min_cost" value="<?php echo $min_cost; ?>" min="0" step="0.01">
                    </div>
                    <div>
                        <label for="max_cost">Max Cost per Day:</label>
                        <input type="number" id="max_cost" name="max_cost" value="<?php echo isset($_GET['max_cost']) ? htmlspecialchars($_GET['max_cost']) : ''; ?>" min="0" step="0.01">
                    </div>
                    <button type="submit">Apply Filters</button>
                </form>
            </div>

            <div id="pagination"></div>
            <div id="destinations-grid" class="destinations-grid"></div>
            <div id="pagination-bottom"></div>
        </main>
    </div>

    <script>
    function fetchDestinations(page = 1) {
        const search = document.getElementById('search').value;
        const country = document.getElementById('country').value;
        const min_cost = document.getElementById('min_cost').value;
        const max_cost = document.getElementById('max_cost').value;

        const params = new URLSearchParams({
            page,
            limit: 4,
            search,
            country,
            min_cost,
            max_cost
        });

        fetch('api/get_destinations.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                renderDestinations(data.destinations);
                renderPagination(data.current_page, data.total_pages);
            });
    }

    function renderDestinations(destinations) {
        const grid = document.getElementById('destinations-grid');
        grid.innerHTML = '';
        if (destinations.length === 0) {
            grid.innerHTML = '<p>No destinations found.</p>';
            return;
        }
        destinations.forEach(destination => {
            grid.innerHTML += `
                <div class="destination-card">
                    ${destination.image_url ? `<img src="${destination.image_url}" alt="${destination.location}">` : ''}
                    <h3>${destination.location}, ${destination.country}</h3>
                    <p>${destination.description ? destination.description.substring(0, 150) + '...' : ''}</p>
                    <p class="cost">$${parseFloat(destination.cost_per_day).toFixed(2)} per day</p>
                    <div class="card-actions">
                        <a href="destination_details.php?id=${destination.id}" class="button">View Details</a>
                        <a href="edit_destination.php?id=${destination.id}" class="button">Edit</a>
                        <button onclick="deleteDestination(${destination.id})" class="button delete">Delete</button>
                    </div>
                </div>
            `;
        });
    }

    function renderPagination(current, total) {
        const pag = document.getElementById('pagination');
        const pagBottom = document.getElementById('pagination-bottom');
        pag.innerHTML = '';
        pagBottom.innerHTML = '';
        if (total > 1) {
            let html = '<div class="pagination">';
            if (current > 1) {
                html += `<a href="#" onclick="fetchDestinations(${current - 1});return false;">Previous</a>`;
            }
            for (let i = 1; i <= total; i++) {
                html += `<a href="#" class="${i === current ? 'active' : ''}" onclick="fetchDestinations(${i});return false;">${i}</a>`;
            }
            if (current < total) {
                html += `<a href="#" onclick="fetchDestinations(${current + 1});return false;">Next</a>`;
            }
            pag.innerHTML = html;
            pagBottom.innerHTML = html;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchDestinations();
        document.getElementById('filter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            fetchDestinations(1);
        });
    });
    </script>

    <script>
        function deleteDestination(id) {
            if (confirm('Are you sure you want to delete this destination?')) {
                fetch('api/delete_destination.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchDestinations();
                    } else {
                        alert('Error: ' + data.error);
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
    </script>
</body>
</html> 