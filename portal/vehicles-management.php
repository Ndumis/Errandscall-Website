<?php
$page_title = "Vehicle Management | ErrandsCall Portal";
include('config/database.php');
include('includes/auth-check.php');
include('includes/header.php');
include('includes/sidebar.php');

// Get vehicles based on user role
$conn = getDBConnection();
if (isCustomer()) {
    $stmt = $conn->prepare("SELECT v.*, COUNT(vi.id) as image_count 
                           FROM vehicles v 
                           LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id 
                           WHERE v.user_id = ? 
                           GROUP BY v.id 
                           ORDER BY v.created_at DESC");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("SELECT v.*, u.fullname as owner_name, COUNT(vi.id) as image_count 
                           FROM vehicles v 
                           LEFT JOIN vehicle_images vi ON v.id = vi.vehicle_id 
                           JOIN users u ON v.user_id = u.id 
                           GROUP BY v.id 
                           ORDER BY v.created_at DESC");
}
$stmt->execute();
$vehicles_result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gradient">Vehicle Management</h1>
        <?php if (isCustomer()): ?>
        <button class="btn btn-gradient" data-toggle="modal" data-target="#addVehicleModal">
            <i class="fas fa-plus mr-2"></i>Add New Vehicle
        </button>
        <?php endif; ?>
    </div>

    <!-- Vehicles Grid -->
    <div class="row" id="vehiclesGrid">
        <?php if ($vehicles_result->num_rows > 0): ?>
            <?php while($vehicle = $vehicles_result->fetch_assoc()): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card vehicle-card h-100">
                    <div class="card-body">
                        <!-- Vehicle Images -->
                        <div class="vehicle-images-preview mb-3">
                            <?php
                            $conn = getDBConnection();
                            $img_stmt = $conn->prepare("SELECT * FROM vehicle_images WHERE vehicle_id = ? ORDER BY image_side");
                            $img_stmt->bind_param("i", $vehicle['id']);
                            $img_stmt->execute();
                            $images_result = $img_stmt->get_result();
                            $images = [];
                            while($img = $images_result->fetch_assoc()) {
                                $images[$img['image_side']] = $img;
                            }
                            $img_stmt->close();
                            $conn->close();
                            ?>
                            
                            <div class="vehicle-images-grid">
                                <?php 
                                $sides = ['front', 'back', 'left', 'right', 'interior', 'engine'];
                                foreach($sides as $side): 
                                ?>
                                <div class="vehicle-image-item">
                                    <?php if (isset($images[$side])): ?>
                                        <img src="<?php echo $images[$side]['image_path']; ?>" alt="<?php echo $side; ?> view" class="img-fluid">
                                        <div class="image-side-label"><?php echo $side; ?></div>
                                        <?php if (isCustomer()): ?>
                                        <div class="image-actions">
                                            <button class="btn btn-sm btn-danger delete-image" data-image-id="<?php echo $images[$side]['id']; ?>" title="Delete Image">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="no-image-placeholder d-flex align-items-center justify-content-center bg-light" style="height: 100px;">
                                            <small class="text-muted">No <?php echo $side; ?> image</small>
                                        </div>
                                        <?php if (isCustomer()): ?>
                                        <div class="image-actions">
                                            <button class="btn btn-sm btn-primary add-image" data-vehicle-id="<?php echo $vehicle['id']; ?>" data-side="<?php echo $side; ?>" title="Add Image">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Vehicle Details -->
                        <h5 class="card-title"><?php echo $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')'; ?></h5>
                        <div class="vehicle-details">
                            <p class="mb-1"><strong>License Plate:</strong> <?php echo $vehicle['license_plate']; ?></p>
                            <p class="mb-1"><strong>Color:</strong> <?php echo $vehicle['color'] ?: 'N/A'; ?></p>
                            <p class="mb-1"><strong>VIN:</strong> <?php echo $vehicle['vin'] ?: 'N/A'; ?></p>
                            <p class="mb-1"><strong>Disc Expiry:</strong> 
                                <span class="<?php echo (strtotime($vehicle['disc_expiry']) < strtotime('+30 days')) ? 'text-danger' : ''; ?>">
                                    <?php echo date('M j, Y', strtotime($vehicle['disc_expiry'])); ?>
                                </span>
                            </p>
                            <p class="mb-1"><strong>License Expiry:</strong> 
                                <span class="<?php echo (strtotime($vehicle['license_expiry']) < strtotime('+30 days')) ? 'text-danger' : ''; ?>">
                                    <?php echo date('M j, Y', strtotime($vehicle['license_expiry'])); ?>
                                </span>
                            </p>
                            <?php if (!isCustomer()): ?>
                            <p class="mb-1"><strong>Owner:</strong> <?php echo $vehicle['owner_name']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <?php if (isCustomer()): ?>
                            <button class="btn btn-sm btn-outline-primary edit-vehicle" data-vehicle-id="<?php echo $vehicle['id']; ?>">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-vehicle" data-vehicle-id="<?php echo $vehicle['id']; ?>">
                                <i class="fas fa-trash mr-1"></i>Delete
                            </button>
                            <button class="btn btn-sm btn-gradient request-service" data-vehicle-id="<?php echo $vehicle['id']; ?>">
                                <i class="fas fa-tasks mr-1"></i>Request Service
                            </button>
                            <?php else: ?>
                            <span class="text-muted">View Only</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-car fa-4x text-muted mb-3"></i>
                    <h4>No Vehicles Found</h4>
                    <p class="text-muted mb-4"><?php echo isCustomer() ? 'You haven\'t added any vehicles yet.' : 'No vehicles have been registered yet.'; ?></p>
                    <?php if (isCustomer()): ?>
                    <button class="btn btn-gradient btn-lg" data-toggle="modal" data-target="#addVehicleModal">
                        <i class="fas fa-plus mr-2"></i>Add Your First Vehicle
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($vehicles_result->num_rows > 0): ?>
    <nav aria-label="Vehicles pagination">
        <ul class="pagination justify-content-center mt-3" id="vehiclesPagination"></ul>
    </nav>
    <?php endif; ?>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Vehicle</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addVehicleForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-row">
						<div class="form-group col-md-6">
							<label>Make *</label>
							<select class="form-control" name="make_id" id="vehicleMake" required>
								<option value="">Select Make</option>
								<?php
								$conn = getDBConnection();
								$makes_sql = "SELECT id, name FROM car_makes WHERE is_active = TRUE ORDER BY name";
								$makes_result = $conn->query($makes_sql);
								while($make = $makes_result->fetch_assoc()): ?>
								<option value="<?php echo $make['id']; ?>"><?php echo htmlspecialchars($make['name']); ?></option>
								<?php endwhile; 
								$conn->close();
								?>
							</select>
						</div>
						<div class="form-group col-md-6">
							<label>Model *</label>
							<select class="form-control" name="model_id" id="vehicleModel" required disabled>
								<option value="">Select Model</option>
							</select>
						</div>
					</div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Year *</label>
                            <input type="number" class="form-control" name="year" min="1900" max="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>License Plate *</label>
                            <input type="text" class="form-control" name="license_plate" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Color</label>
                            <input type="text" class="form-control" name="color">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-12 col-lg-6">
                            <label>VIN Number</label>
                            <input type="text" class="form-control" name="vin">
                        </div>
                        <div class="form-group col-6 col-lg-3">
                            <label>Disc Expiry *</label>
                            <input type="date" class="form-control" name="disc_expiry" required>
                        </div>
                        <div class="form-group col-6 col-lg-3">
                            <label>License Expiry *</label>
                            <input type="date" class="form-control" name="license_expiry" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Vehicle Images</label>
                        <p class="text-muted small">You can add images for different sides of the vehicle after creating it.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Add Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Image Modal -->
<div class="modal fade" id="addImageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Vehicle Image</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="addImageForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="vehicle_id" id="imageVehicleId">
                    <input type="hidden" name="image_side" id="imageSide">
                    
                    <div class="form-group">
                        <label>Image for <span id="sideLabel" class="font-weight-bold text-primary"></span> view *</label>
                        
                        <!-- New Upload Area with Better Styling -->
                        <div class="upload-container">
                            <div class="upload-area" id="imageUploadArea">
                                <div class="upload-content">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h5>Click to upload or drag and drop</h5>
                                    <p class="text-muted mb-2">PNG, JPG, JPEG up to 5MB</p>
                                    <button type="button" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-folder-open mr-2"></i>Browse Files
                                    </button>
                                </div>
                                <input type="file" name="vehicle_image" id="vehicleImageInput" accept="image/*" required>
                            </div>
                            
                            <!-- File Info -->
                            <div id="fileInfo" class="file-info mt-3" style="display: none;">
                                <div class="alert alert-info d-flex align-items-center">
                                    <i class="fas fa-file-image mr-2"></i>
                                    <span id="fileName"></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger ml-auto" id="removeFile">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Image Preview -->
                        <div id="imagePreview" class="mt-3 text-center"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient" id="uploadButton" disabled>
                        <i class="fas fa-upload mr-2"></i>Upload Image
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Vehicle Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Vehicle</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editVehicleForm">
                <div class="modal-body">
                    <input type="hidden" name="vehicle_id" id="editVehicleId">
                    <!-- Form fields same as add vehicle form -->
                    <div class="form-row">
						<div class="form-group col-md-6">
							<label>Make *</label>
							<select class="form-control" name="make_id" id="editMake" required>
								<option value="">Select Make</option>
								<?php
								$conn = getDBConnection();
								$makes_sql = "SELECT id, name FROM car_makes WHERE is_active = TRUE ORDER BY name";
								$makes_result = $conn->query($makes_sql);
								while($make = $makes_result->fetch_assoc()): ?>
								<option value="<?php echo $make['id']; ?>"><?php echo htmlspecialchars($make['name']); ?></option>
								<?php endwhile; 
								$conn->close();
								?>
							</select>
						</div>
						<div class="form-group col-md-6">
							<label>Model *</label>
							<select class="form-control" name="model_id" id="editModel" required disabled>
								<option value="">Select Model</option>
							</select>
						</div>
					</div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Year *</label>
                            <input type="number" class="form-control" name="year" id="editYear" min="1900" max="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>License Plate *</label>
                            <input type="text" class="form-control" name="license_plate" id="editLicensePlate" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Color</label>
                            <input type="text" class="form-control" name="color" id="editColor">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-12 col-lg-6">
                            <label>VIN Number</label>
                            <input type="text" class="form-control" name="vin" id="editVin">
                        </div>
                        <div class="form-group col-6 col-lg-3">
                            <label>Disc Expiry *</label>
                            <input type="date" class="form-control" name="disc_expiry" id="editDiscExpiry" required>
                        </div>
                        <div class="form-group col-6 col-lg-3">
                            <label>License Expiry *</label>
                            <input type="date" class="form-control" name="license_expiry" id="editLicenseExpiry" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">Update Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Service Modal -->
<div class="modal fade" id="requestServiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request New Service</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="requestServiceForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Selected Vehicle Display -->
                    <div class="form-group">
                        <label>Selected Vehicle</label>
                        <div class="selected-vehicle-display p-3 border rounded bg-light">
                            <div id="selectedVehicleInfo" class="text-center">
                                <i class="fas fa-car fa-2x text-muted mb-2"></i>
                                <p class="mb-0 text-muted">No vehicle selected</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Service Type *</label>
                        <select class="form-control" name="service_type" required>
                            <option value="">Select Service Type</option>
                            <option value="Vehicle License Renewal">Vehicle License Renewal</option>
                            <option value="Business Vehicle Registration">Business Vehicle Registration</option>
                            <option value="Vehicle De-registration">Vehicle De-registration</option>
                            <option value="Change of Ownership">Change of Ownership</option>
                            <option value="Roadworthy Certificates">Roadworthy Certificates</option>
                            <option value="Roadworthy">Roadworthy</option>
                            <option value="Police Clearance">Police Clearance</option>
                            <option value="Personalised Number Plates">Personalised Number Plates</option>
                            <option value="Change of Province">Change of Province</option>
                            <option value="Number Plate Manufacturing">Number Plate Manufacturing</option>
                            <option value="Vehicle Sold / Trade In">Vehicle Sold / Trade In</option>
                            <option value="Change of Title Holder">Change of Title Holder</option>
                            <option value="Other Services">Other Services</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Select Vehicle *</label>
                        <select class="form-control" name="vehicle_id" required id="vehicleSelect">
                            <option value="">Select Vehicle</option>
                            <!-- Vehicles will be populated via AJAX -->
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority *</label>
                                <select class="form-control" name="priority" required>
                                    <option value="">Select Priority</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Preferred Service Date *</label>
                                <input type="date" class="form-control" name="preferred_date" required 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <textarea class="form-control" name="description" rows="3" 
                                  placeholder="Provide any additional details about the service required..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Supporting Documents</label>
                        <div class="upload-area" id="documentsUploadArea">
                            <div class="upload-content">
                                <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary"></i>
                                <h5>Drag & Drop Files Here</h5>
                                <p class="text-muted">or click to browse</p>
                                <p class="small text-muted mb-0">PDF, PNG, JPG, JPEG up to 10MB each</p>
                            </div>
                            <input type="file" name="documents[]" id="documentsInput" 
                                   accept=".pdf,.png,.jpg,.jpeg" multiple style="display: none;">
                        </div>
                        
                        <!-- Selected Files List -->
                        <div id="documentsFilesList" class="mt-3" style="display: none;">
                            <h6>Selected Files:</h6>
                            <div id="selectedDocuments" class="selected-files"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

<script src="js/vehicles-management.js"></script>