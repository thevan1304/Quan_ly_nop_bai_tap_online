<?php 
require_once 'includes/db.php';
require_once 'includes/header.php'; 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$sql = "SELECT subjects.*, users.fullname as teacher_name 
        FROM subjects 
        JOIN users ON subjects.teacher_id = users.id 
        ORDER BY subjects.created_at DESC";
$stmt = $conn->query($sql);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="alert alert-primary mt-3">
    Xin chào, <strong><?php echo $_SESSION['fullname']; ?></strong>! <br>
    Vai trò: <span class="badge bg-warning text-dark"><?php echo $_SESSION['role']; ?></span>
</div>
<div class="d-flex justify-content-between align-items-center mb-4 mt-4">
    <h2>Danh sách Môn học</h2>
    <?php if ($_SESSION['role'] == 'Giảng Viên'): ?>
        <a href="add_subject.php" class="btn btn-success">+ Thêm Môn học</a>
    <?php endif; ?>
</div>
<div class="row">
    <?php if (count($subjects) > 0): ?>
        <?php foreach ($subjects as $row): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><?php echo htmlspecialchars($row['name']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">Mã: <?php echo htmlspecialchars($row['code']); ?></h6>
                        <p class="card-text text-muted small">
                            GV: <?php echo htmlspecialchars($row['teacher_name']); ?>
                        </p>
                        <p class="card-text">
                            <?php echo htmlspecialchars(substr($row['description'], 0, 80)); ?>...
                        </p>
                        <div class="d-flex justify-content-between">
                            <a href="view_subject.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm flex-grow-1 me-1">
                                Vào lớp học
                            </a>
                            <?php if ($_SESSION['role'] == 'Giảng Viên'): ?>
                                <a href="delete_subject.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('CẢNH BÁO: Bạn có chắc muốn xóa môn này? Tất cả bài tập và bài nộp trong môn này sẽ bị xóa vĩnh viễn!');">
                                    Xóa
                                </a>
                            <?php endif; ?>
                        </div>
                        </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">Chưa có môn học nào.</div>
        </div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>