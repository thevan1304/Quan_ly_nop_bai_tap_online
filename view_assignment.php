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
                            <div class="alert alert-success">
                                Bạn đã nộp bài lúc: <?php echo $my_submission['submitted_at']; ?> <br>
                                File đã nộp: <strong><?php echo basename($my_submission['file_path']); ?></strong>
</div>
                        </div>
                        <?php if ($my_submission['grade'] !== null): ?>
                            <div class="alert alert-warning">
                                <strong>Điểm số: <?php echo $my_submission['grade']; ?>/10</strong> <br>
                                Lời phê: <?php echo htmlspecialchars($my_submission['feedback']); ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Đang chờ giảng viên chấm điểm...</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label>Chọn file bài làm (Word, PDF, ZIP...):</label>
                                <input type="file" name="homework_file" class="form-control" required>
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
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sinh viên</th>
                                <th>File nộp</th>
                                <th>Điểm & Nhận xét</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $sub): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($sub['fullname']); ?></td>
                                    <td>
                                        <a href="download.php?file=<?php echo urlencode($sub['file_path']); ?>" class="btn btn-sm btn-info">Tải về</a>
                                        <br><small><?php echo date("H:i d/m", strtotime($sub['submitted_at'])); ?></small>
                                    </td>
                                    <form method="POST">
                                        <td>
                                            <input type="hidden" name="submission_id" value="<?php echo $sub['id']; ?>">
                                            <input type="number" name="grade" step="0.1" min="0" max="10" 
                                                   class="form-control mb-1" placeholder="Điểm" 
                                                   value="<?php echo $sub['grade']; ?>" required>
                                            <textarea name="feedback" class="form-control form-control-sm" 
                                                      placeholder="Lời phê"><?php echo $sub['feedback']; ?></textarea>
                                        </td>
                                        <td>
                                            <button type="submit" class="btn btn-success btn-sm mt-2">Lưu điểm</button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>