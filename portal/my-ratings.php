<?php
$page_title = "My Ratings | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');
include('includes/header.php');
include('includes/sidebar.php');
?>
<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-gradient text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">My Ratings & Reviews</h5>
                        <button class="btn btn-light btn-sm" onclick="showRateServiceModal()">
                            <i class="fas fa-plus mr-1"></i> Rate Service
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Rating Summary -->
                        <div class="row mb-4">
                            <div class="col-md-3 text-center">
                                <div class="stat-card">
                                    <div class="stat-number text-primary" id="averageRating">0.0</div>
                                    <div class="stat-label">Average Rating</div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stat-card">
                                    <div class="stat-number text-info" id="totalRatings">0</div>
                                    <div class="stat-label">Total Ratings</div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stat-card">
                                    <div class="stat-number text-success" id="fiveStarRatings">0</div>
                                    <div class="stat-label">5-Star Ratings</div>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="stat-card">
                                    <div class="stat-number text-warning" id="pendingRatings">0</div>
                                    <div class="stat-label">Services to Rate</div>
                                </div>
                            </div>
                        </div>

                        <!-- Ratings Table -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="ratingsTable">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Worker</th>
                                        <th>Rating</th>
                                        <th>Comment</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="ratingsTableBody">
                                    <!-- Ratings will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rate Service Modal -->
<div class="modal fade" id="rateServiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title">Rate Service</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="rateServiceForm">
                    <div class="form-group">
                        <label>Select Service</label>
                        <select class="form-control" id="serviceSelect" required>
                            <option value="">Choose a completed service...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Rating</label>
                        <div class="rating-stars mb-2">
                            <span class="star" data-rating="1"><i class="far fa-star"></i></span>
                            <span class="star" data-rating="2"><i class="far fa-star"></i></span>
                            <span class="star" data-rating="3"><i class="far fa-star"></i></span>
                            <span class="star" data-rating="4"><i class="far fa-star"></i></span>
                            <span class="star" data-rating="5"><i class="far fa-star"></i></span>
                        </div>
                        <input type="hidden" id="selectedRating" name="rating" required>
                    </div>
                    <div class="form-group">
                        <label>Comment (Optional)</label>
                        <textarea class="form-control" id="ratingComment" name="comment" rows="3" 
                                  placeholder="Share your experience..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-gradient" onclick="submitRating()">Submit Rating</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Rating Modal -->
<div class="modal fade" id="editRatingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title">Edit Rating</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editRatingForm">
                    <input type="hidden" id="editRatingId">
                    <div class="form-group">
                        <label>Rating</label>
                        <div class="rating-stars mb-2" id="editRatingStars">
                            <span class="star" data-rating="1"><i class="far fa-star"></i></span>
                            <span class="star" data-rating="2"><i class="far fa-star"></i></span>
                            <span class="star" data-rating="3"><i class="far fa-star"></i></span>
                            <span class="star" data-rating="4"><i class="far fa-star"></i></span>
                            <span class="star" data-rating="5"><i class="far fa-star"></i></span>
                        </div>
                        <input type="hidden" id="editSelectedRating" name="rating" required>
                    </div>
                    <div class="form-group">
                        <label>Comment</label>
                        <textarea class="form-control" id="editRatingComment" name="comment" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-gradient" onclick="updateRating()">Update Rating</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentEditRatingId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadRatings();
    loadCompletedServices();
    initializeStarRating();
});

function loadRatings() {
    fetch('php/ratings-management.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRatings(data.ratings);
                updateSummary(data.ratings);
            }
        })
        .catch(error => console.error('Error:', error));
}

