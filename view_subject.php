<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$subject_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM subjects WHERE id = :id");
$stmt->execute(['id' => $subject_id]);
$subject = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt = $conn->prepare("SELECT * FROM assignments WHERE subject_id = :sid ORDER BY created_at DESC");
$stmt->execute(['sid' => $subject_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo $subject['code']; ?></li>
  </ol>
</nav>
<div class="card mb-4 bg-light">
    <div class="card-body">
        <h2 class="text-primary"><?php echo $subject['name']; ?> (<?php echo $subject['code']; ?>)</h2>
        <p><?php echo $subject['description']; ?></p>
    </div>
</div>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4>Danh sách Bài tập</h4>
    <?php if ($_SESSION['role'] == 'Giảng Viên'): ?>
        <a href="add_assignment.php?subject_id=<?php echo $subject['id']; ?>" class="btn btn-success">+ Giao bài tập mới</a>
    <?php endif; ?>
</div>
<div class="list-group">
    <?php if (count($assignments) > 0): ?>
        <?php foreach ($assignments as $row): ?>
            <div class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div>
                        <a href="view_assignment.php?id=<?php echo $row['id']; ?>" class="text-decoration-none">
                            <h5 class="mb-1"><?php echo htmlspecialchars($row['title']); ?></h5>
                        </a>
                        <small class="text-danger">Hạn: <?php echo date("H:i d/m/Y", strtotime($row['deadline'])); ?></small>
                        <p class="mb-1 text-muted"><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...</p>
                    </div>

                    <div class="ms-3">
                        <a href="view_assignment.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">
                            Xem
                        </a>

                        <?php if ($_SESSION['role'] == 'Giảng Viên'): ?>
                            <a href="delete_assignment.php?id=<?php echo $row['id']; ?>" 
                               class="btn btn-danger btn-sm ms-1"
                               onclick="return confirm('Bạn chắc chắn muốn xóa bài tập này? Mọi bài nộp của sinh viên cũng sẽ bị xóa!');">
                                Xóa
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning">Chưa có bài tập nào trong môn này.</div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>