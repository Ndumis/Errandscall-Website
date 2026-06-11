$(document).ready(function() {
    // Form submission flags to prevent duplicates
    let isSubmitting = false;

    // Load models
    $('#vehicleMake').on('change', function() {
        const makeId = $(this).val();
        loadCarModels(makeId, $('#vehicleModel'));
    });

    // Load models when make is selected (for edit modal)
    $('#editMake').on('change', function() {
        const makeId = $(this).val();
        loadCarModels(makeId, $('#editModel'));
    });

    // Add Vehicle Form
    $('#addVehicleForm').on('submit', function(e) {
        e.preventDefault();
        if (isSubmitting) return;
        
        isSubmitting = true;
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Adding...');
        
        $.ajax({
            url: 'php/add-vehicle.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                isSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    showAlert('Vehicle added successfully!', 'success');
                    $('#addVehicleModal').modal('hide');
                    $('#addVehicleForm')[0].reset();
                    $('#vehicleModel').prop('disabled', true).html('<option value="">Select Model</option>');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                isSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });

    // Edit Vehicle
    $(document).on('click', '.edit-vehicle', function() {
        const vehicleId = $(this).data('vehicle-id');
        
        $.ajax({
            url: 'php/get-vehicle.php',
            type: 'GET',
            data: { id: vehicleId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const vehicle = response.vehicle;
                    const makeId = vehicle.make_id || vehicle.make;
                    const modelId = vehicle.model_id || vehicle.model;
                    
                    $('#editVehicleId').val(vehicle.id);
                    $('#editMake').val(makeId);
                    $('#editYear').val(vehicle.year);
                    $('#editLicensePlate').val(vehicle.license_plate);
                    $('#editColor').val(vehicle.color);
                    $('#editVin').val(vehicle.vin);
                    $('#editDiscExpiry').val(vehicle.disc_expiry);
                    $('#editLicenseExpiry').val(vehicle.license_expiry);
                    
                    if (makeId) {
                        loadCarModels(makeId, $('#editModel'), modelId).then(function() {
                            $('#editVehicleModal').modal('show');
                        });
                    } else {
                        $('#editModel').html('<option value="">Select Make First</option>');
                        $('#editVehicleModal').modal('show');
                    }
                } else {
                    showAlert('Error loading vehicle data.', 'danger');
                }
            },
            error: function(xhr, status, error) {
                console.error('Edit vehicle error:', error);
                showAlert('Error loading vehicle data.', 'danger');
            }
        });
    });

    // Update Vehicle
    $('#editVehicleForm').on('submit', function(e) {
        e.preventDefault();
        if (isSubmitting) return;
        
        isSubmitting = true;
        const formData = $(this).serialize();
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Updating...');
        
        $.ajax({
            url: 'php/update-vehicle.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                isSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    showAlert('Vehicle updated successfully!', 'success');
                    $('#editVehicleModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                isSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });

    // Delete Vehicle
    $(document).on('click', '.delete-vehicle', function() {
        const vehicleId = $(this).data('vehicle-id');
        
        if (confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')) {
            $.ajax({
                url: 'php/delete-vehicle.php',
                type: 'POST',
                data: { vehicle_id: vehicleId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('Vehicle deleted successfully!', 'success');
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
    });

    // Image Upload Functionality
    $(document).on('click', '.add-image', function() {
        const vehicleId = $(this).data('vehicle-id');
        const side = $(this).data('side');
        
        $('#imageVehicleId').val(vehicleId);
        $('#imageSide').val(side);
        $('#sideLabel').text(side);
        $('#imagePreview').empty();
        $('#fileInfo').hide();
        $('#uploadButton').prop('disabled', true);
        $('#addImageForm')[0].reset();
        
        $('#addImageModal').modal('show');
    });

    // File Input Handling - SINGLE EVENT HANDLER
    $('#vehicleImageInput').on('change', function(e) {
        if (this.files && this.files[0]) {
            handleFileSelection(this.files[0], 'single');
        }
    });

    // Drag and Drop for Images - SIMPLIFIED
    const imageUploadArea = $('#imageUploadArea')[0];
    if (imageUploadArea) {
        imageUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });

        imageUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
        });

        imageUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                $('#vehicleImageInput')[0].files = files;
                handleFileSelection(files[0], 'single');
            }
        });
    }

    // Remove File
    $(document).on('click', '#removeFile', function() {
        $('#vehicleImageInput').val('');
        $('#fileInfo').hide();
        $('#imagePreview').empty();
        $('#uploadButton').prop('disabled', true);
    });

    // Add Image Form
    $('#addImageForm').on('submit', function(e) {
        e.preventDefault();
        if (isSubmitting) return;
        
        if (!$('#vehicleImageInput')[0].files[0]) {
            showAlert('Please select an image file.', 'danger');
            return;
        }
        
        isSubmitting = true;
        const formData = new FormData(this);
        const uploadBtn = $('#uploadButton');
        const originalText = uploadBtn.html();
        uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...');
        
        $.ajax({
            url: 'php/add-vehicle-image.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                isSubmitting = false;
                uploadBtn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    showAlert('Image uploaded successfully!', 'success');
                    $('#addImageModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                isSubmitting = false;
                uploadBtn.prop('disabled', false).html(originalText);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });

    // Delete Image
    $(document).on('click', '.delete-image', function() {
        const imageId = $(this).data('image-id');
        
        if (confirm('Are you sure you want to delete this image?')) {
            $.ajax({
                url: 'php/delete-vehicle-image.php',
                type: 'POST',
                data: { image_id: imageId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('Image deleted successfully!', 'success');
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
    });

    // Request Service
    $(document).on('click', '.request-service', function() {
        const vehicleId = $(this).data('vehicle-id');
        
        if ($('#requestServiceModal').length) {
            resetServiceForm();
            loadUserVehicles().then(function() {
                if (vehicleId) {
                    $('#vehicleSelect').val(vehicleId);
                    updateSelectedVehicleDisplay(vehicleId);
                }
                $('#requestServiceModal').modal('show');
            });
        } else {
            showAlert('Service request feature is not available at the moment.', 'warning');
        }
    });

    // Request Service Form
    $('#requestServiceForm').on('submit', function(e) {
        e.preventDefault();
        if (isSubmitting) return;
        
        const vehicleId = $('#vehicleSelect').val();
        if (!vehicleId) {
            showAlert('Please select a vehicle.', 'danger');
            return;
        }
        
        isSubmitting = true;
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...');
        
        $.ajax({
            url: 'php/request-service.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                isSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    showAlert('Service request submitted successfully!', 'success');
                    $('#requestServiceModal').modal('hide');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert(response.message, 'danger');
                }
            },
            error: function() {
                isSubmitting = false;
                submitBtn.prop('disabled', false).html(originalText);
                showAlert('An error occurred. Please try again.', 'danger');
            }
        });
    });

    // Initialize document upload functionality
    initDocumentUpload();

    // Pagination for the vehicles grid
    if ($('#vehiclesGrid > .col-lg-4').length > 0) {
        const vehiclesPager = createPagination({
            getItems: () => $('#vehiclesGrid > .col-lg-4'),
            paginationContainer: '#vehiclesPagination',
            rowsPerPage: 9
        });
        vehiclesPager.refresh();
    }
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

    // Drag and drop for documents - USING VANILLA JS
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

// File Selection Handler
function handleFileSelection(file, type) {
    if (!file) return;

    if (type === 'single') {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            showAlert('Please select a valid image file (JPEG, PNG, GIF).', 'danger');
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            showAlert('File size must be less than 5MB.', 'danger');
            return;
        }
        
        $('#fileName').text(file.name);
        $('#fileInfo').show();
        $('#uploadButton').prop('disabled', false);
        
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#imagePreview').html(`<img src="${e.target.result}" class="img-fluid rounded shadow" alt="Preview">`);
        };
        reader.readAsDataURL(file);
    }
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

// Load Car Models
function loadCarModels(makeId, modelSelect, selectedModelId = null) {
    return new Promise(function(resolve, reject) {
        if (!makeId) {
            modelSelect.html('<option value="">Select Model</option>');
            modelSelect.prop('disabled', true);
            resolve();
            return;
        }
        
        modelSelect.prop('disabled', true);
        modelSelect.html('<option value="">Loading models...</option>');
        
        $.ajax({
            url: 'php/get-car-models.php',
            type: 'GET',
            data: { make_id: makeId },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    modelSelect.html('<option value="">Select Model</option>');
                    response.data.forEach(function(model) {
                        const yearRange = model.year_to ? 
                            `${model.year_from}-${model.year_to}` : 
                            `${model.year_from}+`;
                        const option = $('<option>', {
                            value: model.id,
                            text: `${model.name} (${yearRange})`
                        });
                        if (selectedModelId && model.id == selectedModelId) {
                            option.prop('selected', true);
                        }
                        modelSelect.append(option);
                    });
                    modelSelect.prop('disabled', false);
                } else {
                    modelSelect.html('<option value="">No models found</option>');
                }
                resolve();
            },
            error: function() {
                modelSelect.html('<option value="">Error loading models</option>');
                resolve();
            }
        });
    });
}

// Load user's vehicles for dropdown
function loadUserVehicles() {
    return new Promise(function(resolve, reject) {
        $('#vehicleSelect').html('<option value="">Loading vehicles...</option>');
        
        $.ajax({
            url: 'php/get-user-vehicles.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.vehicles.length > 0) {
                    $('#vehicleSelect').html('<option value="">Select Vehicle</option>');
                    response.vehicles.forEach(function(vehicle) {
                        const option = $('<option>', {
                            value: vehicle.id,
                            text: `${vehicle.year} ${vehicle.make_name} ${vehicle.model_name} - ${vehicle.license_plate}`,
                            'data-vehicle-info': JSON.stringify(vehicle)
                        });
                        $('#vehicleSelect').append(option);
                    });
                } else {
                    $('#vehicleSelect').html('<option value="">No vehicles available</option>');
                }
                resolve();
            },
            error: function() {
                $('#vehicleSelect').html('<option value="">Error loading vehicles</option>');
                resolve();
            }
        });
    });
}

// Update selected vehicle display
function updateSelectedVehicleDisplay(vehicleId) {
    const selectedOption = $('#vehicleSelect option:selected');
    const vehicleInfo = selectedOption.data('vehicle-info');
    
    if (vehicleInfo && vehicleId) {
        $('#selectedVehicleInfo').html(`
            <div class="d-flex align-items-center justify-content-between">
                <div class="text-left">
                    <h6 class="mb-1">${vehicleInfo.year} ${vehicleInfo.make_name} ${vehicleInfo.model_name}</h6>
                    <p class="mb-1 small text-muted">License: ${vehicleInfo.license_plate}</p>
                    <p class="mb-0 small text-muted">VIN: ${vehicleInfo.vin || 'N/A'}</p>
                </div>
                <div class="text-right">
                    <span class="badge badge-primary">${vehicleInfo.color || 'N/A'}</span>
                </div>
            </div>
        `);
    } else {
        $('#selectedVehicleInfo').html(`
            <i class="fas fa-car fa-2x text-muted mb-2"></i>
            <p class="mb-0 text-muted">No vehicle selected</p>
        `);
    }
}

// Reset service form
function resetServiceForm() {
    $('#requestServiceForm')[0].reset();
    $('#documentsFilesList').hide();
    $('#selectedDocuments').empty();
    $('#selectedVehicleInfo').html(`
        <i class="fas fa-car fa-2x text-muted mb-2"></i>
        <p class="mb-0 text-muted">No vehicle selected</p>
    `);
}

// Show Alert
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