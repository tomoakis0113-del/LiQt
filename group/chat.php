<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

// CSRF
$csrfToken = new lib\CSRFToken();

?>

<!DOCTYPE html>

<html lang="ja">

<head>

  <!-- meta -->
  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <meta name="description"
        content="グループチャットページ">

  <meta name="keywords"
        content="LiQt,SNS,コミュニティ,BLOG">

  <meta name="author"
        content="乙成,島田,勝原">

  <!-- title -->
  <title>

    グループチャット | LiQt

  </title>

  <!-- Bootstrap -->
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- CSS -->
  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <link rel="stylesheet"
        href="../custom/custom-theme.css">

  <!-- markdown -->
  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>

  <style>

    html,
    body {

      height: 100%;

    }

    body {

      background: #f8f9fa;

    }

    main {

      padding-top: 50px !important;
      padding-bottom: 260px !important;

    }

    .section-card {

      margin-bottom: 45px;

    }

    .chat-box {

      min-height: 450px;
      max-height: 700px;
      overflow-y: auto;

      background: #f8f9fa;
      border: 1px solid #ddd;
      border-radius: 20px;
      padding: 25px;

    }

    .message {

      margin-bottom: 30px;

    }

    .message-icon {

      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;

    }

    .message-content {

      background: #fff;
      border-radius: 18px;
      padding: 18px;
      margin-top: 10px;

    }

    .preview-box {

      background: #fff;
      border: 1px dashed #ccc;
      border-radius: 15px;
      padding: 20px;
      min-height: 120px;

    }

    .list-group-item {

      border-radius: 16px !important;
      padding: 20px;

    }

    .blog-item {

      margin-bottom: 20px;

    }

    .message-image {

      max-width: 300px;
      border-radius: 16px;
      margin-top: 10px;

    }

    .tag-badge {

      margin-right: 6px;
      margin-top: 8px;

    }

  </style>

</head>

