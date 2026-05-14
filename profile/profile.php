<!DOCTYPE html>

<html lang="ja">
<head>
  <!-- meta -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="プロフィールページ">
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <!-- title -->
  <title>プロフィール | LiQt</title>

  <!-- Google Fonts Icons -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

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
  <main class="container py-4 mb-5">

    <!-- プロフィールカード -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
      <div class="card-body text-center p-4">

        <!-- アイコン -->
        <div class="mb-3">
          <span class="material-symbols-outlined bg-success text-white rounded-circle p-3"
                style="font-size: 70px;">
            person
          </span>
        </div>

        <!-- 名前 -->
        <h2 class="fw-bold mb-1">山田 太郎</h2>

        <!-- ユーザーID -->
        <p class="text-muted mb-3">@taro_yamada</p>

        <!-- 自己紹介 -->
        <p class="mb-4">
          Webエンジニアを目指して勉強中です。<br>
          Java・PHP・Bootstrap を勉強しています！
        </p>

        <!-- ボタン -->
        <div class="d-flex justify-content-center gap-2">
          <button class="btn btn-success px-4">
            編集
          </button>

        </div>

      </div>
    </div>

    <!-- 投稿一覧 -->
    <div class="card shadow-sm border-0 rounded-4">

      <div class="card-header bg-white border-0 pt-4 pb-0">
        <h4 class="fw-bold">投稿一覧</h4>
      </div>

      <div class="card-body">

        <!-- 投稿1 -->
        <div class="border rounded-4 p-3 mb-3">

          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">
              Bootstrapでプロフィールページを作ってみた
            </h5>

            <small class="text-muted">
              2026/05/14
            </small>
          </div>

          <p class="text-muted mb-2">
            Bootstrapを使ってレスポンシブ対応のプロフィールページを作成しました。
          </p>

          <a href="#" class="text-success text-decoration-none fw-bold">
            詳細を見る
          </a>

        </div>

        <!-- 投稿2 -->
        <div class="border rounded-4 p-3 mb-3">

          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">
              Javaのクラス設計について
            </h5>

            <small class="text-muted">
              2026/05/10
            </small>
          </div>

          <p class="text-muted mb-2">
            クラス・インスタンス・継承について学んだ内容をまとめました。
          </p>

          <a href="#" class="text-success text-decoration-none fw-bold">
            詳細を見る
          </a>

        </div>

        <!-- 投稿3 -->
        <div class="border rounded-4 p-3">

          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">
              PHPでログイン機能を作成
            </h5>

            <small class="text-muted">
              2026/05/01
            </small>
          </div>

          <p class="text-muted mb-2">
            セッションを使用したログイン認証機能を実装しました。
          </p>

          <a href="#" class="text-success text-decoration-none fw-bold">
            詳細を見る
          </a>

        </div>

      </div>
    </div>

  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

</body>
</html>