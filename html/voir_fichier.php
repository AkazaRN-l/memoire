<?php
$filename = basename($_GET['file'] ?? '');
$filepath = "../uploads/" . $filename;

if (file_exists($filepath)) {
    $mime = mime_content_type($filepath);
    header("Content-Type: $mime");
    header("Content-Disposition: inline; filename=\"$filename\"");
    readfile($filepath);
    exit;
} else {
    echo "Fichier non trouvé.";
}