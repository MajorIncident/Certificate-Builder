<?php
$ds = DIRECTORY_SEPARATOR;  // Directory separator for cross-platform compatibility
$storeFolder = 'logos';  // Directory to store uploaded logos

if (!empty($_FILES)) {
    $tempFile = $_FILES['file']['tmp_name'];  // Temporary file path

    // Ensure the logos directory exists
    if (!is_dir($storeFolder)) {
        mkdir($storeFolder, 0755, true);
    }

    $targetPath = dirname(__FILE__) . $ds . $storeFolder . $ds;  // Target directory path
    $targetFile = $targetPath . basename($_FILES['file']['name']);  // Target file path with basename for security

    // Validate file type (optional, but recommended for security)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    $fileType = mime_content_type($tempFile);

    if (in_array($fileType, $allowedTypes)) {
        // Move the file to the target directory
        if (move_uploaded_file($tempFile, $targetFile)) {
            echo "File uploaded successfully.";
        } else {
            echo "File upload failed.";
        }
    } else {
        echo "Invalid file type.";
    }
}
?>
