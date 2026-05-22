<?php
// セッションの開始（CSRFトークン管理用）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 💡 lib\CSRFToken クラスから安全に生成
require_once __DIR__ . '/../vendor/autoload.php';
$csrfToken = new lib\CSRFToken();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>ブログ一覧 | LiQt</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <style>
    /* 💡 動的マージン完全対応：
       タブの切り替えやブログ件数の増減（コンテンツの長さ）に関わらず、
       画面最下部の固定メニューの上に必ず十分な余白を確保するため、
       body要素の最下部に強制パディングを設定します。
    */
    body {
      background-color: #f5f7fb;
      padding-bottom: 240px !important; /* 👈 固定メニューに絶対に被らなくする絶対余白 */
    }

    .tag-badge {
      margin-right: 5px;
    }

    .blog-card-link {
      text-decoration: none;
      color: inherit;
    }

    .blog-card {
      transition: transform 0.2s, background-color 0.2s;
    }

    .blog-card:hover {
      background: #f8f9fa;
      transform: translateY(-2px);
    }
  </style>
</head>

<body>

  <input type="hidden" id="csrf_token" value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container p-4" style="max-width:900px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0 fw-bold">ブログ一覧</h3>
      <a href="/blog/create_blog.php" class="btn btn-success rounded-pill px-4">
        ＋ ブログ作成
      </a>
    </div>

    <div id="alertBox" class="alert d-none"></div>

    <form id="searchForm" class="mb-4">
      <div class="input-group shadow-sm rounded-3 overflow-hidden">
        <input type="text" name="search_word" class="form-control border-0 px-3" placeholder="ブログのタイトル、タグで検索...">
        <button class="btn btn-primary px-4" type="submit">検索</button>
      </div>
    </form>

    <ul class="nav nav-pills mb-4 bg-white p-2 rounded-4 shadow-sm" id="blogTab" role="tablist">
      <li class="nav-item flex-fill text-center" role="presentation">
        <button class="nav-link active w-100 rounded-3 fw-bold" data-bs-toggle="tab" data-bs-target="#public" type="button" role="tab">
          公開ブログ
        </button>
      </li>
      <li class="nav-item flex-fill text-center" role="presentation">
        <button class="nav-link w-100 rounded-3 fw-bold" data-bs-toggle="tab" data-bs-target="#private" type="button" role="tab">
          非公開ブログ
        </button>
      </li>
      <li class="nav-item flex-fill text-center" role="presentation">
        <button class="nav-link w-100 rounded-3 fw-bold" data-bs-toggle="tab" data-bs-target="#myblogs" type="button" role="tab">
          自分のブログ
        </button>
      </li>
    </ul>

    <div class="tab-content" id="blogTabContent">
      <div class="tab-pane fade show active" id="public" role="tabpanel">
        <div id="publicList" class="d-flex flex-column gap-3">
          <div class="text-center text-muted py-4">読み込み中...</div>
        </div>
      </div>

      <div class="tab-pane fade" id="private" role="tabpanel">
        <div id="privateList" class="d-flex flex-column gap-3">
          <div class="text-center text-muted py-4">読み込み中...</div>
        </div>
      </div>

      <div class="tab-pane fade" id="myblogs" role="tabpanel">
        <div id="myblogsList" class="d-flex flex-column gap-3">
          <div class="text-center text-muted py-4">読み込み中...</div>
        </div>
      </div>
    </div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    // HTMLに埋め込んだCSRFトークンの取得
    const csrfToken = document.getElementById("csrf_token").value;

    // 画面初期ロード時にブログ一覧を自動取得
    document.addEventListener("DOMContentLoaded", () => {
      loadAllBlogs();
    });

    // =========================
    // ブログ一覧取得APIの統合
    // =========================
    async function loadAllBlogs() {
      clearError();

      const formData = new FormData();
      formData.append("csrf_token", csrfToken);

      try {
        const response = await fetch("/api/blog/get_blogs.php", {
          method: "POST",
          body: formData
        });

        if (!response.ok) throw new Error("ブログ一覧の取得に失敗しました。");

        const result = await response.json();

        if (result.success && result.data) {
          // 仕様に基づく各リストデータへのレンダリングマッピング
          renderBlogs("publicList", result.data.public_blogs || []);
          renderBlogs("privateList", result.data.private_blogs || []);
          renderBlogs("myblogsList", result.data.my_blogs || [], true);
        } else {
          showError(result.message || "ブログ一覧を取得できませんでした。");
        }
      } catch (error) {
        console.error(error);
        showError("サーバーとの通信中にエラーが発生しました。");
      }
    }

    // =========================
    // 動的ブログリストの描画処理
    // =========================
    function renderBlogs(elementId, blogs, isMyBlogTab = false) {
      const container = document.getElementById(elementId);
      container.innerHTML = "";

      if (blogs.length === 0) {
        container.innerHTML = `<div class="card border-0 shadow-sm rounded-4 p-4 text-center text-muted">ブログがありません</div>`;
        return;
      }

      blogs.forEach(blog => {
        // タグのパースとトリミング
        const tagsHtml = blog.tags ? blog.tags.split(",").map(tag => 
          `<span class="badge bg-success tag-badge">${escapeHtml(tag.trim())}</span>`
        ).join("") : "";

        // 自分のブログタブの場合のみ公開ステータス（visibility）を表示
        let visibilityBadge = "";
        if (isMyBlogTab && blog.visibility) {
          const isPublic = blog.visibility === "public";
          visibilityBadge = `<span class="badge ${isPublic ? 'bg-primary' : 'bg-secondary'} me-2">${isPublic ? '公開' : '非公開'}</span>`;
        }

        // ブログ詳細への正しいリンクパス構造
        container.innerHTML += `
          <a href="/blog/blog_detail.php?blog_id=${encodeURIComponent(blog.blog_id)}" class="blog-card-link">
            <div class="card border-0 shadow-sm rounded-4 blog-card mb-1">
              <div class="card-body p-4">
                <div class="d-flex align-items-center mb-2">
                  ${visibilityBadge}
                  <h4 class="card-title fw-bold mb-0 text-truncate">${escapeHtml(blog.title)}</h4>
                </div>
                <div class="card-tags">${tagsHtml}</div>
              </div>
            </div>
          </a>
        `;
      });
    }

    // =========================
    // 検索処理
    // =========================
    document.getElementById("searchForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      clearError();

      const formData = new FormData(e.target);
      formData.append("csrf_token", csrfToken);

      try {
        const res = await fetch("api/search_blogs.php", {
          method: "POST",
          body: formData
        });

        if (!res.ok) throw new Error("検索処理に失敗しました。");

        const data = await res.json();

        if (data.success) {
          renderBlogs("publicList", data.blogs || []);
          document.querySelector('[data-bs-target="#public"]').click();
        } else {
          showError(data.message);
        }
      } catch (err) {
        showError("検索中に通信エラーが発生しました。");
      }
    });

    // =========================
    // ユーティリティ
    // =========================
    function showError(msg) {
      const box = document.getElementById("alertBox");
      box.textContent = msg;
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