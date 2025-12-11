<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $sql = "UPDATE users SET fullname = :fullname WHERE id = :id";
    $stmt = $conn->prepare($sql);
    if ($stmt->execute(['fullname' => $fullname, 'id' => $user_id])) {
        $_SESSION['fullname'] = $fullname;
        $message = "Cập nhật thông tin thành công!";
    } else {
        $message = "Có lỗi xảy ra!";
    }
}
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">THÔNG TIN CÁ NHÂN</div>
            <div class="card-body">
                <?php if($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Tên đăng nhập:</label>
                        <input type="text" class="form-control" value="<?php echo $user['username']; ?>" disabled readonly>
                    </div>
                    <div class="mb-3">
                        <label>Vai trò:</label>
                        <input type="text" class="form-control" value="<?php echo $user['role']; ?>" disabled readonly>
                    </div>
                    <div class="mb-3">
                        <label>Họ và tên:</label>
                        <input type="text" name="fullname" class="form-control" value="<?php echo $user['fullname']; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                    <a href="index.php" class="btn btn-secondary">Quay lại</a>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>