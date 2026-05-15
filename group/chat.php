<!DOCTYPE html>

<html lang="ja">

<head>
  <!-- meta -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="グループチャットページ">
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <!-- title -->
  <title>グループチャット | LiQt</title>

  <!-- Bootstrap -->
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- CSS -->
  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../custom/custom-theme.css">

  <!-- markdown -->
  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>
  <!-- markdown -->
  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>

  <style>

    html,
    body {
      height: 100%;
    }

    body {
      background: #f8f9fa;
    }

    /* ページ全体余白 */
    main {
      padding-top: 50px !important;
      padding-bottom: 260px !important;
    }

    /* 各ブロック余白 */
    .section-card {
      margin-bottom: 45px;
    }

    .chat-box {
      min-height: 450px;
      background: #f8f9fa;
      border: 1px solid #ddd;
      border-radius: 20px;
      padding: 25px;
    }

    .message {
      margin-bottom: 30px;
    }

    .message-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;
    }

    .message-content {
      background: #fff;
      border-radius: 18px;
      padding: 18px;
      margin-top: 10px;
    }

    .preview-box {
      background: #fff;
      border: 1px dashed #ccc;
      border-radius: 15px;
      padding: 20px;
      min-height: 120px;
    }

    .list-group-item {
      border-radius: 16px !important;
      padding: 20px;
    }

    .blog-item {
      margin-bottom: 20px;
    }

  </style>
</head>

<body>

  <!-- ヘッダー -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <!-- 本文 -->
  <main class="container py-5 px-4">

    <!-- グループ情報 -->
    <div class="card border-0 shadow-sm rounded-4 section-card">

      <div class="card-body p-4 d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center">

          <img id="groupIcon"
               src="https://placehold.jp/100x100.png"
               width="80"
               height="80"
               class="rounded-circle me-4">

          <div>

            <h2 id="groupName" class="fw-bold mb-2">
              Web開発チーム
            </h2>

            <p class="text-muted mb-0">
              グループチャット
            </p>

          </div>

        </div>

        <!-- 編集 -->
        <a id="editLink"
           href="edit_group.php?group_id=1"
           class="btn btn-outline-secondary rounded-pill px-4 py-2">

          編集

        </a>

      </div>

    </div>

    <!-- チャット -->
    <div class="card border-0 shadow-sm rounded-4 section-card">

      <div class="card-body p-4">

        <h4 class="fw-bold mb-4">
          チャット
        </h4>

        <div class="chat-box mb-5" id="chatBox">

          <!-- メッセージ1 -->
          <div class="message d-flex">

            <img src="https://placehold.jp/100x100.png"
                 class="message-icon me-3">

            <div>

              <a href="profile.php?user_id=1"
                 class="fw-bold text-decoration-none">

                山田太郎

              </a>

              <div class="message-content shadow-sm">

                Bootstrapでチャット画面作りました！

              </div>

            </div>

          </div>

          <!-- メッセージ2 -->
          <div class="message d-flex">

            <img src="https://placehold.jp/100x100.png"
                 class="message-icon me-3">

            <div>

              <a href="profile.php?user_id=2"
                 class="fw-bold text-decoration-none">

                佐藤花子

              </a>

              <div class="message-content shadow-sm">

                **Markdown** も使えるようにしたい！

              </div>

            </div>

          </div>

        </div>

        <!-- 投稿フォーム -->
        <form id="messageForm">

          <!-- メッセージ -->
          <textarea
            class="form-control mb-4"
            id="messageInput"
            name="message_content"
            rows="5"
            placeholder="Markdown対応メッセージ"></textarea>

          <!-- 画像 -->
          <input
            type="file"
            class="form-control mb-4"
            name="image_upload">

          <!-- プレビュー -->
          <div class="mb-5">

            <label class="fw-bold mb-3">
              プレビュー
            </label>

            <div id="preview"
                 class="preview-box">

              ここにMarkdownプレビューが表示されます

            </div>

          </div>

          <!-- ボタン -->
          <button class="btn btn-success w-100 py-3 rounded-pill">

            送信

          </button>

        </form>

      </div>

    </div>

    <!-- グループブログ -->
    <div class="card border-0 shadow-sm rounded-4" style="margin-bottom: 500px;">

      <div class="card-body p-4">

        <h4 class="fw-bold mb-4">
          グループブログ
        </h4>

        <ul class="list-group" id="blogList">

          <!-- ブログ1 -->
          <li class="list-group-item blog-item">

            <a href="blog_detail.php?blog_id=1"
               class="fw-bold text-decoration-none fs-5">

              Bootstrapでモック作成

            </a>

            <div class="mt-3">

              <span class="badge bg-primary me-2">
                Bootstrap
              </span>

              <span class="badge bg-success">
                HTML
              </span>

            </div>

          </li>

          <!-- ブログ2 -->
          <li class="list-group-item">

            <a href="blog_detail.php?blog_id=2"
               class="fw-bold text-decoration-none fs-5">

              Java継承について

            </a>

            <div class="mt-3">

              <span class="badge bg-primary me-2">
                Java
              </span>

              <span class="badge bg-warning text-dark">
                オブジェクト指向
              </span>

            </div>

          </li>

        </ul>

      </div>

    </div>

  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>

    // markdown
    const md = window.markdownit();

    // プレビュー
    document.getElementById("messageInput")
      .addEventListener("input", function () {

        document.getElementById("preview").innerHTML =
          md.render(this.value);

      });

  </script>

</body>

</html>