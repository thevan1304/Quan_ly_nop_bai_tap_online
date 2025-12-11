<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Giảng Viên') {
    header("Location: index.php");
    exit();
}
if (!isset($_GET['subject_id'])) {
    die("Lỗi: Không xác định được môn học!");
}
$subject_id = $_GET['subject_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $deadline = $_POST['deadline'];
    $teacher_id = $_SESSION['user_id'];
    $sql = "INSERT INTO assignments (title, description, created_by, deadline, subject_id) 
            VALUES (:title, :description, :teacher_id, :deadline, :subject_id)";
    $stmt = $conn->prepare($sql);    
    if ($stmt->execute([
        'title' => $title, 
        'description' => $description, 
        'teacher_id' => $teacher_id,
        'deadline' => $deadline,
        'subject_id' => $subject_id
    ])) {
        header("Location: view_subject.php?id=" . $subject_id);
        exit();
    } else {
        $message = "Lỗi khi tạo bài tập!";
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white">GIAO BÀI TẬP MỚI</div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label>Tiêu đề bài tập:</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>                    
                    <div class="mb-3">
                        <label>Mô tả chi tiết:</label>
                        <textarea name="description" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Hạn nộp:</label>
                        <input type="datetime-local" name="deadline" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Đăng bài tập</button>
                    <a href="view_subject.php?id=<?php echo $subject_id; ?>" class="btn btn-secondary">Hủy</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>