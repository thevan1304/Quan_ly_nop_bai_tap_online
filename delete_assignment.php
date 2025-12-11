<?php
require_once 'includes/db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Giảng Viên') {
    header("Location: index.php");
    exit();
}
if (isset($_GET['id'])) {
    $assignment_id = $_GET['id'];
    try {
        $stmt = $conn->prepare("SELECT subject_id FROM assignments WHERE id = :id");
        $stmt->execute(['id' => $assignment_id]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$assignment) {
            die("Bài tập không tồn tại.");
        }
        $subject_id = $assignment['subject_id'];
        $stmt = $conn->prepare("DELETE FROM submissions WHERE assignment_id = :id");
        $stmt->execute(['id' => $assignment_id]);
        $stmt = $conn->prepare("DELETE FROM assignments WHERE id = :id");
        $stmt->execute(['id' => $assignment_id]);
        header("Location: view_subject.php?id=" . $subject_id);
        exit();
    } catch (Exception $e) {
        die("Lỗi không thể xóa: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}
?>