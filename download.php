<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Bạn phải đăng nhập mới được tải file.");
}
if (isset($_GET['file'])) {
    $filepath = $_GET['file'];
    if (strpos($filepath, 'uploads/') !== 0) {
        die("File không hợp lệ.");
    }
    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        echo "File không tồn tại.";
    }
}
?>