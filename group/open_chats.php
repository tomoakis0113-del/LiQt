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

  <title>オープンチャット一覧</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      background-color: #f5f7fb;
    }

    /* 💡 グループカード全体のデザイン調整 */
    .group-card {
      transition: 0.2s;
      margin-bottom: 1.5rem;
      cursor: pointer;
    }

    .group-card:hover {
      transform: translateY(-2px);
      background-color: #f8f9fa;
    }

    /* 💡 アイコンを少し大きめ・丸型に変更 */
    .group-icon {
      width: 65px;
      height: 65px;
      border-radius: 50%;
      object-fit: cover;
    }
  </style>
</head>

<body class="bg-light">

  <?php
  if (!isset($_SESSION)) {
      session_start();
  }
  require_once __DIR__ . '/../vendor/autoload.php';
  $csrfToken = new lib\CSRFToken();
  ?>
  <input type="hidden" id="csrf_token" value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container p-4" style="max-width:800px; padding-bottom: 120px;">

    <h3 class="fw-bold mb-4">オープンチャット</h3>

    <div id="alertBox" class="alert d-none"></div>

    <div id="groupList"></div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    // 💡 HTMLに埋め込んだCSRFトークンの取得
    const csrfToken = document.getElementById("csrf_token").value;

    // 初期ロード
    loadGroups();

    // 💡 一覧取得（API統合・POST送信版）
    async function loadGroups() {
      // エラー表示をクリア
      const box = document.getElementById("alertBox");
      box.className = "alert d-none";

      // POST用のFormDataを作成し、CSRFトークンをセット
      const formData = new FormData();
      formData.append("csrf_token", csrfToken);

      try {
        const res = await fetch("/api/group/get_public_groups.php", {
          method: "POST", // 💡 POSTで安全に送信
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          // APIから返ってきた data 配列をレンダリングに渡す
          renderGroups(data.data || []);
        } else {
          showError(data.message || "グループの取得に失敗しました");
        }

      } catch (e) {
        console.error("Error loading groups:", e);
        showError("通信エラーが発生しました");
      }
    }

    // 描画
    function renderGroups(groups) {
      const list = document.getElementById("groupList");
      list.innerHTML = "";

      if (groups.length === 0) {
        list.innerHTML = `
          <div class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-body text-center text-muted py-5">
              グループが見つかりません
            </div>
          </div>
        `;
        return;
      }

      // 💡 ダッシュボードのカードデザインに統一
      groups.forEach(g => {
        list.innerHTML += `
          <div class="card border-0 shadow-sm rounded-4 group-card" onclick="joinGroup('${g.group_id}')">
            <div class="card-body">
              <div class="d-flex align-items-center">
                
                <img src="${g.group_icon}" class="group-icon me-3" alt="${g.group_name}">

                <div class="flex-grow-1">
                  <h5 class="fw-bold mb-2">${g.group_name}</h5>
                  <p class="text-muted mb-0 text-truncate">
                    最新メッセージ：${g.latest_message || "メッセージなし"}
                  </p>
                </div>

              </div>
            </div>
          </div>
        `;
      });
    }

    // 💡 参加処理（API統合版）
    async function joinGroup(groupId) {
      if (!confirm("このグループに参加しますか？")) return;

      // エラー表示をクリア
      const box = document.getElementById("alertBox");
      box.className = "alert d-none";

      // 💡 要件に沿ったFormDataの作成（csrf_token, group_id）
      const formData = new FormData();
      formData.append("csrf_token", csrfToken);
      formData.append("group_id", groupId);

      try {
        const res = await fetch("/api/group/join_public_group.php", {
          method: "POST",
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          // 参加成功時はチャット画面に遷移
          location.href = `chat.php?group_id=${groupId}`;
        } else {
          showError(data.message);
        }

      } catch (e) {
        console.error("Error joining group:", e);
        showError("通信エラーが発生しました");
      }
    }

    // エラー表示
    function showError(msg) {
      const box = document.getElementById("alertBox");
      box.textContent = msg;
      box.className = "alert alert-danger mb-4";
    }
  </script>

</body>

</html>