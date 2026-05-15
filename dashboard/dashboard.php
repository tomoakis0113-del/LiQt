<!DOCTYPE html>

<html lang="ja">

<head>
  <!-- meta -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ダッシュボードページ">
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <!-- title -->
  <title>ダッシュボード | LiQt</title>

  <!-- Bootstrap -->
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>

  <!-- CSS -->
  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../custom/custom-theme.css">

  <!-- Google Fonts Icons -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>

<body class="bg-light">

  <!-- ヘッダー -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>
  <!-- ヘッダー -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <!-- 本文 -->
  <main class="container py-4" style="padding-bottom: 120px;">

    <!-- タイトル -->
    <div class="mb-4">

      <h1 class="fw-bold mb-1">
        ダッシュボード
      </h1>

      <p class="text-muted mb-0">
        所属チャット一覧
      </p>

    </div>

    <!-- オープンチャット -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">

      <div class="card-body d-flex justify-content-between align-items-center">

        <div>

          <h5 class="fw-bold mb-1">
            オープンチャット
          </h5>

          <p class="text-muted mb-0">
            新しいグループを探す
          </p>

        </div>

        <a href="/group/open_chats.php"
           class="btn btn-success rounded-pill px-4">

          <span class="material-symbols-outlined align-middle me-1">
            groups
          </span>

          開く

        </a>

      </div>

    </div>

    <!-- 所属チャット一覧 -->

    <!-- グループ1 -->
    <a href="/group/chat.php?group_id=1"
       class="text-decoration-none text-dark">

      <div class="card border-0 shadow-sm rounded-4 mb-3">

        <div class="card-body">

          <div class="d-flex align-items-center">

            <!-- アイコン -->
            <img src="https://placehold.jp/80x80.png"
                 class="rounded-circle me-3"
                 width="65"
                 height="65">

            <!-- グループ情報 -->
            <div class="flex-grow-1">

              <div class="d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-1">
                  Web開発チーム
                </h5>

                <small class="text-muted">
                  14:22
                </small>

              </div>

              <p class="text-muted mb-0 text-truncate">
                最新メッセージ：Bootstrapモック完成しました！
              </p>

            </div>

          </div>

        </div>

      </div>

    </a>

    <!-- グループ2 -->
    <a href="/chat/chat.php?group_id=2"
       class="text-decoration-none text-dark">

      <div class="card border-0 shadow-sm rounded-4 mb-3">

        <div class="card-body">

          <div class="d-flex align-items-center">

            <!-- アイコン -->
            <img src="https://placehold.jp/80x80.png"
                 class="rounded-circle me-3"
                 width="65"
                 height="65">

            <!-- グループ情報 -->
            <div class="flex-grow-1">

              <div class="d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-1">
                  Java勉強会
                </h5>

                <small class="text-muted">
                  昨日
                </small>

              </div>

              <p class="text-muted mb-0 text-truncate">
                最新メッセージ：継承の課題終わった？
              </p>

            </div>

          </div>

        </div>

      </div>

    </a>

    <!-- グループ3 -->
    <a href="/chat/chat.php?group_id=3"
       class="text-decoration-none text-dark">

      <div class="card border-0 shadow-sm rounded-4 mb-3">

        <div class="card-body">

          <div class="d-flex align-items-center">

            <!-- アイコン -->
            <img src="https://placehold.jp/80x80.png"
                 class="rounded-circle me-3"
                 width="65"
                 height="65">

            <!-- グループ情報 -->
            <div class="flex-grow-1">

              <div class="d-flex justify-content-between align-items-center">

                <h5 class="fw-bold mb-1">
                  PHPチーム
                </h5>

                <small class="text-muted">
                  5/13
                </small>

              </div>

              <p class="text-muted mb-0 text-truncate">
                最新メッセージ：ログイン機能追加しました！
              </p>

            </div>

          </div>

        </div>

      </div>

    </a>

  </main>
  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>
  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

</body>

</html>