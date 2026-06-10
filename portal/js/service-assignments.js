$(document).ready(function() {
    let currentServiceId = null;
    
    // Load service statistics
    loadServiceStatistics();
    
    // Filter services by status
    $('.filter-btn').on('click', function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        const status = $(this).data('status');
        if (status === 'all') {
            $('.service-row').show();
        } else {
            $('.service-row').hide();
            $('.service-row[data-status="' + status + '"]').show();
        }
    });

    // Pick up available service
    $(document).on('click', '.pickup-service', function() {
        const serviceId = $(this).data('service-id');
        pickupService(serviceId);
    });

    // View Service Details
    $(document).on('click', '.view-service', function() {
        const serviceId = $(this).data('service-id');
        currentServiceId = serviceId;
        viewServiceDetails(serviceId);
    });

    // Update Service
    $(document).on('click', '.update-service', function() {
        const serviceId = $(this).data('service-id');
        currentServiceId = serviceId;
        loadServiceForUpdate(serviceId);
    });

    // Add Progress Update
    $(document).on('click', '.add-update', function() {
        const serviceId = $(this).data('service-id');
        currentServiceId = serviceId;
        $('#updateServiceId2').val(serviceId);
        $('#updateServiceIdHeader2').text(serviceId.toString().padStart(6, '0'));
        $('#addUpdateForm')[0].reset();
        $('#addUpdateModal').modal('show');
    });

    // Update Service Form
    $('#updateServiceForm').on('submit', function(e) {
        e.preventDefault();
        updateService();
    });

    // Add Update Form
    $('#addUpdateForm').on('submit', function(e) {
        e.preventDefault();
        addProgressUpdate();
    });
});

function loadServiceStatistics() {
    $.ajax({
        url: 'php/get-service-statistics.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#totalAssigned').text(response.statistics.assigned || 0);
                $('#inProgressCount').text(response.statistics.in_progress || 0);
                $('#completedCount').text(response.statistics.completed || 0);
                $('#availableCount').text(response.statistics.available || 0);
                $('#assignedCount').text(response.statistics.assigned || 0);
                $('#availableServicesCount').text((response.statistics.available || 0) + ' services available');
            }
        }
    });
}

function pickupService(serviceId) {
    if (!confirm('Are you sure you want to pick up this service?')) {
        return;
    }
	
	console.log(serviceId)

    $.ajax({
        url: 'php/pickup-service.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ service_id: serviceId }), // Use JSON.stringify
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Service picked up successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

function updateService() {
    const formData = $('#updateServiceForm').serialize();
    
    $.ajax({
        url: 'php/update-service-status.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Service updated successfully!', 'success');
                $('#updateServiceModal').modal('hide');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

function addProgressUpdate() {
    const formData = $('#addUpdateForm').serialize();
    
    $.ajax({
        url: 'php/add-service-update.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Progress update added successfully!', 'success');
                $('#addUpdateModal').modal('hide');
                // Refresh the view modal to show new updates
                if (currentServiceId) {
                    viewServiceDetails(currentServiceId);
                }
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
        }
    });
}

function loadServiceForUpdate(serviceId) {
    $.ajax({
        url: 'php/get-service-details.php',
        type: 'GET',
        data: { id: serviceId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const service = response.service || {};
                
                $('#updateServiceId').val(service.id || '');
                $('#updateServiceIdHeader').text(serviceId.toString().padStart(6, '0'));
                $('#updateStatus').val(service.status || 'assigned');
                $('#updatePriority').val(service.priority || 'Medium');
                $('#serviceNotes').val('');
                
                $('#updateServiceModal').modal('show');
            } else {
                showAlert('Error loading service data.', 'danger');
            }
        },
        error: function() {
            showAlert('Error loading service data.', 'danger');
        }
    });
}

function viewServiceDetails(serviceId) {
    $.ajax({
        url: 'php/get-service-details.php',
        type: 'GET',
        data: { id: serviceId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const service = response.service || {};
                const updates = response.updates || [];
                const documents = response.documents || [];
                
                $('#serviceIdHeader').text(serviceId.toString().padStart(6, '0'));
                
                let html = `
                    <div class="service-details">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Service Type:</strong> ${service.service_type || 'N/A'}
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong> 
                                <span class="badge status-badge status-${service.status || 'pending'}">
                                    ${(service.status || 'pending').replace('_', ' ')}
                                </span>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Vehicle:</strong> ${service.vehicle_info || 'N/A'}
                            </div>
                            <div class="col-md-6">
                                <strong>Customer:</strong> ${service.customer_name || 'N/A'}
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Priority:</strong> 
                                <span class="badge badge-${service.priority == 'High' ? 'danger' : (service.priority == 'Medium' ? 'warning' : 'info')}">
                                    ${service.priority || 'Medium'}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <strong>Preferred Date:</strong> ${service.preferred_date ? new Date(service.preferred_date).toLocaleDateString() : 'N/A'}
                            </div>
                        </div>
                        
                        ${service.description ? `
                        <div class="row mb-3">
                            <div class="col-12">
                                <strong>Description:</strong>
                                <p class="mt-1">${service.description}</p>
                            </div>
                        </div>
                        ` : ''}
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Assigned To:</strong> 
                                ${service.assigned_to_name || 'Not assigned'}
                            </div>
                            <div class="col-md-6">
                                <strong>Created:</strong> ${service.created_at ? new Date(service.created_at).toLocaleDateString() : 'N/A'}
                            </div>
                        </div>
                `;

                // Add progress updates if any
                if (updates.length > 0) {
                    html += `
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>Progress Updates</h6>
                                <div class="progress-updates" style="max-height: 300px; overflow-y: auto;">
                    `;
                    
                    updates.forEach(update => {
                        const updateTypeBadge = update.update_type === 'progress_update' ? 'primary' : 
                                              update.update_type === 'document_request' ? 'warning' :
                                              update.update_type === 'status_change' ? 'info' : 'secondary';
                        
                        html += `
                            <div class="update-item mb-3 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge badge-${updateTypeBadge}">
                                        ${(update.update_type || 'progress_update').replace('_', ' ')}
                                    </span>
                                    <small class="text-muted">${update.created_at ? new Date(update.created_at).toLocaleString() : 'Unknown date'}</small>
                                </div>
                                <p class="mb-1">${update.update_text || 'No update text'}</p>
                                <small class="text-muted">By ${update.user_name || 'Unknown'}</small>
                            </div>
                        `;
                    });
                    
                    html += `
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    No progress updates yet.
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Add documents if any
                if (documents.length > 0) {
                    html += `
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>Documents</h6>
                                <div class="document-list">
                    `;
                    
                    documents.forEach(doc => {
                        const fileName = doc.document_path ? doc.document_path.split('/').pop() : 'Unknown file';
                        html += `
                            <div class="document-item mb-2">
                                <a href="${doc.document_path || '#'}" target="_blank" class="text-primary">
                                    <i class="fas fa-file mr-2"></i>${fileName}
                                </a>
                                <small class="text-muted ml-2">(${(doc.document_type || 'unknown').replace('_', ' ')})</small>
                            </div>
                        `;
                    });
                    
                    html += `
                                </div>
                            </div>
                        </div>
                    `;
                }

                html += `</div>`;
                $('#serviceDetails').html(html);
                $('#viewServiceModal').modal('show');
            } else {
                showAlert('Error loading service details.', 'danger');
            }
        },
        error: function() {
            showAlert('Error loading service details.', 'danger');
        }
    });
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alert = $(`<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    </div>`);
    
    $('.main-content').prepend(alert);
    
    setTimeout(() => {
        alert.alert('close');
    }, 5000);
}