<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>ブログ詳細</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- markdown -->
  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>

  <style>
    .author-icon,
    .comment-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
    }

    .tag-badge {
      margin-right: 5px;
    }
  </style>
</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container p-4" style="max-width:900px;">

    <!-- タイトル -->
    <h2 id="title" class="fw-bold mb-2"></h2>

    <!-- タグ -->
    <div id="tags" class="mb-3"></div>

    <!-- 投稿者 -->
    <div class="d-flex align-items-center mb-4">
      <img id="authorIcon" class="author-icon me-2">
      <a id="authorName"></a>
    </div>

    <!-- 本文 -->
    <div id="content" class="mb-4"></div>

    <!-- いいね -->
    <div class="mb-4">
      👍 <span id="likes"></span>
    </div>

    <hr>

    <!-- コメント -->
    <h5>コメント</h5>
    <div id="commentList" class="mb-4"></div>

    <hr>

    <!-- 関連記事 -->
    <h5>関連記事</h5>
    <div id="relatedList" class="list-group"></div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    const md = window.markdownit();

    // blog_id取得
    const params = new URLSearchParams(location.search);
    const blogId = params.get("blog_id");

    // 初期ロード
    fetch(`api/get_blog_detail.php?blog_id=${blogId}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) return alert(data.message);

        const d = data;

        // タイトル
        document.getElementById("title").textContent = d.title;

        // タグ
        document.getElementById("tags").innerHTML = renderTags(d.tags);

        // 投稿者
        document.getElementById("authorIcon").src = d.author.icon_url;
        document.getElementById("authorName").textContent = d.author.display_name;
        document.getElementById("authorName").href =
          `profile.php?user_id=${d.author.user_id}`;

        // 本文（Markdown）
        document.getElementById("content").innerHTML =
          md.render(d.content);

        // いいね
        document.getElementById("likes").textContent = d.likes;

        // コメント
        renderComments(d.comments);

        // 関連記事
        renderRelated(d.related_blogs);
      });

    // タグ
    function renderTags(tags) {
      if (!tags) return "";
      return tags.split(",").map(t =>
        `<span class="badge bg-primary tag-badge">${t.trim()}</span>`
      ).join("");
    }

    // コメント
    function renderComments(comments) {
      const box = document.getElementById("commentList");
      box.innerHTML = "";

      comments.forEach(c => {
        box.innerHTML += `
      <div class="d-flex mb-3">
        <img src="${c.author.icon_url}" class="comment-icon me-2">
        <div>
          <a href="profile.php?user_id=${c.author.user_id}">
            ${c.author.display_name}
          </a>
          <div>${c.content}</div>
        </div>
      </div>
    `;
      });
    }

    // 関連記事
    function renderRelated(blogs) {
      const list = document.getElementById("relatedList");
      list.innerHTML = "";

      blogs.forEach(b => {
        list.innerHTML += `
      <a href="blog_detail.php?blog_id=${b.blog_id}"
         class="list-group-item">
        <div class="fw-bold">${b.title}</div>
        <div>${renderTags(b.tags)}</div>
      </a>
    `;
      });
    }
  </script>

</body>

</html>