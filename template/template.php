<?php require_once __DIR__ . '/../component/auth_check.php'; ?>
<!DOCTYPE html>

<html lang="ja">
<head>
  <!-- meta -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ページの説明文をここに記載します。"><!-- ! [注意]ページの内容を簡潔に説明する文を記載してください。 -->
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <!-- title -->
  <title>Template Page</title><!-- ! [注意]ページのタイトルを記載してください。 -->

  <!-- js -->
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>

  <!-- css -->
  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../custom/custom-theme.css">
</head>
<body>
  <!-- ヘッダー -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <!-- 本文 -->
  <main class="container p-4">
    <!-- ここにページの内容を追加してください -->



    <!-- 以下サンプルコンテンツ 削除してください -->
    <h1 class="display-6 fw-bold mb-4">テンプレートページ</h1>
    <p class="mb-4">このページは、LiQtフレームワークのテンプレートです。ここにコンテンツを追加してください。</p>
    
    <div class="bg-primary text-white p-4">
      Primary Box
    </div>

    <button class="btn btn-secondary mt-3 box-shadow">
      Secondary Button
    </button>
    <!-- ここまでサンプルコンテンツ -->
  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>
</body>
</html>