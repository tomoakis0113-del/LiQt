<?php
// セッションの開始（CSRFトークン管理用）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRFトークンの生成（存在しない場合のみ）
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>ブログ一覧</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <style>
    .tag-badge {
      margin-right: 5px;
    }

    .blog-card:hover {
      background: #f8f9fa;
    }
  </style>
</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container p-4" style="max-width:900px; margin-bottom:120px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="mb-0">ブログ一覧</h3>
      <a href="/blog/create_blog.php" class="btn btn-success">
        ＋ ブログ作成
      </a>
    </div>

    <div id="alertBox" class="alert d-none"></div>

    <form id="searchForm" class="row g-2 mb-4">
      <div class="col-md-6">
        <input type="text" class="form-control" name="tag_search" placeholder="タグ検索（例: Java,PHP）">
      </div>
      <div class="col-md-4">
        <input type="text" class="form-control" name="group_filter" placeholder="グループID">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">検索</button>
      </div>
    </form>

    <ul class="nav nav-tabs mb-3" id="blogTabs">
      <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#public">公開</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#private">非公開</button>
      </li>
      <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#my">自分の投稿</button>
      </li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane fade show active" id="public">
        <div id="publicList" class="list-group"></div>
      </div>
      <div class="tab-pane fade" id="private">
        <div id="privateList" class="list-group"></div>
      </div>
      <div class="tab-pane fade" id="my">
        <div id="myList" class="list-group"></div>
      </div>
    </div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    // ページのロードが完了したらブログ一覧を取得
    document.addEventListener("DOMContentLoaded", () => {
      loadBlogs();
    });

    // CSRFトークンをPHPからJavaScriptへ安全に渡す
    const csrfToken = "<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>";

    // =========================
    // APIからブログ一覧データを取得
    // =========================
    async function loadBlogs() {
      clearError();

      // FormDataにAPI仕様で要求されているcsrf_tokenを積載する
      const formData = new FormData();
      formData.append("csrf_token", csrfToken);

      try {
        const res = await fetch("/api/blog/get_blogs.php", {
          method: "POST", // トークンを送るため通常はPOST、もしくはGETのパラメータに付与
          body: formData
        });

        if (!res.ok) {
          throw new Error("サーバーとの通信に失敗しました。");
        }

        const data = await res.json();

        if (data.success) {
          // 取得成功時：レスポンスから各属性のブログ配列を抽出して描画
          // ※API側の出力キー名称に合わせて調整してください（以下は一般的な設計に基づいています）
          const publicBlogs  = data.public_blogs  || [];
          const privateBlogs = data.private_blogs || [];
          const myBlogs      = data.my_blogs      || [];

          renderBlogs("publicList", publicBlogs);
          renderBlogs("privateList", privateBlogs);
          renderMyBlogs(myBlogs);
        } else {
          showError(data.message);
        }
      } catch (err) {
        showError("ブログ一覧の取得中にエラーが発生しました。");
      }
    }

    // =========================
    // 共通描画 (公開 / 非公開)
    // =========================
    function renderBlogs(targetId, blogs) {
      const list = document.getElementById(targetId);
      list.innerHTML = "";

      if (blogs.length === 0) {
        list.innerHTML = '<div class="text-muted p-3 text-center">該当するブログはありません。</div>';
        return;
      }

      blogs.forEach(b => {
        list.innerHTML += `
          <a href="blog_detail.php?blog_id=${b.blog_id}" class="list-group-item blog-card mb-2 text-decoration-none text-dark">
            <div class="fw-bold mb-1">${escapeHtml(b.title || b.blog_title)}</div>
            <div>${renderTags(b.tags || b.blog_tags)}</div>
          </a>
        `;
      });
    }

    // =========================
    // 自分の投稿 描画
    // =========================
    function renderMyBlogs(blogs) {
      const list = document.getElementById("myList");
      list.innerHTML = "";

      if (blogs.length === 0) {
        list.innerHTML = '<div class="text-muted p-3 text-center">投稿したブログはありません。</div>';
        return;
      }

      blogs.forEach(b => {
        // 公開ステータスに応じたバッジ色の選定
        const visibility = b.visibility || b.blog_visibility || "public";
        const badgeClass = visibility === "public" ? "bg-success" : (visibility === "private" ? "bg-secondary" : "bg-info");
        const badgeLabel = visibility === "public" ? "公開" : (visibility === "private" ? "非公開" : "グループ");

        list.innerHTML += `
          <a href="blog_detail.php?blog_id=${b.blog_id}" class="list-group-item blog-card mb-2 text-decoration-none text-dark">
            <div class="fw-bold mb-1">${escapeHtml(b.title || b.blog_title)}</div>
            <div class="mb-2">${renderTags(b.tags || b.blog_tags)}</div>
            <span class="badge ${badgeClass}">${badgeLabel}</span>
          </a>
        `;
      });
    }

    // =========================
    // タグ表示用ヘルパー
    // =========================
    function renderTags(tags) {
      if (!tags) return "";
      return tags
        .split(",")
        .map(tag => {
          if (!tag.trim()) return "";
          return `<span class="badge bg-primary tag-badge">${escapeHtml(tag.trim())}</span>`;
        })
        .join("");
    }

    // =========================
    // 検索処理
    // =========================
    document.getElementById("searchForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      clearError();

      const formData = new FormData(e.target);
      formData.append("csrf_token", csrfToken); // 検索時にもトークンを付与

      try {
        const res = await fetch("api/search_blogs.php", {
          method: "POST",
          body: formData
        });

        if (!res.ok) throw new Error("検索処理に失敗しました。");

        const data = await res.json();

        if (data.success) {
          // 検索結果を公開リスト等に反映し、公開タブを強制アクティブ化
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
    // ユーティリティ（エラー・エスケープ）
    // =========================
    function showError(msg) {
      const box = document.getElementById("alertBox");
      box.textContent = msg;
      box.className = "alert alert-danger mb-4";
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