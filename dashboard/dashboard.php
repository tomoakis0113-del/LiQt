<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

// CSRF生成
$csrfToken = new lib\CSRFToken();

?>

<!DOCTYPE html>

<html lang="ja">

<head>

  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <meta name="description"
        content="ダッシュボードページ">

  <meta name="keywords"
        content="LiQt,SNS,コミュニティ,BLOG">

  <meta name="author"
        content="乙成,島田,勝原">

  <title>

    ダッシュボード | LiQt

  </title>

  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet"
        href="../custom/custom-theme.css">

  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

  <style>

    body {

      background-color: #f5f7fb;

    }

    .group-card {

      transition: 0.2s;

      /* 下マージン追加 */
      margin-bottom: 1.5rem;

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

    /* カード同士の下マージン */
    .dashboard-card {

      margin-bottom: 1.75rem;

    }

    /* 一番下の余白を大きめに追加 */
    #groupList {

      margin-bottom: 8rem;

    }

  </style>

</head>

<body class="bg-light">

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container py-4"
        style="padding-bottom: 120px;">

    <div class="mb-5">

      <h1 class="fw-bold mb-2">

        ダッシュボード

      </h1>

      
    <form id="searchForm" class="mb-4">
      <div class="input-group">
        <input type="text" class="form-control" name="group_name_search" placeholder="グループ名で検索">
        <button type="submit" class="btn btn-primary">検索</button>
      </div>
    </form>
      <p id="listTitle" class="text-muted mb-0">

        所属チャット一覧

      </p>

    </div>

    <div id="messageBox"
         class="mb-4"></div>

    <div class="card border-0 shadow-sm rounded-4 dashboard-card">

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

    <div class="card border-0 shadow-sm rounded-4 dashboard-card">

      <div class="card-body d-flex justify-content-between align-items-center">

        <div>

          <h5 class="fw-bold mb-1">

            グループ作成

          </h5>

          <p class="text-muted mb-0">

            新しいグループを作る

          </p>

        </div>

        <a href="/group/create_group.php"
           class="btn btn-primary rounded-pill px-4">

          <span class="material-symbols-outlined align-middle me-1">

            add

          </span>

          作成

        </a>

      </div>

    </div>

    <div id="groupList"></div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <input type="hidden"
         id="csrf_token"
         value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <script>

    // CSRF取得
    const csrfToken = document.getElementById("csrf_token").value;

    // 初期ロード
    loadDashboard();

    // =========================
    // ダッシュボード取得 (所属グループ一覧)
    // =========================

    async function loadDashboard() {

      // FormData
      const formData = new FormData();
      formData.append("csrf_token", csrfToken);

      try {

        // fetch
        const response =
          await fetch("../api/dashboard/get_dashboard.php", {

            method: "POST",
            body: formData

          });

        // text
        const text = await response.text();
        console.log(text);

        // JSON
        const data = JSON.parse(text);

        // エラー
        if (!data.success) {

          showMessage(
            data.message,
            "danger"
          );

          return;

        }

        // ラベルを元に戻す
        document.getElementById("listTitle").textContent = "所属チャット一覧";

        // グループ表示
        renderGroups(
          data.data.joined_groups,
          "参加中のグループはありません"
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
    // グループ検索のイベントリスナー統合
    // =========================
    document.getElementById("searchForm").addEventListener("submit", async (e) => {
      e.preventDefault();

      // メッセージボックスをクリア
      document.getElementById("messageBox").innerHTML = "";

      const keyword = e.target.elements["group_name_search"].value.trim();

      // キーワードが空の場合は、通常のダッシュボード（所属一覧）を表示
      if (keyword === "") {
        loadDashboard();
        return;
      }

      // APIパラメータ設定 (要件：csrf_token, keyword)
      const formData = new FormData();
      formData.append("csrf_token", csrfToken);
      formData.append("keyword", keyword);

      try {
        const response = await fetch("/api/group/search_groups.php", {
          method: "POST",
          body: formData
        });

        const data = await response.json();

        if (!data.success) {
          showMessage(data.message, "danger");
          return;
        }

        // 見出しを検索結果に変更
        document.getElementById("listTitle").textContent = `「${keyword}」の検索結果`;

        // グループ一覧の描画
        renderGroups(data.data, "該当する公開グループが見つかりません");

      } catch (error) {
        console.error(error);
        showMessage("検索中に通信エラーが発生しました", "danger");
      }
    });

    // =========================
    // グループ表示 (統合版)
    // =========================

    function renderGroups(groups, emptyMessage) {

      const groupList =
        document.getElementById("groupList");

      // 初期化
      groupList.innerHTML = "";

      // グループなし
      if (!groups || groups.length === 0) {

        groupList.innerHTML = `

          <div class="card border-0 shadow-sm rounded-4 mb-5">

            <div class="card-body text-center text-muted py-5">

              ${emptyMessage}

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

            <div class="card border-0 shadow-sm rounded-4 group-card">

              <div class="card-body">

                <div class="d-flex align-items-center">

                  <img src="${icon}"
                       class="group-icon me-3">

                  <div class="flex-grow-1">

                    <h5 class="fw-bold mb-2">

                      ${group.group_name}

                    </h5>

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

        <div class="alert alert-${type} mb-4">

          ${message}

        </div>

      `;

    }

  </script>

</body>

</html>