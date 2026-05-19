<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

// CSRF生成
$csrfToken = new lib\CSRFToken();

?>

<!DOCTYPE html>

<html lang="ja">

<head>

  <!-- meta -->
  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <meta name="description"
        content="ダッシュボードページ">

  <meta name="keywords"
        content="LiQt,SNS,コミュニティ,BLOG">

  <meta name="author"
        content="乙成,島田,勝原">

  <!-- title -->
  <title>

    ダッシュボード | LiQt

  </title>

  <!-- Bootstrap -->
  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- CSS -->
  <link rel="stylesheet"
        href="../custom/custom-theme.css">

  <!-- Google Fonts Icons -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

  <style>

    body {

      background-color: #f5f7fb;

    }

    .group-card {

      transition: 0.2s;

    }

    .group-card:hover {

      transform: translateY(-2px);

    }

    .group-icon {

      width: 65px;
      height: 65px;
      border-radius: 50%;
      object-fit: cover;

    }

  </style>

</head>

<body class="bg-light">

  <!-- ヘッダー -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <!-- 本文 -->
  <main class="container py-4"
        style="padding-bottom: 120px;">

    <!-- タイトル -->
    <div class="mb-4">

      <h1 class="fw-bold mb-1">

        ダッシュボード

      </h1>

      <p class="text-muted mb-0">

        所属チャット一覧

      </p>

    </div>

    <!-- メッセージ -->
    <div id="messageBox"></div>

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

    <!-- グループ一覧 -->
    <div id="groupList"></div>

  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <!-- CSRF -->
  <input type="hidden"
         id="csrf_token"
         value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <script>

    // 初期ロード
    loadDashboard();

    // =========================
    // ダッシュボード取得
    // =========================

    async function loadDashboard() {

      // CSRF取得
      const csrfToken =
        document.getElementById("csrf_token").value;

      // FormData
      const formData =
        new FormData();

      formData.append(
        "csrf_token",
        csrfToken
      );

      try {

        // fetch
        const response =
          await fetch("../api/dashboard/get_dashboard.php", {

            method: "POST",
            body: formData

          });

        // text
        const text =
          await response.text();

        console.log(text);

        // JSON
        const data =
          JSON.parse(text);

        // エラー
        if (!data.success) {

          showMessage(
            data.message,
            "danger"
          );

          return;

        }

        // 成功
        showMessage(
          data.message,
          "success"
        );

        // グループ表示
        renderGroups(
          data.data.joined_groups
        );

      }

      catch (error) {

        console.error(error);

        showMessage(
          "通信エラーが発生しました",
          "danger"
        );

      }

    }

    // =========================
    // グループ表示
    // =========================

    function renderGroups(groups) {

      const groupList =
        document.getElementById("groupList");

      // 初期化
      groupList.innerHTML = "";

      // グループなし
      if (!groups || groups.length === 0) {

        groupList.innerHTML = `

          <div class="card border-0 shadow-sm rounded-4">

            <div class="card-body text-center text-muted py-5">

              参加中のグループはありません

            </div>

          </div>

        `;

        return;

      }

      // ループ
      groups.forEach(group => {

        // アイコン
        const icon =
          group.group_icon && group.group_icon !== ""
          ? group.group_icon
          : "https://placehold.jp/80x80.png";

        // メッセージ
        const latestMessage =
          group.latest_message && group.latest_message !== ""
          ? group.latest_message
          : "まだメッセージはありません";

        // HTML追加
        groupList.innerHTML += `

          <a href="/group/chat.php?group_id=${group.group_id}"
             class="text-decoration-none text-dark">

            <div class="card border-0 shadow-sm rounded-4 mb-3 group-card">

              <div class="card-body">

                <div class="d-flex align-items-center">

                  <!-- アイコン -->
                  <img src="${icon}"
                       class="group-icon me-3">

                  <!-- グループ情報 -->
                  <div class="flex-grow-1">

                    <!-- グループ名 -->
                    <h5 class="fw-bold mb-2">

                      ${group.group_name}

                    </h5>

                    <!-- 最新メッセージ -->
                    <p class="text-muted mb-0 text-truncate">

                      最新メッセージ：
                      ${latestMessage}

                    </p>

                  </div>

                </div>

              </div>

            </div>

          </a>

        `;

      });

    }

    // =========================
    // メッセージ表示
    // =========================

    function showMessage(message, type) {

      const box =
        document.getElementById("messageBox");

      box.innerHTML = `

        <div class="alert alert-${type}">

          ${message}

        </div>

      `;

    }

  </script>

</body>

</html>