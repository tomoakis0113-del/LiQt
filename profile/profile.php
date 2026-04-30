<?php
session_start();

// 🔐 認証チェック（必要なら）
require_once __DIR__ . '/../component/auth_check.php';

// GETパラメータ（なければ自分）
$user_id = $_GET['user_id'] ?? $_SESSION['user_id'];

// 自分 or 他人 判定
$is_my_profile = ($user_id == $_SESSION['user_id']);

// モックデータ
$user = [
    'id' => $user_id,
    'name' => $is_my_profile ? '自分ユーザー' : '他ユーザー',
    'bio' => 'ここに自己紹介が入ります。よろしくお願いします！'
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>プロフィール</title>

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
</head>

<!-- ✅ フッター固定のための設定 -->
<body class="d-flex flex-column min-vh-100">

<!-- ヘッダー -->
<?php require_once __DIR__ . '/../component/header.php'; ?>

<!-- メイン -->
<main class="container p-4 flex-grow-1">

  <div class="card shadow-sm p-4">

    <!-- 上部 -->
    <div class="d-flex align-items-center mb-4">
      <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center"
           style="width:80px;height:80px;font-size:32px;">
        👤
      </div>

      <div class="ms-3">
        <h4 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
        <small class="text-muted">ID: <?php echo htmlspecialchars($user['id']); ?></small>
      </div>
    </div>

    <!-- 自己紹介 -->
    <div class="mb-4">
      <h5>自己紹介</h5>
      <p><?php echo htmlspecialchars($user['bio']); ?></p>
    </div>

    <!-- ボタン -->
    <?php if ($is_my_profile): ?>
      <a href="#" class="btn btn-success">プロフィール編集</a>
    <?php else: ?>
      <button class="btn btn-primary">フォロー</button>
    <?php endif; ?>

  </div>

</main>

<!-- フッター（これ使う） -->
<footer class="mt-auto border-top" style="background-color: #06C755;">
  <div class="container py-3">

    <div class="row text-center">
      <div class="col">
        <a href="/dashboard.php" class="text-white text-decoration-none d-block">
          🏠<br>ダッシュボード
        </a>
      </div>

      <div class="col">
        <a href="/blogs.php" class="text-white text-decoration-none d-block">
          📝<br>ブログ
        </a>
      </div>

      <div class="col">
        <a href="/profile.php" class="text-white text-decoration-none d-block">
          👤<br>プロフィール
        </a>
      </div>
    </div>

    <div class="text-center small text-white-50 mt-2">
      © <?php echo date('Y'); ?> LiQt
    </div>

  </div>
</footer>

</body>
</html>