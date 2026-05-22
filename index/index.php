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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ダッシュボードページ">
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <title>ダッシュボード | LiQt</title>

  <link class="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../custom/custom-theme.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

  <style>
    /* ==========================================
       💡 追記：前の画面の緑・白爆発からシームレスに
       ダッシュボードへ軟着陸させるフェードイン
    ========================================== */
    body {
      background-color: #f5f7fb;
      opacity: 0;
      /* 1.2秒かけて暗闇から近未来的に明るくなる */
      animation: superFadeIn 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes superFadeIn {
      0% {
        opacity: 0;
        transform: scale(0.97) translateY(20px);
        background-color: #11141a; /* 遷移レイヤーの最後の色と完全一致 */
      }
      40% {
        opacity: 0.5;
      }
      100% {
        opacity: 1;
        transform: scale(1) translateY(0);
        background-color: #f5f7fb;
      }
    }

    .dashboard-card {
      margin-bottom: 1.75rem;
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      border: none !important;
    }

    /* ホバー時にブランドカラーの緑の光をまとうように進化 */
    .dashboard-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 20px rgba(40, 167, 69, 0.15) !important;
    }

    .group-card {
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      margin-bottom: 1.5rem;
    }

    .group-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(40, 167, 69, 0.12) !important;
    }

    .group-icon {
      width: 65px;
      height: 65px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #ffffff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    #groupList {
      margin-bottom: 8rem;
    }
  </style>
</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container py-4" style="padding-bottom: 120px;">

    <div class="mb-5">
      <h1 class="fw-bold mb-2">ダッシュボード</h1>
      <p class="text-muted mb-0">所属チャット一覧</p>
    </div>

    <div id="messageBox" class="mb-4"></div>

    <div class="card shadow-sm rounded-4 dashboard-card" style="animation: itemReveal 0.6s ease forwards; animation-delay: 0.2s; opacity: 0;">
      <div class="card-body d-flex justify-content-between align-items-center p-4">
        <div>
          <h5 class="fw-bold mb-1">オープンチャット</h5>
          <p class="text-muted mb-0">新しいグループを探す</p>
        </div>
        <a href="/group/open_chats.php" class="btn btn-success rounded-pill px-4 shadow-sm">
          <span class="material-symbols-outlined align-middle me-1">groups</span>
          開く
        </a>
      </div>
    </div>

    <div class="card shadow-sm rounded-4 dashboard-card" style="animation: itemReveal 0.6s ease forwards; animation-delay: 0.3s; opacity: 0;">
      <div class="card-body d-flex justify-content-between align-items-center p-4">
        <div>
          <h5 class="fw-bold mb-1">グループ作成</h5>
          <p class="text-muted mb-0">新しいグループを作る</p>
        </div>
        <a href="/group/create_group.php" class="btn btn-primary rounded-pill px-4 shadow-sm">
          <span class="material-symbols-outlined align-middle me-1">add</span>
          作成
        </a>
      </div>
    </div>

    <div id="groupList"></div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <input type="hidden" id="csrf_token" value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <script>
    loadDashboard();

    async function loadDashboard() {
      const csrfToken = document.getElementById("csrf_token").value;
      const formData = new FormData();
      formData.append("csrf_token", csrfToken);

      try {
        const response = await fetch("../api/dashboard/get_dashboard.php", {
          method: "POST",
          body: formData
        });

        const text = await response.text();
        const data = JSON.parse(text);

        if (!data.success) {
          showMessage(data.message, "danger");
          return;
        }

        renderGroups(data.data.joined_groups);
      } catch (error) {
        console.error(error);
        showMessage("通信エラーが発生しました", "danger");
      }
    }

    function renderGroups(groups) {
      const groupList = document.getElementById("groupList");
      groupList.innerHTML = "";

      if (!groups || groups.length === 0) {
        groupList.innerHTML = `
          <div class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-body text-center text-muted py-5">
              参加中のグループはありません
            </div>
          </div>
        `;
        return;
      }

      groups.forEach((group, index) => {
        const icon = group.group_icon && group.group_icon !== "" ? group.group_icon : "https://placehold.jp/80x80.png";
        const latestMessage = group.latest_message && group.latest_message !== "" ? group.latest_message : "まだメッセージはありません";

        // 💡 読み込まれたグループカードが流れるように1つずつ時間差（スタッガー）で出現
        groupList.innerHTML += `
          <a href="/group/chat.php?group_id=${group.group_id}" class="text-decoration-none text-dark">
            <div class="card border-0 shadow-sm rounded-4 group-card" style="animation: itemReveal 0.5s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; animation-delay: ${0.4 + (index * 0.06)}s; opacity: 0;">
              <div class="card-body p-4">
                <div class="d-flex align-items-center">
                  <img src="${icon}" class="group-icon me-3">
                  <div class="flex-grow-1">
                    <h5 class="fw-bold mb-1">${group.group_name}</h5>
                    <p class="text-muted mb-0 text-truncate">最新メッセージ：${latestMessage}</p>
                  </div>
                  <span class="material-symbols-outlined text-muted">chevron_right</span>
                </div>
              </div>
            </div>
          </a>
        `;
      });
    }

    function showMessage(message, type) {
      const box = document.getElementById("messageBox");
      box.innerHTML = `<div class="alert alert-${type} mb-4">${message}</div>`;
    }
  </script>

  <style>
    @keyframes itemReveal {
      0% {
        opacity: 0;
        transform: translateY(25px) scale(0.98);
      }
      100% {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }
  </style>

</body>
</html>