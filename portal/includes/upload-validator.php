<?php
// Shared helper for validating uploaded files by extension, real content
// (MIME type via finfo), and size. Use this in addition to extension checks
// since a file extension alone can be spoofed.

function validateUploadedFile($file, array $allowed_extensions, array $allowed_mime_types, $max_size) {
    if ($file['size'] > $max_size) {
        return 'File size must be less than ' . round($max_size / (1024 * 1024)) . 'MB.';
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowed_extensions)) {
        return 'File type not allowed.';
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_mime_types)) {
        return 'File content does not match an allowed file type.';
    }

    return null;
}
