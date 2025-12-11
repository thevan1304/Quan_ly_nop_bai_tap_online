<?php
require_once 'includes/db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Giảng Viên') {
    header("Location: index.php");
    exit();
}
if (isset($_GET['id'])) {
    $subject_id = $_GET['id'];
    try {
        $stmt = $conn->prepare("DELETE FROM subjects WHERE id = :id");
        $stmt->execute(['id' => $subject_id]);
        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        die("Lỗi không thể xóa: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}
?>