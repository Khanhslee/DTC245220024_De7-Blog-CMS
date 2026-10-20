<?php
$host = getenv('DB_HOST') ?: 'mysql';
$db   = getenv('DB_NAME') ?: 'blog_cms_db';
$user = getenv('DB_USER') ?: 'wp_app_user';
$pass = getenv('DB_PASSWORD') ?: 'WpApp@Pass2026!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die("<h3 style='color:red;'>Lỗi kết nối CSDL: " . htmlspecialchars($e->getMessage()) . "</h3>");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_post'])) {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, category_id, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([trim($_POST['title']), trim($_POST['content']), (int)$_POST['category_id'], (int)$_POST['user_id']]);
    } elseif (isset($_POST['add_category'])) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([trim($_POST['cat_name']), trim($_POST['cat_desc'])]);
    }
    header("Location: /");
    exit;
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();
$categories = $pdo->query("SELECT c.*, COUNT(p.id) AS total_posts FROM categories c LEFT JOIN posts p ON c.id = p.category_id GROUP BY c.id")->fetchAll();
$posts = $pdo->query("SELECT p.*, c.name AS cat_name, u.fullname, u.username FROM posts p 
                      LEFT JOIN categories c ON p.category_id = c.id 
                      LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đề 7: Hệ thống Blog / CMS - DTC245220024</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; margin: 0; background: #f1f5f9; color: #0f172a; }
        .navbar { background: #0f172a; color: #fff; padding: 16px 32px; display: flex; justify-content: space-between; align-items: center; }
        .container { max-width: 1100px; margin: 24px auto; padding: 0 16px; display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h1, h2, h3 { margin-top: 0; }
        .badge { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 99px; font-size: 12px; font-weight: 600; }
        .meta { font-size: 13px; color: #64748b; margin-bottom: 12px; }
        input, select, textarea, button { width: 100%; padding: 10px; margin-bottom: 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        button { background: #2563eb; color: #fff; border: none; font-weight: 600; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="navbar">
        <h2>Đề 7: Hệ thống Blog / CMS</h2>
        <span>SV: Lê Nam Khánh - MSSV: DTC245220024</span>
    </div>
    <div class="container">
        <div>
            <div class="card">
                <h3>Đăng bài viết mới</h3>
                <form method="POST">
                    <input type="text" name="title" placeholder="Tiêu đề bài viết..." required>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                        <select name="category_id">
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>">Danh mục: <?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="user_id">
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>">Tác giả: <?= htmlspecialchars($u['fullname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <textarea name="content" rows="3" placeholder="Nội dung bài viết..." required></textarea>
                    <button type="submit" name="add_post"> Xuất bản bài viết</button>
                </form>
            </div>

            <h3>Danh sách bài viết (<?= count($posts) ?>)</h3>
            <?php foreach ($posts as $p): ?>
                <div class="card">
                    <span class="badge"><?= htmlspecialchars($p['cat_name'] ?: 'Chung') ?></span>
                    <h3 style="margin-top:10px; margin-bottom:6px;"><?= htmlspecialchars($p['title']) ?></h3>
                    <div class="meta">
                        Tác giả: <b><?= htmlspecialchars($p['fullname']) ?></b> (@<?= htmlspecialchars($p['username']) ?>) | Thời gian: <?= $p['created_at'] ?>
                    </div>
                    <p style="margin-bottom:0; line-height:1.6;"><?= nl2br(htmlspecialchars($p['content'])) ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div>
            <div class="card">
                <h3>Danh mục (Categories)</h3>
                <table>
                    <tr><th>Tên danh mục</th><th>Số bài</th></tr>
                    <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><b><?= htmlspecialchars($c['name']) ?></b><br><small style="color:#64748b"><?= htmlspecialchars($c['description']) ?></small></td>
                            <td><span class="badge"><?= $c['total_posts'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <form method="POST" style="margin-top:16px;">
                    <input type="text" name="cat_name" placeholder="Tên danh mục mới..." required>
                    <input type="text" name="cat_desc" placeholder="Mô tả ngắn...">
                    <button type="submit" name="add_category">+ Thêm danh mục</button>
                </form>
            </div>

            <div class="card">
                <h3>Người dùng hệ thống (Users)</h3>
                <table>
                    <tr><th>Username</th><th>Họ tên</th><th>Vai trò</th></tr>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                            <td><?= htmlspecialchars($u['fullname']) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($u['role']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
