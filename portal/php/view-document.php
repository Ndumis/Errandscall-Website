<?php
// Serves a service document only to users allowed to see that service.
// The uploads/documents folder is blocked from direct web access (see its .htaccess),
// so every document link goes through here. Access rules match get-service-details.php:
// customers may only open documents on their own services; staff may open any.
require_once('../config/database.php');
require_once('../includes/session-config.php');

startSecureSession();

function denyDocument($code, $message) {
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $message;
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_id'])) {
    denyDocument(403, 'Please log in to view this document.');
}

$doc_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($doc_id <= 0) {
    denyDocument(400, 'Invalid document.');
}

$user_id = (int) $_SESSION['user_id'];
$conn = getDBConnection();

// The user must still exist and be active
$user_stmt = $conn->prepare("SELECT role FROM users WHERE id = ? AND status = 'active'");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

if (!$user) {
    $conn->close();
    denyDocument(403, 'Access denied.');
}

$doc_stmt = $conn->prepare("SELECT sd.document_path, s.user_id AS owner_id
                            FROM service_documents sd
                            JOIN services s ON sd.service_id = s.id
                            WHERE sd.id = ?");
$doc_stmt->bind_param("i", $doc_id);
$doc_stmt->execute();
$doc = $doc_stmt->get_result()->fetch_assoc();
$doc_stmt->close();
$conn->close();

if (!$doc || ($user['role'] === 'customer' && (int) $doc['owner_id'] !== $user_id)) {
    denyDocument(404, 'Document not found.');
}

// All service documents live directly in the documents folder. Older rows store the
// path as '../assets/...' and newer ones as 'assets/...', so use only the file name.
$docs_dir = realpath(__DIR__ . '/../assets/uploads/documents');
$file_path = $docs_dir === false ? false : realpath($docs_dir . DIRECTORY_SEPARATOR . basename($doc['document_path']));

if ($file_path === false || dirname($file_path) !== $docs_dir || !is_file($file_path)) {
    denyDocument(404, 'Document not found.');
}

$mime_types = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
];
$ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
$mime = $mime_types[$ext] ?? 'application/octet-stream';
$disposition = isset($mime_types[$ext]) ? 'inline' : 'attachment';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($file_path));
header('Content-Disposition: ' . $disposition . '; filename="' . basename($file_path) . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
header('X-Robots-Tag: noindex, nofollow');
readfile($file_path);
exit;
