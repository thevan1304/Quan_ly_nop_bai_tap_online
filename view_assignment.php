<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$assignment_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$message = "";
if ($user_role == 'Sinh Viên' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['homework_file'])) {
    $file = $_FILES['homework_file'];
    $filename = time() . "_" . $file['name'];
    $target_file = "uploads/" . $filename;
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $sql = "INSERT INTO submissions (assignment_id, student_id, file_path) VALUES (:aid, :uid, :path)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['aid' => $assignment_id, 'uid' => $user_id, 'path' => $target_file]);
        $message = "Nộp bài thành công!";
    } else {
        $message = "Lỗi upload file!";
    }
}
if ($user_role == 'Giảng Viên' && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade'])) {
    $submission_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];

    $sql = "UPDATE submissions SET grade = :grade, feedback = :feedback WHERE id = :sid";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['grade' => $grade, 'feedback' => $feedback, 'sid' => $submission_id]);
    $message = "Đã cập nhật điểm!";
}
$stmt = $conn->prepare("SELECT * FROM assignments WHERE id = :id");
$stmt->execute(['id' => $assignment_id]);
$assignment = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user_role == 'Giảng Viên') {
    $sql = "SELECT submissions.*, users.fullname 
            FROM submissions 
            JOIN users ON submissions.student_id = users.id 
            WHERE assignment_id = :aid";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['aid' => $assignment_id]);
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "SELECT * FROM submissions WHERE assignment_id = :aid AND student_id = :uid";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['aid' => $assignment_id, 'uid' => $user_id]);
    $my_submission = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">ĐỀ BÀI</div>
            <div class="card-body">
                <h4><?php echo htmlspecialchars($assignment['title']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                <hr>
                <small class="text-danger">Hạn nộp: <?php echo date("H:i d/m/Y", strtotime($assignment['deadline'])); ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($user_role == 'Sinh Viên'): ?>
            <div class="card">
                <div class="card-header bg-primary text-white">KHU VỰC NỘP BÀI</div>
                <div class="card-body">
                    <?php if ($my_submission): ?>
                        <div class="alert alert-success">
                            Bạn đã nộp bài lúc: <?php echo $my_submission['submitted_at']; ?> <br>
                            File đã nộp: <strong><?php echo basename($my_submission['file_path']); ?></strong>
                        </div>
                        <?php if ($my_submission['grade'] !== null): ?>
                            <div class="alert alert-warning">
                                <strong>Điểm số: <?php echo $my_submission['grade']; ?>/10</strong> <br>
                                Lời phê: <?php echo htmlspecialchars($my_submission['feedback']); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Đang chờ giảng viên chấm điểm</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>Chọn file bài làm (Ảnh chụp, Word, PDF...):</label>
                                <input type="file" name="homework_file" class="form-control" 
                                       accept="image/*,.pdf,.doc,.docx,.zip,.rar" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Nộp bài</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($user_role == 'Giảng Viên'): ?>
            <div class="card">
                <div class="card-header bg-warning text-dark">DANH SÁCH BÀI NỘP (<?php echo count($submissions); ?>)</div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Sinh viên</th>
                                <th style="width: 30%;">File nộp</th>
                                <th>Chấm điểm</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $sub): 
                                $file_ext = strtolower(pathinfo($sub['file_path'], PATHINFO_EXTENSION));
                                $is_viewable = in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
                            ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($sub['fullname']); ?></strong><br>
                                        <small class="text-muted"><?php echo date("H:i d/m", strtotime($sub['submitted_at'])); ?></small>
                                    </td>
                                    <td>
                                        <a href="download.php?file=<?php echo urlencode($sub['file_path']); ?>" class="btn btn-sm btn-outline-primary mb-1">
                                            Tải về
                                        </a>

                                        <?php if ($is_viewable): ?>
                                            <button type="button" class="btn btn-sm btn-info mb-1 text-white" 
                                                    onclick="previewFile('<?php echo $sub['file_path']; ?>')">
                                                Xem bài nộp
                                            </button>
                                        <?php else: ?>
                                            <br><small class="text-muted fst-italic">File này cần tải về để xem</small>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted"><?php echo basename($sub['file_path']); ?></small>
                                    </td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                            <div class="input-group input-group-sm mb-2">
                                                <span class="input-group-text">Điểm</span>
                                                <input type="number" name="grade" step="0.1" min="0" max="10" 
                                                       class="form-control" value="<?php echo $sub['grade']; ?>" required>
                                            </div>
                                            <textarea name="feedback" class="form-control form-control-sm mb-2" 
                                                      rows="2" placeholder="Nhận xét..."><?php echo $sub['feedback']; ?></textarea>
                                            <button type="submit" class="btn btn-success btn-sm w-100">Lưu kết quả</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header">
                <h5 class="modal-title">Xem trước bài làm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light d-flex justify-content-center align-items-center">
                <iframe id="fileViewer" src="" style="width: 100%; height: 100%; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>
<script>
    function previewFile(path) {
        var viewer = document.getElementById('fileViewer');
        var modal = new bootstrap.Modal(document.getElementById('previewModal'));
        viewer.src = path;
        modal.show();
    }
</script>
<?php require_once 'includes/footer.php'; ?>