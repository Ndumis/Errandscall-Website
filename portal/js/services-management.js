$(document).ready(function() {
    let currentServiceId = null;
    
    // Load service statistics and vehicles
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

    // View Service Details
    $(document).on('click', '.view-service', function() {
        const serviceId = $(this).data('service-id');
        currentServiceId = serviceId;
        viewServiceDetails(serviceId);
    });

    // Update Service
    $(document).on('click', '.update-service', function() {
        const serviceId = $(this).data('service-id');
        loadServiceForUpdate(serviceId);
    });

    // Delete Service - Show confirmation modal
    $(document).on('click', '.delete-service', function() {
        const serviceId = $(this).data('service-id');
        const serviceNumber = $(this).data('service-number');
        currentServiceId = serviceId;
        
        $('#deleteServiceNumber').text(serviceNumber);
        $('#deleteReason').val('');
        $('#deleteServiceModal').modal('show');
    });

    // Confirm Delete Service
    $('#confirmDeleteBtn').on('click', function() {
        if (!currentServiceId) return;
        
        const reason = $('#deleteReason').val();
        
        $.ajax({
            url: 'php/delete-service.php',
            type: 'POST',
            data: { 
                service_id: currentServiceId,
                reason: reason
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('Service deleted successfully!', 'success');
                    $('#deleteServiceModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });

    // Upload More Documents Button
    $('#uploadMoreDocumentsBtn').on('click', function() {
        if (!currentServiceId) return;
        
        $('#uploadServiceId').text(currentServiceId.toString().padStart(6, '0'));
        $('#uploadServiceIdInput').val(currentServiceId);
        $('#documentsFilesList').hide();
        $('#selectedDocuments').empty();
        $('#uploadDocumentsForm')[0].reset();
        $('#uploadDocumentsModal').modal('show');
    });

    // Upload Documents Form
    $('#uploadDocumentsForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        if (!$('#documentsInput')[0].files.length) {
            showAlert('Please select at least one document to upload.', 'danger');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...');
        
        $.ajax({
            url: 'php/upload-service-documents.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    showAlert('Documents uploaded successfully!', 'success');
                    $('#uploadDocumentsModal').modal('hide');
                    // Refresh the view modal to show new documents
                    if (currentServiceId) {
                        viewServiceDetails(currentServiceId);
                    }
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                submitBtn.prop('disabled', false).html(originalText);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });

    // Update Service Form
    $('#updateServiceForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.ajax({
            url: 'php/update-service.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
    });

    // Initialize document upload functionality
    initDocumentUpload();
});

// Initialize document upload functionality
function initDocumentUpload() {
    const documentsUploadArea = $('#documentsUploadArea')[0];
    const documentsInput = $('#documentsInput')[0];
    
    if (!documentsUploadArea || !documentsInput) return;
    
    // Click handler
    $(documentsUploadArea).on('click', function() {
        documentsInput.click();
    });

    // Change handler
    $(documentsInput).on('change', function(e) {
        if (this.files && this.files.length > 0) {
            handleDocumentsSelection(this.files);
        }
    });

    // Drag and drop for documents
    documentsUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });

    documentsUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });

    documentsUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            documentsInput.files = files;
            handleDocumentsSelection(files);
        }
    });

    // Remove document file
    $(document).on('click', '.remove-document-file', function() {
        const indexToRemove = parseInt($(this).data('index'));
        const input = $('#documentsInput')[0];
        const dt = new DataTransfer();
        
        for (let i = 0; i < input.files.length; i++) {
            if (i !== indexToRemove) {
                dt.items.add(input.files[i]);
            }
        }
        
        input.files = dt.files;
        if (input.files.length > 0) {
            handleDocumentsSelection(input.files);
        } else {
            $('#documentsFilesList').hide();
            $('#selectedDocuments').empty();
        }
    });
}

// Handle documents selection
function handleDocumentsSelection(files) {
    if (!files || files.length === 0) {
        $('#documentsFilesList').hide();
        $('#selectedDocuments').empty();
        return;
    }
    
    let validFiles = [];
    
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const validTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg'];
        
        if (validTypes.includes(file.type) && file.size <= 10 * 1024 * 1024) {
            validFiles.push({
                file: file,
                index: i
            });
        }
    }
    
    if (validFiles.length === 0) {
        showAlert('No valid files selected. Please select PDF, PNG, or JPG files under 10MB.', 'danger');
        return;
    }
    
    $('#selectedDocuments').empty();
    validFiles.forEach((fileInfo) => {
        const file = fileInfo.file;
        const fileIcon = file.type === 'application/pdf' ? 'fa-file-pdf' : 'fa-file-image';
        
        $('#selectedDocuments').append(`
            <div class="file-item">
                <div class="file-item-info">
                    <i class="fas ${fileIcon} file-icon text-danger"></i>
                    <span>${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-document-file" data-index="${fileInfo.index}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `);
    });
    
    $('#documentsFilesList').show();
}


// Delete document function
function deleteDocument(documentId, serviceId) {
    $.ajax({
        url: 'php/delete-service-document.php',
        type: 'POST',
        data: { document_id: documentId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Document deleted successfully!', 'success');
                // Refresh the view
                viewServiceDetails(serviceId);
            } else {
                showAlert(response.message, 'danger');
            }
        },
        error: function() {
            showAlert('An error occurred. Please try again.', 'danger');
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
                // Add safe array access with null checks
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

                // Add progress updates if any - SAFE ACCESS
                if (updates.length > 0) {
                    html += `
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6>Progress Updates</h6>
                                <div class="progress-updates">
                    `;
                    
                    updates.forEach(update => {
                        html += `
                            <div class="update-item mb-3">
                                <p class="mb-1">${update.update_text || 'No update text'}</p>
                                <small class="update-meta">
                                    By ${update.user_name || 'Unknown'} • ${update.created_at ? new Date(update.created_at).toLocaleString() : 'Unknown date'}
                                </small>
                            </div>
                        `;
                    });
                    
                    html += `
                                </div>
                            </div>
                        </div>
                    `;
                }

                // Add documents if any - SAFE ACCESS
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

function loadServiceStatistics() {
    $.ajax({
        url: 'php/get-service-statistics.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#totalServices').text(response.statistics.total);
                $('#pendingServices').text(response.statistics.pending);
                $('#assignedServices').text(response.statistics.assigned);
                $('#progressServices').text(response.statistics.in_progress);
                $('#completedServices').text(response.statistics.completed);
                $('#cancelledServices').text(response.statistics.cancelled);
            }
        }
    });
}

function loadVehiclesForSelect() {
    $.ajax({
        url: 'php/get-vehicles.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<option value="">Select Vehicle</option>';
                response.vehicles.forEach(vehicle => {
                    html += `<option value="${vehicle.id}">${vehicle.make} ${vehicle.model} - ${vehicle.license_plate}</option>`;
                });
                $('#vehicleSelect').html(html);
            }
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
                // Add safe object access with null checks
                const service = response.service || {};
                
                $('#updateServiceId').val(service.id || '');
                $('#updateServiceType').val(service.service_type || '');
                $('#currentStatus').val((service.status || 'pending').replace('_', ' '));
                $('#updateStatus').val(service.status || 'pending');
                $('#assignWorker').val(service.assigned_to || '');
                $('#documentPreview').empty();
                $('#updateServiceForm')[0].reset();
                
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