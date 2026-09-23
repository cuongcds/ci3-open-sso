<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập thất bại</title>
</head>
<body>
    <h1>Đăng nhập qua SSO thất bại</h1>
    <p><?php echo htmlspecialchars($message ?? 'Login failed', ENT_QUOTES, 'UTF-8'); ?></p>
    <p><a href="<?php echo site_url('login'); ?>">Thử lại</a></p>
</body>
</html>
