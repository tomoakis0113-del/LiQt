<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>ブログ編集</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- markdown -->
  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>

  <style>
    .preview-box {
      border: 1px dashed #ccc;
      padding: 10px;
      background: #fff;
    }
  </style>
</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container p-4" style="max-width:900px;">

    <h3 class="mb-4">ブログ編集</h3>

    <div id="alertBox" class="alert d-none"></div>

    <form id="editForm">

      <!-- タイトル -->
      <div class="mb-3">
        <label class="form-label">タイトル</label>
        <input type="text" name="title" id="titleInput" class="form-control">
      </div>

      <!-- タグ -->
      <div class="mb-3">
        <label class="form-label">タグ</label>
        <input type="text" name="tags" id="tagsInput" class="form-control">
      </div>

      <!-- 本文 -->
      <div class="mb-3">
        <label class="form-label">本文（Markdown）</label>
        <textarea name="content" id="contentInput" class="form-control" rows="8"></textarea>
      </div>

      <!-- プレビュー -->
      <div class="mb-3">
        <label class="form-label">プレビュー</label>
        <div id="preview" class="preview-box"></div>
      </div>

      <!-- 公開設定 -->
      <div class="mb-3">
        <label class="form-label">公開設定</label>
        <select name="visibility" id="visibility" class="form-select">
          <option value="public">公開</option>
          <option value="private">非公開</option>
          <option value="group">グループ</option>
        </select>
      </div>

      <!-- 更新 -->
      <button class="btn btn-primary w-100 mb-2">更新する</button>

      <!-- 削除 -->
      <button type="button" id="deleteBtn" class="btn btn-danger w-100">
        削除する
      </button>

    </form>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    const md = window.markdownit();

    // blog_id取得
    const params = new URLSearchParams(location.search);
    const blogId = params.get("blog_id");

    // 初期取得
    fetch(`api/get_blog_detail.php?blog_id=${blogId}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) return redirect();

        const d = data;

        // 投稿者チェック（モック：is_authorで判断想定）
        if (!d.is_author) {
          redirect();
          return;
        }

        // フォーム反映
        document.getElementById("titleInput").value = d.title;
        document.getElementById("tagsInput").value = d.tags;
        document.getElementById("contentInput").value = d.content;
        document.getElementById("visibility").value = d.visibility;

        // 初期プレビュー
        updatePreview();
      });

    // プレビュー
    document.getElementById("contentInput").addEventListener("input", updatePreview);

    function updatePreview() {
      const text = document.getElementById("contentInput").value;
      document.getElementById("preview").innerHTML = md.render(text);
    }

    // 更新
    document.getElementById("editForm").addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
      formData.append("blog_id", blogId);

      const res = await fetch("api/update_blog.php", {
        method: "POST",
        body: formData
      });

      const data = await res.json();

      if (data.success) {
        location.href = `blog_detail.php?blog_id=${blogId}`;
      } else {
        showError(data.message);
      }
    });

    // 削除
    document.getElementById("deleteBtn").addEventListener("click", async () => {
      if (!confirm("本当に削除する？")) return;

      const res = await fetch("api/delete_blog.php", {
        method: "POST",
        body: new URLSearchParams({
          blog_id: blogId
        })
      });

      const data = await res.json();

      if (data.success) {
        location.href = "blogs.php";
      } else {
        showError(data.message);
      }
    });

    // リダイレクト
    function redirect() {
      location.href = `blog_detail.php?blog_id=${blogId}`;
    }

    // エラー
    function showError(msg) {
      const box = document.getElementById("alertBox");
      box.textContent = msg;
      box.className = "alert alert-danger";
    }
  </script>

</body>

</html>