<!DOCTYPE html>
<html lang="ja">

<head>
  <!-- meta -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>プロフィール | LiQt</title>

  <!-- Bootstrap -->
  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- Icons -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>

<body>

  <!-- ヘッダー -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <!-- 本文 -->
  <main class="container py-4"
        style="padding-bottom: 120px; max-width: 900px;">

    <!-- プロフィールカード -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">

      <div class="card-body text-center p-4">

        <!-- アイコン -->
        <div class="mb-3">

          <span class="material-symbols-outlined
                       bg-success
                       text-white
                       rounded-circle
                       p-3"
                style="font-size: 70px;">

            person

          </span>

        </div>

        <!-- 名前 -->
        <h2 class="fw-bold mb-1">
          山田 太郎
        </h2>

        <!-- ID -->
        <p class="text-muted mb-3">
          @taro_yamada
        </p>

        <!-- 自己紹介 -->
        <p class="mb-4">
          Webエンジニアを目指して勉強中です。<br>
          Java・PHP・Bootstrap を勉強しています！
        </p>

        <!-- 編集 -->
        <button class="btn btn-success px-4">
          編集
        </button>

      </div>

    </div>

    <!-- 投稿一覧 -->
    <div class="card border-0 shadow-sm rounded-4 mb-5">

      <!-- ヘッダー -->
      <div class="card-header bg-white border-0 pt-4 pb-0">

        <h4 class="fw-bold">
          投稿一覧
        </h4>

      </div>

      <!-- 本文 -->
      <div class="card-body">

        <!-- 投稿1 -->
        <div class="border rounded-4 p-3 mb-3">

          <div class="d-flex justify-content-between align-items-center mb-2">

            <!-- タイトル -->
            <h5 class="fw-bold mb-0">

              <a href="/blog/blog_detail.php?blog_id=1"
                 class="text-dark text-decoration-none">

                Bootstrapでプロフィールページを作ってみた

              </a>

            </h5>

            <!-- 日付 -->
            <small class="text-muted">
              2026/05/14
            </small>

          </div>

          <!-- 本文 -->
          <p class="text-muted mb-2">
            Bootstrapを使ってレスポンシブ対応のプロフィールページを作成しました。
          </p>

          <!-- リンク -->
          <a href="/blog/blog_detail.php?blog_id=1"
             class="text-success text-decoration-none fw-bold">

            詳細を見る

          </a>

        </div>

        <!-- 投稿2 -->
        <div class="border rounded-4 p-3 mb-3">

          <div class="d-flex justify-content-between align-items-center mb-2">

            <h5 class="fw-bold mb-0">

              <a href="/blog/blog_detail.php?blog_id=2"
                 class="text-dark text-decoration-none">

                Javaのクラス設計について

              </a>

            </h5>

            <small class="text-muted">
              2026/05/10
            </small>

          </div>

          <p class="text-muted mb-2">
            クラス・インスタンス・継承について学んだ内容をまとめました。
          </p>

          <a href="/blog/blog_detail.php?blog_id=2"
             class="text-success text-decoration-none fw-bold">

            詳細を見る

          </a>

        </div>

        <!-- 投稿3 -->
        <div class="border rounded-4 p-3">

          <div class="d-flex justify-content-between align-items-center mb-2">

            <h5 class="fw-bold mb-0">

              <a href="/blog/blog_detail.php?blog_id=3"
                 class="text-dark text-decoration-none">

                PHPでログイン機能を作成

              </a>

            </h5>

            <small class="text-muted">
              2026/05/01
            </small>

          </div>

          <p class="text-muted mb-2">
            セッションを使用したログイン認証機能を実装しました。
          </p>

          <a href="/blog/blog_detail.php?blog_id=3"
             class="text-success text-decoration-none fw-bold">

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