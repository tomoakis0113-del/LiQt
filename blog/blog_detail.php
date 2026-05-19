<!DOCTYPE html>
<html lang="ja">

<head>

  <!-- meta -->
  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <title>
    ブログ詳細 | LiQt
  </title>

  <!-- Bootstrap -->
  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- markdown -->
  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>
  <!-- markdown -->
  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>

  <!-- Icons -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

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

  <!-- ヘッダー -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <!-- 本文 -->
  <main class="container py-4"
        style="max-width: 900px; padding-bottom: 120px;">

    <!-- ブログカード -->
    <div class="card border-0 shadow-sm rounded-4">

      <div class="card-body p-4">

        <!-- タイトル -->
        <h1 id="title"
            class="fw-bold mb-3">
        </h1>

        <!-- タグ -->
        <div id="tags"
             class="mb-4">
        </div>

        <!-- 投稿者 -->
        <div class="d-flex align-items-center mb-4">

          <!-- アイコン -->
          <img id="authorIcon"
               class="author-icon me-3"
               alt="投稿者アイコン">

          <!-- 名前 -->
          <div>

            <a id="authorName"
               class="fw-bold text-decoration-none">
            </a>

          </div>

        </div>

        <!-- 本文 -->
        <div id="content"
             class="mb-5">
        </div>

        <!-- いいね -->
        <div class="mb-4">

          <button id="likeButton"
                  class="btn btn-outline-danger rounded-pill"
                  onclick="toggleLike()">

            ❤️ いいね
            <span id="likes"></span>

          </button>

        </div>

        <hr class="my-4">

        <!-- コメント -->
        <h4 class="fw-bold mb-4">

          コメント

        </h4>

        <!-- コメント入力 -->
        <div class="card border-0 bg-light rounded-4 mb-4">

          <div class="card-body">

            <textarea id="commentInput"
                      class="form-control mb-3"
                      rows="3"
                      placeholder="コメントを書く">
            </textarea>

            <div class="text-end">

              <button class="btn btn-success px-4"
                      onclick="postComment()">

                コメント投稿

              </button>

            </div>

          </div>

        </div>

        <!-- コメント一覧 -->
        <div id="commentList"></div>

        <hr class="my-4">

        <!-- 関連記事 -->
        <h4 class="fw-bold mb-4">

          関連記事

        </h4>

        <!-- 関連記事一覧 -->
        <div id="relatedList"></div>

      </div>

    </div>

  </main>
  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>

    // markdown
    const md = window.markdownit();

    // URLパラメータ
    const params =
      new URLSearchParams(location.search);

    // blog_id取得
    const blogId =
      params.get("blog_id");

    // いいね状態
    let liked = false;

    // 初期ロード
    loadBlog();

    // =========================
    // ブログ取得
    // =========================

    function loadBlog() {

      fetch(`api/get_blog_detail.php?blog_id=${blogId}`)
        .then(res => res.json())
        .then(data => {

          // エラー
          if (!data.success) {

            alert(data.message);
            return;

          }

          // データ
          const d = data;

          // タイトル
          document.getElementById("title").textContent =
            d.title;

          // タグ
          document.getElementById("tags").innerHTML =
            renderTags(d.tags);

          // 投稿者
          document.getElementById("authorIcon").src =
            d.author.icon_url;

          document.getElementById("authorName").textContent =
            d.author.display_name;

          document.getElementById("authorName").href =
            `/profile/profile.php?user_id=${d.author.user_id}`;

          // 本文
          document.getElementById("content").innerHTML =
            md.render(d.content);

          // いいね
          document.getElementById("likes").textContent =
            d.likes;

          // コメント
          renderComments(d.comments);

          // 関連記事
          renderRelated(d.related_blogs);

        });

    }

    // =========================
    // タグ表示
    // =========================

    function renderTags(tags) {

      // タグなし
      if (!tags) {

        return "";

      }

      // カンマ区切り
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

      const commentList =
        document.getElementById("commentList");

      // 初期化
      commentList.innerHTML = "";

      // コメントなし
      if (!comments || comments.length === 0) {

        commentList.innerHTML = `

          <div class="text-muted">

            コメントはまだありません

          </div>

        `;

        return;

      }

      // コメントループ
      comments.forEach(comment => {

        commentList.innerHTML += `

          <div class="d-flex mb-4">

            <!-- アイコン -->
            <img src="${comment.author.icon_url}"
                 class="comment-icon me-3"
                 alt="コメント投稿者">

            <!-- 内容 -->
            <div class="w-100">

              <!-- 名前 -->
              <a href="/profile/profile.php?user_id=${comment.author.user_id}"
                 class="fw-bold text-decoration-none">

                ${comment.author.display_name}

              </a>

              <!-- コメント -->
              <div class="mt-1">

                ${comment.content}

              </div>

            </div>

          </div>

        `;

      });

    }

    // =========================
    // 関連記事表示
    // =========================

    function renderRelated(blogs) {

      const relatedList =
        document.getElementById("relatedList");

      // 初期化
      relatedList.innerHTML = "";

      // 関連記事なし
      if (!blogs || blogs.length === 0) {

        relatedList.innerHTML = `

          <div class="text-muted">

            関連記事はありません

          </div>

        `;

        return;

      }

      // 関連記事ループ
      blogs.forEach(blog => {

        relatedList.innerHTML += `

          <a href="/blog/blog_detail.php?blog_id=${blog.blog_id}"
             class="related-link
                    d-block
                    border
                    rounded-4
                    p-3
                    mb-3
                    shadow-sm
                    text-dark">

            <!-- タイトル -->
            <div class="fw-bold fs-5 mb-2">

              ${blog.title}

            </div>

            <!-- タグ -->
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

      // 入力取得
      const comment =
        document.getElementById("commentInput").value.trim();

      // 空チェック
      if (!comment) {

        alert("コメントを入力してください");
        return;

      }

      // コメント一覧
      const commentList =
        document.getElementById("commentList");

      // コメントなし削除
      if (commentList.innerHTML.includes("コメントはまだありません")) {

        commentList.innerHTML = "";

      }

      // コメント追加
      commentList.innerHTML =

        `
          <div class="d-flex mb-4">

            <!-- アイコン -->
            <img src="https://placehold.jp/48x48.png"
                 class="comment-icon me-3">

            <!-- 内容 -->
            <div class="w-100">

              <!-- 名前 -->
              <a href="/profile/profile.php?user_id=999"
                 class="fw-bold text-decoration-none">

                あなた

              </a>

              <!-- コメント -->
              <div class="mt-1">

                ${comment}

              </div>

            </div>

          </div>
        `

        + commentList.innerHTML;

      // 入力リセット
      document.getElementById("commentInput").value = "";

    }

    // =========================
    // いいね
    // =========================

    function toggleLike() {

      // 現在の数
      let likes =
        parseInt(document.getElementById("likes").textContent);

      // ボタン
      const button =
        document.getElementById("likeButton");

      // いいね済み
      if (liked) {

        likes--;

        button.classList.remove("btn-danger");
        button.classList.add("btn-outline-danger");

        liked = false;

      }

      // 未いいね
      else {

        likes++;

        button.classList.remove("btn-outline-danger");
        button.classList.add("btn-danger");

        liked = true;

      }

      // 更新
      document.getElementById("likes").textContent =
        likes;

    }

  </script>

</body>

</html>