let currentPage = 1;
const destinationsPerPage = 4;

function loadDestinations(page, country = '') {
    $.ajax({
        url: 'api/get_destinations.php',
        method: 'GET',
        data: {
            page: page,
            country: country
        },
        success: function(response) {
            const destinations = response.destinations;
            const totalPages = response.totalPages;
            
            $('#prev-page').prop('disabled', page === 1);
            $('#next-page').prop('disabled', page === totalPages);
            $('#page-info').text(`Page ${page} of ${totalPages}`);
            
            const container = $('#destinations-container');
            container.empty();
            
            if (destinations.length === 0) {
                container.html('<p>No destinations found.</p>');
                return;
            }
            
            destinations.forEach(destination => {
                const card = `
                    <div class="destination-card">
                        <h3>${destination.location}, ${destination.country}</h3>
                        <p><strong>Cost per day:</strong> $${destination.cost_per_day}</p>
                        <p><strong>Description:</strong> ${destination.description || 'No description available'}</p>
                        <p><strong>Tourist Targets:</strong> ${destination.tourist_targets || 'No tourist targets listed'}</p>
                        <div class="card-actions">
                            <button onclick="editDestination(${destination.id})">Edit</button>
                            <button onclick="deleteDestination(${destination.id})">Delete</button>
                        </div>
                    </div>
                `;
                container.append(card);
            });
        },
        error: function(xhr, status, error) {
            console.error('Error loading destinations:', error);
            $('#destinations-container').html('<p>Error loading destinations. Please try again later.</p>');
        }
    });
}

function editDestination(id) {
    window.location.href = `edit_destination.php?id=${id}`;
}

function deleteDestination(id) {
    if (confirm('Are you sure you want to delete this destination?')) {
        $.ajax({
            url: 'api/delete_destination.php',
            method: 'POST',
            data: { id: id },
            success: function(response) {
                if (response.success) {
                    loadDestinations(currentPage, $('#country-filter').val());
                } else {
                    alert('Error deleting destination: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Error deleting destination. Please try again later.');
            }
        });
    }
}

$(document).ready(function() {
    loadDestinations(currentPage);
    
    $('#prev-page').click(function() {
        if (currentPage > 1) {
            currentPage--;
            loadDestinations(currentPage, $('#country-filter').val());
        }
    });
    
    $('#next-page').click(function() {
        currentPage++;
        loadDestinations(currentPage, $('#country-filter').val());
    });
    
    $('#country-filter').change(function() {
        currentPage = 1;
        loadDestinations(currentPage, $(this).val());
    });
}); 