<body>

  <!-- ヘッダー -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <!-- 本文 -->
  <main class="container py-5 px-4">

    <!-- メッセージ -->
    <div id="messageBox"></div>

    <!-- グループ情報 -->
    <div class="card border-0 shadow-sm rounded-4 section-card">

      <div class="card-body p-4 d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center">

          <img id="groupIcon"
               src="https://placehold.jp/100x100.png"
               width="80"
               height="80"
               class="rounded-circle me-4">

          <div>

            <h2 id="groupName"
                class="fw-bold mb-2">

              読み込み中...

            </h2>

            <p class="text-muted mb-0">

              グループチャット

            </p>

          </div>

        </div>

        <!-- 編集 -->
        <a id="editLink"
           href="#"
           class="btn btn-outline-secondary rounded-pill px-4 py-2">

          編集

        </a>

      </div>

    </div>

    <!-- チャット -->
    <div class="card border-0 shadow-sm rounded-4 section-card">

      <div class="card-body p-4">

        <h4 class="fw-bold mb-4">

          チャット

        </h4>

        <!-- チャット -->
        <div class="chat-box mb-5"
             id="chatBox">

          <div class="text-muted">

            読み込み中...

          </div>

        </div>

        <!-- 投稿フォーム -->
        <form id="messageForm">

          <!-- メッセージ -->
          <textarea
            class="form-control mb-4"
            id="messageInput"
            name="message_content"
            rows="5"
            placeholder="Markdown対応メッセージ"></textarea>

          <!-- 画像 -->
          <input
            type="file"
            class="form-control mb-4"
            name="image_upload"
            accept="image/*">

          <!-- プレビュー -->
          <div class="mb-5">

            <label class="fw-bold mb-3">

              プレビュー

            </label>

            <div id="preview"
                 class="preview-box">

              ここにMarkdownプレビューが表示されます

            </div>

          </div>

          <!-- ボタン -->
          <button class="btn btn-success w-100 py-3 rounded-pill">

            送信

          </button>

        </form>

      </div>

    </div>

    <!-- グループブログ -->
    <div class="card border-0 shadow-sm rounded-4"
         style="margin-bottom: 500px;">

      <div class="card-body p-4">

        <h4 class="fw-bold mb-4">

          グループブログ

        </h4>

        <ul class="list-group"
            id="blogList">

          <li class="list-group-item">

            読み込み中...

          </li>

        </ul>

      </div>

    </div>

  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <!-- CSRF -->
  <input type="hidden"
         id="csrf_token"
         value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <script>

    // markdown
    const md =
      window.markdownit();

    // group_id
    const params =
      new URLSearchParams(location.search);

    const groupId =
      params.get("group_id");

    // csrf
    const csrfToken =
      document.getElementById("csrf_token").value;

    // =========================
    // 初期ロード
    // =========================

    loadGroupChat();

    // =========================
    // 3秒ごと更新
    // =========================

    setInterval(() => {

      loadGroupChat();

    }, 3000);

    // 初期ロード
    loadGroupChat();

    // =========================
    // Markdownプレビュー
    // =========================

    document.getElementById("messageInput")
      .addEventListener("input", function () {

        document.getElementById("preview").innerHTML =
          md.render(this.value);

      });

    // =========================
    // グループ取得
    // =========================

    async function loadGroupChat() {

      // FormData
      const formData =
        new FormData();

      // group_id
      formData.append(
        "group_id",
        groupId
      );

      // csrf
      formData.append(
        "csrf_token",
        csrfToken
      );

      try {

        // fetch
        const response =
          await fetch(
            "../api/group/get_group_chat.php",
            {
              method: "POST",
              body: formData
            }
          );

        // text
        const text =
          await response.text();

        console.log(text);

        // json
        const result =
          JSON.parse(text);

        // エラー
        if (!result.success) {

          showMessage(
            result.message,
            "danger"
          );

          return;

        }

        const data =
          result.data;

        // グループ情報
        renderGroupInfo(data);

        // メッセージ
        renderMessages(data.messages);

        // ブログ
        renderBlogs(data.group_blogs);

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
    // グループ情報
    // =========================

    function renderGroupInfo(data) {

      // 名前
      document.getElementById("groupName").textContent =
        data.group_name;

      // アイコン
      document.getElementById("groupIcon").src =
        data.group_icon || "https://placehold.jp/100x100.png";

      // 編集リンク
      document.getElementById("editLink").href =
        `edit_group.php?group_id=${groupId}`;

    }

    // =========================
    // メッセージ表示
    // =========================

    function renderMessages(messages) {

      const chatBox =
        document.getElementById("chatBox");

      // 初期化
      chatBox.innerHTML = "";

      // メッセージなし
      if (!messages || messages.length === 0) {

        chatBox.innerHTML = `

          <div class="text-muted">

            メッセージはありません

          </div>

        `;

        return;

      }

      // ループ
      messages.forEach(message => {

        chatBox.innerHTML += `

          <div class="message d-flex">

            <!-- アイコン -->
            <img src="${message.sender_icon || 'https://placehold.jp/100x100.png'}"
                 class="message-icon me-3">

            <div class="w-100">

              <!-- 名前 -->
              <a href="/profile/profile.php?user_id=${message.sender_user_id}"
                 class="fw-bold text-decoration-none">

                ${message.sender_display_name}

              </a>

              <!-- 本文 -->
              <div class="message-content shadow-sm">

                ${md.render(message.content)}

                ${message.image_url
                  ? `<img src="${message.image_url}"
                          class="message-image img-fluid">`
                  : ""
                }

              </div>

              <!-- 日時 -->
              <div class="small text-muted mt-2">

                ${message.created_at}

              </div>

            </div>

          </div>

        `;

      });

    }

    // =========================
    // ブログ表示
    // =========================

    function renderBlogs(blogs) {

      const blogList =
        document.getElementById("blogList");

      // 初期化
      blogList.innerHTML = "";

      // ブログなし
      if (!blogs || blogs.length === 0) {

        blogList.innerHTML = `

          <li class="list-group-item">

            ブログはありません

          </li>

        `;

        return;

      }

      // ループ
      blogs.forEach(blog => {

        blogList.innerHTML += `

          <li class="list-group-item blog-item">

            <!-- タイトル -->
            <a href="/blog/blog_detail.php?blog_id=${blog.blog_id}"
               class="fw-bold text-decoration-none fs-5">

              ${blog.title}

            </a>

            <!-- 内容 -->
            <div class="mt-2 text-muted">

              ${blog.content}

            </div>

            <!-- タグ -->
            <div class="mt-3">

              ${renderTags(blog.tags)}

            </div>

            <!-- 日時 -->
            <div class="small text-muted mt-3">

              ${blog.created_at}

            </div>

          </li>

        `;

      });

    }

    // =========================
    // タグ
    // =========================

    function renderTags(tags) {

      if (!tags) {

        return "";

      }

      return tags
        .split(",")
        .map(tag => `

          <span class="badge bg-success tag-badge">

            ${tag.trim()}

          </span>

        `)
        .join("");

    }

    // =========================
    // メッセージ送信
    // =========================

    document.getElementById("messageForm")
      .addEventListener("submit", async (e) => {

        e.preventDefault();

        // FormData
        const formData =
          new FormData(e.target);

        // csrf
        formData.append(
          "csrf_token",
          csrfToken
        );

        // group_id
        formData.append(
          "group_id",
          groupId
        );

        try {

          // fetch
          const response =
            await fetch(
              "../api/group/send_message.php",
              {
                method: "POST",
                body: formData
              }
            );

          // text
          const text =
            await response.text();

          console.log(text);

          // json
          const result =
            JSON.parse(text);

          // エラー
          if (!result.success) {

            showMessage(
              result.message,
              "danger"
            );

            return;

          }

          // 成功
          showMessage(
            result.message,
            "success"
          );

          // フォームリセット
          e.target.reset();

          // プレビュー初期化
          document.getElementById("preview").innerHTML =
            "ここにMarkdownプレビューが表示されます";

          // 再読み込み
          loadGroupChat();

        }

        catch (error) {

          console.error(error);

          showMessage(
            "通信エラーが発生しました",
            "danger"
          );

        }

      });

    // =========================
    // メッセージ表示
    // =========================

    function showMessage(message, type) {

      document.getElementById("messageBox").innerHTML = `

        <div class="alert alert-${type}">

          ${message}

        </div>

      `;

    }

  </script>

</body>

</html>