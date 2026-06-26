<?php
require_once __DIR__ . '/../component/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php';
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
    /* 💡 動的マージン完全対応：
       タブの切り替えや件数の増減に関わらず、
       画面最下部の固定メニューの上に必ず十分な余白を確保するため、
       body要素の最下部に強制パディングを設定します。
    */
    body {
      background-color: #f5f7fb;
      padding-bottom: 240px !important; /* 👈 固定メニューに絶対に被らなくする絶対余白 */
    }

    /* 💡 グループカード全体のデザイン調整 */
    .group-card {
      transition: transform 0.2s, background-color 0.2s;
      cursor: pointer;
    }

    .group-card:hover {
      background: #f8f9fa;
      transform: translateY(-2px);
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

<body>

  <input type="hidden" id="csrf_token" value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container p-4" style="max-width:800px;">

    <h3 class="fw-bold mb-4">オープンチャット</h3>

    <div id="alertBox" class="alert d-none"></div>

    <div id="groupList" class="d-flex flex-column gap-3">
      <div class="text-center text-muted py-4">読み込み中...</div>
    </div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    // 💡 変数の定義のみを最初に行い、null参照エラーを防止
    let csrfToken = "";

    // 画面初期ロード時にグループ一覧を自動取得
    document.addEventListener("DOMContentLoaded", () => {
      // 💡 HTML要素の構築が完了したこのタイミングでトークンを安全に取得します
      const tokenElement = document.getElementById("csrf_token");
      if (tokenElement) {
        csrfToken = tokenElement.value;
      }
      
      loadGroups();
    });

    // =========================
    // 公開グループ一覧取得API
    // =========================
    async function loadGroups() {
      clearError();

      const formData = new FormData();
      formData.append("csrf_token", csrfToken);

      try {
        const res = await fetch("/api/group/get_public_groups.php", {
          method: "POST", // 💡 トークンを安全に送るためPOSTで送信
          body: formData
        });

        const data = await res.json();

        if (data.success) {
          // APIから返ってきたグループの配列をレンダリングに渡す
          renderGroups(data.data || []);
        } else {
          showError(data.message || "グループの取得に失敗しました。");
        }

      } catch (e) {
        console.error("Error loading groups:", e);
        showError("サーバーとの通信中にエラーが発生しました。");
      }
    }

    // =========================
    // 動的グループリストの描画処理
    // =========================
    function renderGroups(groups) {
      const list = document.getElementById("groupList");
      list.innerHTML = "";

      if (!groups || groups.length === 0) {
        list.innerHTML = `
          <div class="card border-0 shadow-sm rounded-4 p-4 text-center text-muted">
            グループが見つかりません
          </div>
        `;
        return;
      }

      // 💡 ブログカードのテクスチャ（border-0 shadow-sm rounded-4 p-4）を継承し、カード間隔を最適化
      groups.forEach(g => {
        list.innerHTML += `
          <div class="card border-0 shadow-sm rounded-4 group-card" onclick="joinGroup('${encodeURIComponent(g.group_id)}')">
            <div class="card-body p-4">
              <div class="d-flex align-items-center">
                
                <img src="${g.group_icon}" class="group-icon me-3" alt="${escapeHtml(g.group_name)}">

                <div class="flex-grow-1" style="min-width: 0;">
                  <h5 class="fw-bold mb-2 text-truncate">${escapeHtml(g.group_name)}</h5>
                  <p class="text-muted mb-0 text-truncate">
                    最新メッセージ：${escapeHtml(g.latest_message || "メッセージなし")}
                  </p>
                </div>

              </div>
            </div>
          </div>
        `;
      });
    }

    // =========================
    // 参加処理
    // =========================
    async function joinGroup(groupId) {
      if (!confirm("このグループに参加しますか？")) return;

      clearError();

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
        showError("通信エラーが発生しました。");
      }
    }

    // =========================
    // ユーティリティ
    // =========================
    function showError(msg) {
      const box = document.getElementById("alertBox");
      box.textContent = typeof msg === 'string' ? msg : "処理中にエラーが発生しました。";
      box.className = "alert alert-danger mb-4 shadow-sm rounded-3";
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function clearError() {
      const box = document.getElementById("alertBox");
      box.textContent = "";
      box.className = "alert d-none";
    }

    function escapeHtml(str) {
      if (!str) return "";
      return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }
  </script>

</body>

</html>