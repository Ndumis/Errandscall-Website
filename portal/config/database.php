<?php
// Production-safe error handling: never display raw errors to users,
// but keep logging them so issues can still be diagnosed.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Database configuration
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'errandscall');
}

// Create connection
if (!function_exists('getDBConnection')) {
    function getDBConnection() {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            die("A database error occurred. Please try again later.");
        }
        
        return $conn;
    }
}

// Create tables if not exists
if (!function_exists('createTables')) {
	function createTables($conn) {
		// Users table with roles
		$users_sql = "CREATE TABLE IF NOT EXISTS users (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			id_number VARCHAR(50) UNIQUE NOT NULL,
			fullname VARCHAR(100) NOT NULL,
			email VARCHAR(100) UNIQUE NOT NULL,
			phone VARCHAR(20) NOT NULL,
			dob DATE NOT NULL,
			password VARCHAR(255) NOT NULL,
			role ENUM('admin', 'manager', 'worker', 'customer') DEFAULT 'customer',
			reset_token VARCHAR(255) DEFAULT NULL,
			reset_expiry DATETIME DEFAULT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		)";
		
		// Vehicles table
		$vehicles_sql = "CREATE TABLE IF NOT EXISTS vehicles (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			user_id INT(11) NOT NULL,
			make VARCHAR(100) NOT NULL,
			model VARCHAR(100) NOT NULL,
			year INT(4) NOT NULL,
			license_plate VARCHAR(20) UNIQUE NOT NULL,
			vin VARCHAR(50),
			color VARCHAR(30),
			disc_expiry DATE NOT NULL,
			license_expiry DATE NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
		)";
		
		// Vehicle images table
		$vehicle_images_sql = "CREATE TABLE IF NOT EXISTS vehicle_images (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			vehicle_id INT(11) NOT NULL,
			image_path VARCHAR(255) NOT NULL,
			image_side ENUM('front', 'back', 'left', 'right', 'interior', 'engine') NOT NULL,
			uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
		)";
		
		// Services table
		$services_sql = "CREATE TABLE IF NOT EXISTS services (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			service_type VARCHAR(100) NOT NULL,
			description TEXT,
			status ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
			user_id INT(11) NOT NULL,
			vehicle_id INT(11) NOT NULL,
			assigned_to INT(11) DEFAULT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
			FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
			FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
		)";
		
		// Service documents table
		$service_documents_sql = "CREATE TABLE IF NOT EXISTS service_documents (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			service_id INT(11) NOT NULL,
			document_path VARCHAR(255) NOT NULL,
			document_type ENUM('user_uploaded', 'worker_requested', 'completion_doc') DEFAULT 'user_uploaded',
			uploaded_by INT(11) NOT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
			FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
		)";
		
		// Service updates table
		$service_updates_sql = "CREATE TABLE IF NOT EXISTS service_updates (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			service_id INT(11) NOT NULL,
			user_id INT(11) NOT NULL,
			update_text TEXT NOT NULL,
			update_type ENUM('status_change', 'document_request', 'progress_update', 'note') DEFAULT 'progress_update',
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
			FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
		)";
		
		$activity_log_sql = "CREATE TABLE IF NOT EXISTS activity_log (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			user_id INT(11) NOT NULL,
			activity_type VARCHAR(100) NOT NULL,
			description TEXT NOT NULL,
			ip_address VARCHAR(45),
			user_agent TEXT,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
		)";
		
		$car_makes_sql = "CREATE TABLE IF NOT EXISTS car_makes (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(100) NOT NULL UNIQUE,
			country VARCHAR(50),
			founded_year INT(4),
			is_active BOOLEAN DEFAULT TRUE,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
		)";

		$car_models_sql = "CREATE TABLE IF NOT EXISTS car_models (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			make_id INT(11) NOT NULL,
			name VARCHAR(100) NOT NULL,
			year_from INT(4),
			year_to INT(4),
			vehicle_type ENUM('sedan', 'suv', 'truck', 'coupe', 'hatchback', 'convertible', 'van', 'wagon', 'minivan', 'pickup') DEFAULT 'sedan',
			is_active BOOLEAN DEFAULT TRUE,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			FOREIGN KEY (make_id) REFERENCES car_makes(id) ON DELETE CASCADE,
			UNIQUE KEY unique_make_model (make_id, name, year_from)
		)";

		// Service ratings table
		$service_ratings_sql = "CREATE TABLE IF NOT EXISTS service_ratings (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			service_id INT(11) NOT NULL,
			user_id INT(11) NOT NULL,
			worker_id INT(11) NOT NULL,
			rating TINYINT(1) NOT NULL,
			comment TEXT,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			UNIQUE KEY unique_service_rating (service_id, user_id),
			FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
			FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
			FOREIGN KEY (worker_id) REFERENCES users(id) ON DELETE CASCADE
		)";

		// Worker ratings summary table (aggregated from service_ratings)
		$worker_ratings_summary_sql = "CREATE TABLE IF NOT EXISTS worker_ratings_summary (
			worker_id INT(11) PRIMARY KEY,
			total_ratings INT(11) NOT NULL DEFAULT 0,
			average_rating DECIMAL(3,2) NOT NULL DEFAULT 0,
			FOREIGN KEY (worker_id) REFERENCES users(id) ON DELETE CASCADE
		)";

		// Email templates table
		$email_templates_sql = "CREATE TABLE IF NOT EXISTS email_templates (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(100) NOT NULL,
			subject VARCHAR(255) NOT NULL,
			body TEXT NOT NULL,
			variables VARCHAR(255) DEFAULT NULL,
			created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
		)";

		$tables = [
			$users_sql,
			$vehicles_sql,
			$vehicle_images_sql,
			$services_sql,
			$service_documents_sql,
			$service_updates_sql,
			$activity_log_sql,
			$car_makes_sql,
			$car_models_sql,
			$service_ratings_sql,
			$worker_ratings_summary_sql,
			$email_templates_sql
		];
		
		foreach ($tables as $sql) {
			if (!$conn->query($sql)) {
				error_log("Error creating table: " . $conn->error);
				die("A database error occurred while initializing the application.");
			}
		}

		// Add KYC / registration columns to users table if missing
		// (MySQL does not support ALTER TABLE ... ADD COLUMN IF NOT EXISTS)
		$kyc_columns = [
			'is_foreign_national' => "TINYINT(1) NOT NULL DEFAULT 0",
			'initials' => "VARCHAR(10) DEFAULT NULL",
			'firstnames' => "VARCHAR(100) DEFAULT NULL",
			'surname' => "VARCHAR(100) DEFAULT NULL",
			'id_document_path' => "VARCHAR(255) DEFAULT NULL",
			'address_line1' => "VARCHAR(150) DEFAULT NULL",
			'address_line2' => "VARCHAR(150) DEFAULT NULL",
			'address_line3' => "VARCHAR(150) DEFAULT NULL",
			'address_line4' => "VARCHAR(150) DEFAULT NULL",
			'postal_code' => "VARCHAR(10) DEFAULT NULL",
		];

		$existing_columns = [];
		$columns_result = $conn->query("SHOW COLUMNS FROM users");
		while ($col = $columns_result->fetch_assoc()) {
			$existing_columns[$col['Field']] = true;
		}

		foreach ($kyc_columns as $column_name => $definition) {
			if (!isset($existing_columns[$column_name])) {
				$conn->query("ALTER TABLE users ADD COLUMN $column_name $definition");
			}
		}

		// Create default admin user if not exists
		$check_admin = $conn->query("SELECT id FROM users WHERE role = 'admin'");
		if ($check_admin->num_rows === 0) {
			$hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
			$conn->query("INSERT INTO users (id_number, fullname, email, phone, dob, password, role)
						 VALUES ('ADMIN001', 'System Administrator', 'admin@errandscall.com', '0000000000', '2000-01-01', '$hashed_password', 'admin')");
		}

		// Seed default email templates if none exist
		$check_templates = $conn->query("SELECT id FROM email_templates");
		if ($check_templates->num_rows === 0) {
			$default_templates = [
				[
					'Service Status Update',
					'Update on your {service_type} request',
					"Hi {customer_name},\n\nThere's an update on your {service_type} request for {vehicle_info}.\n\nNew status: {service_status}\n{update_text}\n\nThank you for choosing ErrandsCall.",
					'{customer_name}, {service_type}, {vehicle_info}, {service_status}, {update_text}'
				],
				[
					'Service Assigned',
					'Your {service_type} request has been assigned',
					"Hi {customer_name},\n\nYour {service_type} request for {vehicle_info} has been assigned to {worker_name}, who will be handling it from here.\n\nThank you for choosing ErrandsCall.",
					'{customer_name}, {service_type}, {vehicle_info}, {worker_name}'
				],
				[
					'Service Completed',
					'Your {service_type} has been completed',
					"Hi {customer_name},\n\nGreat news! Your {service_type} request for {vehicle_info} has been completed.\n\nWe'd love to hear about your experience - please take a moment to rate our service in your portal.\n\nThank you for choosing ErrandsCall.",
					'{customer_name}, {service_type}, {vehicle_info}'
				]
			];

			$insert_template = $conn->prepare("INSERT INTO email_templates (name, subject, body, variables) VALUES (?, ?, ?, ?)");
			foreach ($default_templates as $template) {
				$insert_template->bind_param("ssss", $template[0], $template[1], $template[2], $template[3]);
				$insert_template->execute();
			}
			$insert_template->close();
		}
	}

	// Initialize database and tables
	$conn = getDBConnection();
	createTables($conn);
}
?>