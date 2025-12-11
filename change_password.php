<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$message = "";
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!password_verify($current_password, $user['password'])) {
        $error = "Mật khẩu hiện tại không đúng!";
    } 
    elseif ($new_password !== $confirm_password) {
        $error = "Mật khẩu mới xác nhận không khớp!";
    } 
    elseif (strlen($new_password) < 6) {
        $error = "Mật khẩu mới phải có ít nhất 6 ký tự!";
    }
    else {
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute(['password' => $new_hashed_password, 'id' => $user_id])) {
            $message = "Đổi mật khẩu thành công! Lần sau hãy đăng nhập bằng mật khẩu mới.";
        } else {
            $error = "Lỗi hệ thống, vui lòng thử lại.";
        }
    }
}
?>
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-warning text-dark">ĐỔI MẬT KHẨU</div>
            <div class="card-body">
                <?php if($message): ?><div class="alert alert-success"><?php echo $message; ?></div><?php endif; ?>
                <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label>Mật khẩu hiện tại:</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>                    
                    <hr>
                    <div class="mb-3">
                        <label>Mật khẩu mới:</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Xác nhận mật khẩu mới:</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">Cập nhật mật khẩu</button>
                    <div class="text-center mt-3">
                        <a href="index.php">Quay lại trang chủ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>