function displayRatings(ratings) {
    const tbody = document.getElementById('ratingsTableBody');
    
    if (ratings.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="fas fa-star fa-2x text-muted mb-3"></i>
                    <p class="text-muted">No ratings yet</p>
                    <button class="btn btn-gradient btn-sm" onclick="showRateServiceModal()">
                        Rate Your First Service
                    </button>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    ratings.forEach(rating => {
        const stars = generateStars(rating.rating);
        html += `
            <tr>
                <td>${rating.service_type}</td>
                <td>${rating.worker_name || 'N/A'}</td>
                <td>
                    <div class="rating-stars">
                        ${stars}
                    </div>
                    <small class="text-muted">${rating.rating}/5</small>
                </td>
                <td>${rating.comment || '<span class="text-muted">No comment</span>'}</td>
                <td>${formatDate(rating.created_at)}</td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="editRating(${rating.id}, ${rating.rating}, '${rating.comment || ''}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRating(${rating.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function generateStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        const starClass = i <= rating ? 'fas fa-star' : 'far fa-star';
        stars += `<i class="${starClass} text-warning"></i> `;
    }
    return stars;
}

function updateSummary(ratings) {
    if (ratings.length === 0) return;
    
    const total = ratings.length;
    const average = (ratings.reduce((sum, r) => sum + r.rating, 0) / total).toFixed(1);
    const fiveStar = ratings.filter(r => r.rating === 5).length;
    
    document.getElementById('averageRating').textContent = average;
    document.getElementById('totalRatings').textContent = total;
    document.getElementById('fiveStarRatings').textContent = fiveStar;
}

function initializeStarRating() {
    // Initialize for both modals
    initializeStars('rateServiceModal');
    initializeStars('editRatingModal');
}

function initializeStars(modalId) {
    const stars = document.querySelectorAll(`#${modalId} .star`);
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.getAttribute('data-rating'));
            setRating(rating, modalId);
        });
    });
}

function setRating(rating, modalId) {
    const stars = document.querySelectorAll(`#${modalId} .star`);
    const hiddenInput = modalId === 'rateServiceModal' ? 
        document.getElementById('selectedRating') : document.getElementById('editSelectedRating');
    
    stars.forEach(star => {
        const starRating = parseInt(star.getAttribute('data-rating'));
        const icon = star.querySelector('i');
        
        if (starRating <= rating) {
            icon.className = 'fas fa-star text-warning';
        } else {
            icon.className = 'far fa-star';
        }
    });
    
    hiddenInput.value = rating;
}

function showRateServiceModal() {
    $('#rateServiceModal').modal('show');
}

function loadCompletedServices() {
    fetch('php/get-completed-services.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const select = document.getElementById('serviceSelect');
                let html = '<option value="">Choose a completed service...</option>';
                
                data.services.forEach(service => {
                    html += `<option value="${service.id}">${service.service_type} - ${service.vehicle_info}</option>`;
                });
                
                select.innerHTML = html;
            }
        })
        .catch(error => console.error('Error:', error));
}

function submitRating() {
    const formData = new FormData();
    formData.append('service_id', document.getElementById('serviceSelect').value);
    formData.append('rating', document.getElementById('selectedRating').value);
    formData.append('comment', document.getElementById('ratingComment').value);
    
    fetch('php/ratings-management.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#rateServiceModal').modal('hide');
            showAlert('Rating submitted successfully!', 'success');
            loadRatings();
            resetRatingForm();
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Error submitting rating', 'error');
        console.error('Error:', error);
    });
}

function editRating(ratingId, currentRating, currentComment) {
    currentEditRatingId = ratingId;
    document.getElementById('editRatingId').value = ratingId;
    setRating(currentRating, 'editRatingModal');
    document.getElementById('editRatingComment').value = currentComment;
    $('#editRatingModal').modal('show');
}

function updateRating() {
    const params = new URLSearchParams();
    params.append('id', currentEditRatingId);
    params.append('comment', document.getElementById('editRatingComment').value);

    fetch('php/ratings-management.php', {
        method: 'PUT',
        body: params
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#editRatingModal').modal('hide');
            showAlert('Rating updated successfully!', 'success');
            loadRatings();
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Error updating rating', 'error');
        console.error('Error:', error);
    });
}

function deleteRating(ratingId) {
    if (!confirm('Are you sure you want to delete this rating?')) return;
    
    fetch('php/ratings-management.php', {
        method: 'DELETE',
        body: JSON.stringify({ id: ratingId }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Rating deleted successfully!', 'success');
            loadRatings();
        } else {
            showAlert('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showAlert('Error deleting rating', 'error');
        console.error('Error:', error);
    });
}

function resetRatingForm() {
    document.getElementById('rateServiceForm').reset();
    setRating(0, 'rateServiceModal');
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = document.createElement('div');
    alert.className = `alert ${alertClass} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.querySelector('.main-content').insertBefore(alert, document.querySelector('.container-fluid'));
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>

<?php include('includes/footer.php'); ?>