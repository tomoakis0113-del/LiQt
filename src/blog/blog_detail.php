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

  <title>ブログ詳細 | LiQt</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

  <style>
    /* 投稿者アイコン */
    .author-icon,
    .comment-icon {
      width: 48px;
      height: 48px;
      border-radius: 50%;
      object-fit: cover;
    }

    /* タグ */
    .tag-badge {
      margin-right: 6px;
    }

    /* 本文 */
    #content {
      line-height: 1.9;
    }

    /* 関連記事 */
    .related-link {
      text-decoration: none;
      transition: 0.2s;
    }

    .related-link:hover {
      transform: translateY(-2px);
      background-color: #f8f9fa;
    }
  </style>

</head>

<body>

  <input type="hidden" id="csrf_token" value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container py-4" style="max-width: 900px; padding-bottom: 240px;">

    <div class="card border-0 shadow-sm rounded-4">
      <div class="card-body p-4">

        <div class="d-flex justify-content-between align-items-start mb-3">
          <h1 id="title" class="fw-bold mb-0"></h1>

          <a id="editButton" href="#" class="btn btn-outline-primary d-none">
            編集
          </a>
        </div>

        <div id="tags" class="mb-4"></div>

        <div class="d-flex align-items-center mb-4">
          <img id="authorIcon" class="author-icon me-3" alt="投稿者アイコン">

          <div>
            <a id="authorName" class="fw-bold text-decoration-none"></a>
          </div>
        </div>

        <div id="content" class="mb-5"></div>

        <div class="mb-4">
          <button id="likeButton" class="btn btn-outline-danger rounded-pill" onclick="toggleLike()">
            ❤️ いいね
            <span id="likes">0</span>
          </button>
        </div>

        <hr class="my-4">

        <h4 class="fw-bold mb-4">コメント</h4>

        <div class="card border-0 bg-light rounded-4 mb-4">
          <div class="card-body">
            <textarea id="commentInput" class="form-control mb-3" rows="3" placeholder="コメントを書く"></textarea>
            <div class="text-end">
              <button class="btn btn-success px-4" onclick="postComment()">
                コメント投稿
              </button>
            </div>
          </div>
        </div>

        <div id="commentList"></div>

        <hr class="my-4">

        <h4 class="fw-bold mb-4">関連記事</h4>

        <div id="relatedList"></div>

      </div>
    </div>

    <div style="margin-bottom: 12rem;"></div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    // markdown
    const md = window.markdownit();

    // URLパラメータ
    const params = new URLSearchParams(location.search);

    // blog_id取得
    const blogId = params.get("blog_id");

    // HTMLに埋め込んだCSRFトークンの取得
    const csrfToken = document.getElementById("csrf_token").value;

    // いいね状態を管理する変数
    let liked = false;

    // 初期ロード実行
    loadBlog();

    // =========================
    // ブログ取得
    // =========================
    function loadBlog() {
      if (!blogId) {
        alert("ブログIDが指定されていません");
        return;
      }

      const formData = new FormData();
      formData.append("blog_id", blogId);
      formData.append("csrf_token", csrfToken);

      fetch("/api/blog/get_blog_detail.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(result => {
          if (!result.success) {
            alert(result.message);
            return;
          }

          const blogData = result.data;

          document.getElementById("title").textContent = blogData.title;

          const editButton = document.getElementById("editButton");
          editButton.href = `/blog/edit_blog.php?blog_id=${blogData.id}`;
          editButton.classList.remove("d-none");

          document.getElementById("tags").innerHTML = renderTags(blogData.tags);

          if(!blogData.author) {
            alert("ブログの投稿ユーザー情報の取得に失敗しました");
            return;
          }

          document.getElementById("authorIcon").src = blogData.author.icon_url || "https://placehold.jp/48x48.png";
          document.getElementById("authorName").textContent = blogData.author.display_name || "ユーザー";
          document.getElementById("authorName").href = `/profile/profile.php?user_id=${blogData.author.user_id}`;

          document.getElementById("content").innerHTML = md.render(blogData.content || "");

          // いいね件数
          document.getElementById("likes").textContent = blogData.likes ?? 0;
          liked = blogData.is_liked ?? false;

          const likeButton = document.getElementById("likeButton");

          if (liked) {
            likeButton.classList.remove("btn-outline-danger");
            likeButton.classList.add("btn-danger");
          } else {
            likeButton.classList.remove("btn-danger");
            likeButton.classList.add("btn-outline-danger");
          }
          // コメント・関連記事一覧表示
          renderComments(blogData.comments || []);
          renderRelated(blogData.related_blogs || []);
        })
        .catch(error => {
          console.error("Error fetching blog details:", error);
          alert("ブログ詳細の取得中に通信エラーが発生しました");
        });
    }

    // =========================
    // いいね（確実に+1 / -1 するトグル修正版）
    // =========================
    function toggleLike() {
      if (!blogId) {
        alert("ブログIDが不明なため、いいねの操作ができません");
        return;
      }

      const button = document.getElementById("likeButton");
      const likesWord = document.getElementById("likes");
      let currentLikes = parseInt(likesWord.textContent) || 0;

      const apiUrl = "/api/blog/blog_like.php";

      const formData = new FormData();
      formData.append("blog_id", blogId);
      formData.append("csrf_token", csrfToken);

      fetch(apiUrl, {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(result => {
          if (!result.success) {
            alert(result.message);
            return;
          }

          // 💡 現在のボタンの見た目（赤色＝いいね済）を基準にトグル処理を行う
          const isCurrentlyLiked = button.classList.contains("btn-danger");

          if (!isCurrentlyLiked) {
            // 現在が「未いいね」なら、ボタンを赤くしてカウントを【プラス1】
            button.classList.remove("btn-outline-danger");
            button.classList.add("btn-danger");
            currentLikes++;
            liked = true;
          } else {
            // 現在が「いいね済」なら、ボタンを白抜きにしてカウントを【マイナス1】
            button.classList.remove("btn-danger");
            button.classList.add("btn-outline-danger");
            currentLikes--;
            liked = false;
          }

          // 計算結果を画面に反映（念のため0未満にならないセーフティを適用）
          likesWord.textContent = currentLikes < 0 ? 0 : currentLikes;
        })
        .catch(error => {
          console.error("Error toggling like:", error);
          alert("いいねの処理中に通信エラーが発生しました");
        });
    }

    // =========================
    // タグ表示
    // =========================
    function renderTags(tags) {
      if (!tags) {
        return "";
      }
      return tags.split(",").map(tag =>
        `
          <span class="badge bg-success tag-badge">
            ${tag.trim()}
          </span>
        `
      ).join("");
    }

    // =========================
    // コメント表示
    // =========================
    function renderComments(comments) {
      const commentList = document.getElementById("commentList");
      commentList.innerHTML = "";

      if (!comments || comments.length === 0) {
        commentList.innerHTML = `<div class="text-muted">コメントはまだありません</div>`;
        return;
      }

      comments.forEach(comment => {
        const iconUrl = comment.author && comment.author.icon_url ? comment.author.icon_url : "https://placehold.jp/48x48.png";
        const displayName = comment.author && comment.author.display_name ? comment.author.display_name : "名無しユーザー";
        const userId = comment.author && comment.author.user_id ? comment.author.user_id : "#";

        const deleteButton = comment.is_mine ?
          `<button class="btn btn-sm btn-outline-danger mt-2" onclick="deleteComment(${comment.comment_id})">
         削除
       </button>` :
          "";

        commentList.innerHTML += `
    <div class="d-flex mb-4">
      <img src="${iconUrl}" class="comment-icon me-3" alt="コメント投稿者">
      <div class="w-100">
        <a href="/profile/profile.php?user_id=${userId}" class="fw-bold text-decoration-none">
          ${displayName}
        </a>
        <div class="mt-1" style="white-space: pre-wrap;">${comment.content}</div>
        ${deleteButton}
      </div>
    </div>
  `;
      });
    }

    // =========================
    // 関連記事表示
    // =========================
    function renderRelated(blogs) {
      const relatedList = document.getElementById("relatedList");
      relatedList.innerHTML = "";

      if (!blogs || blogs.length === 0) {
        relatedList.innerHTML = `<div class="text-muted">関連記事はありません</div>`;
        return;
      }

      blogs.forEach(blog => {
        relatedList.innerHTML += `
          <a href="/blog/blog_detail.php?blog_id=${blog.blog_id}"
             class="related-link d-block border rounded-4 p-3 mb-3 shadow-sm text-dark">
            <div class="fw-bold fs-5 mb-2">
              ${blog.title}
            </div>
            <div>
              ${renderTags(blog.tags)}
            </div>
          </a>
        `;
      });
    }

    // =========================
    // コメント投稿
    // =========================
    function postComment() {
      const comment = document.getElementById("commentInput").value.trim();

      if (!comment) {
        alert("コメントを入力してください");
        return;
      }

      const formData = new FormData();
      formData.append("csrf_token", csrfToken);
      formData.append("blog_id", blogId);
      formData.append("content", comment);

      fetch("/api/blog/add_comment.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(result => {
          if (!result.success) {
            alert(result.message);
            return;
          }

          document.getElementById("commentInput").value = "";

          // コメント追加後、ブログ詳細を再読み込みしてコメント一覧を更新
          loadBlog();
        })
        .catch(error => {
          console.error("Error adding comment:", error);
          alert("コメント投稿中に通信エラーが発生しました");
        });
    }
    // =========================
    // いいね
    // =========================
    function toggleLike() {
      const formData = new FormData();
      formData.append("blog_id", blogId);
      formData.append("csrf_token", csrfToken);

      fetch("/api/blog/toggle_like.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(result => {
          if (!result.success) {
            alert(result.message);
            return;
          }

          liked = result.data.is_liked;
          document.getElementById("likes").textContent = result.data.like_count;

          const button = document.getElementById("likeButton");

          if (liked) {
            button.classList.remove("btn-outline-danger");
            button.classList.add("btn-danger");
          } else {
            button.classList.remove("btn-danger");
            button.classList.add("btn-outline-danger");
          }
        });
    }

    function deleteComment(commentId) {
      if (!confirm("このコメントを削除しますか？")) {
        return;
      }

      const formData = new FormData();
      formData.append("csrf_token", csrfToken);
      formData.append("comment_id", commentId);

      fetch("/api/blog/delete_comment.php", {
          method: "POST",
          body: formData
        })
        .then(res => res.json())
        .then(result => {
          if (!result.success) {
            alert(result.message);
            return;
          }

          // コメント削除後、ブログ詳細を再読み込みしてコメント一覧を更新
          loadBlog();
        })
        .catch(error => {
          console.error("Error deleting comment:", error);
          alert("コメント削除中に通信エラーが発生しました");
        });
    }
  </script>

</body>

</html>