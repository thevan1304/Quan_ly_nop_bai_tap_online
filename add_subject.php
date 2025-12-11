<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Giảng Viên') {
    header("Location: index.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $code = $_POST['code'];
    $description = $_POST['description'];
    $teacher_id = $_SESSION['user_id'];
    $sql = "INSERT INTO subjects (name, code, description, teacher_id) 
            VALUES (:name, :code, :description, :teacher_id)";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute(['name' => $name, 'code' => $code, 'description' => $description, 'teacher_id' => $teacher_id])) {
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Lỗi tạo môn học');</script>";
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">THÊM MÔN HỌC MỚI</div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="mb-3">
                        <label>Mã môn học:</label>
                        <input type="text" name="code" class="form-control" required placeholder="VD: DC2HT36">
                    </div>
                    <div class="mb-3">
                        <label>Tên môn học:</label>
                        <input type="text" name="name" class="form-control" required placeholder="VD: Lập trình trên môi trường Web">
                    </div>
                    <div class="mb-3">
                        <label>Mô tả:</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Tạo Môn Học</button>
                    <a href="index.php" class="btn btn-secondary">Hủy</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>