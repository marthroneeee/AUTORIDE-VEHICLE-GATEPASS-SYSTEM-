<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    exit('Access denied.');
}

if (!isset($_GET['file'])) {
    http_response_code(400);
    exit('No file specified.');
}

$filename = basename($_GET['file']); // prevent directory traversal
$filepath = __DIR__ . "/uploads/" . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    exit('File not found.');
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $filepath);
finfo_close($finfo);

header("Content-Type: $mime");
header("Content-Disposition: inline; filename=\"$filename\"");
readfile($filepath);
exit;

echo "Looking for: " . $filepath;
exit;