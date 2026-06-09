<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

// CSRF
$csrfToken = new lib\CSRFToken();

?>

<!DOCTYPE html>

<html lang="ja">

<head>

  <meta charset="UTF-8">

  <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

  <meta name="description"
    content="グループチャットページ">

  <meta name="keywords"
    content="LiQt,SNS,コミュニティ,BLOG">

  <meta name="author"
    content="乙成,島田,勝原">

  <title>

    グループチャット | LiQt

  </title>

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet"
    href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <link rel="stylesheet"
    href="../custom/custom-theme.css">

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

      scroll-behavior: smooth;

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

      word-break: break-word;

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

    .alert {

      border-radius: 16px;

    }

    /* メンバー一覧用の追加スタイル */
    .member-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
    }
  </style>

</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container py-5 px-4">

    <div id="messageBox"></div>

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

        <a id="editLink"
          href="#"
          class="btn btn-outline-secondary rounded-pill px-4 py-2">

          編集

        </a>

      </div>

    </div>

    <div class="card border-0 shadow-sm rounded-4 section-card">
      <div class="card-body p-4">
        <h4 class="fw-bold mb-3">グループメンバー</h4>
        <div id="memberList" class="d-flex flex-wrap gap-3">
          <div class="text-muted">読み込み中...</div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 section-card">

      <div class="card-body p-4">

        <h4 class="fw-bold mb-4">

          チャット

        </h4>

        <div class="chat-box mb-5"
          id="chatBox">

          <div class="text-muted">

            読み込み中...

          </div>

        </div>

        <form id="messageForm"
          enctype="multipart/form-data">

          <textarea
            class="form-control mb-4"
            id="messageInput"
            name="message_content"
            rows="5"
            placeholder="Markdown対応メッセージ"></textarea>

          <input
            type="file"
            class="form-control mb-4"
            id="imageUpload"
            name="image_upload"
            accept="image/*">

          <div class="mb-5">

            <label class="fw-bold mb-3">

              プレビュー

            </label>

            <div id="preview"
              class="preview-box">

              ここにMarkdownプレビューが表示されます

            </div>

          </div>

          <button id="submitButton"
            class="btn btn-success w-100 py-3 rounded-pill">

            送信

          </button>

        </form>

      </div>

    </div>

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

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <input type="hidden"
    id="csrf_token"
    value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <script>
    // markdown
    const md = window.markdownit({
      html: false,
      linkify: true,
      typographer: true
    });

    // group_id
    const params =
      new URLSearchParams(location.search);

    const groupId =
      params.get("group_id");

    // csrf
    const csrfToken =
      document.getElementById("csrf_token").value;

    // 初回ロード
    let firstLoad = true;

    // =========================
    // group_idチェック
    // =========================

    if (!groupId) {

      showMessage(
        "グループIDが存在しません",
        "danger"
      );

      throw new Error("group_id not found");

    }

    // =========================
    // 初期ロード
    // =========================

    loadGroupChat();

    // =========================
    // 3秒ごと更新
    // =========================

    setInterval(() => {

      loadGroupChat(false);

    }, 3000);

    // =========================
    // Markdownプレビュー
    // =========================

    document.getElementById("messageInput")
      .addEventListener("input", function() {

        document.getElementById("preview").innerHTML =
          md.render(this.value);

      });

    // =========================
    // 画像プレビュー
    // =========================

    document.getElementById("imageUpload")
      .addEventListener("change", function(e) {

        const file =
          e.target.files[0];

        // リセット
        const preview =
          document.getElementById("preview");

        preview.innerHTML =
          md.render(
            document.getElementById("messageInput").value
          );

        if (!file) {

          return;

        }

        // 画像チェック
        if (!file.type.startsWith("image/")) {

          showMessage(
            "画像ファイルを選択してください",
            "danger"
          );

          e.target.value = "";

          return;

        }

        // 5MB制限
        if (file.size > 5 * 1024 * 1024) {

          showMessage(
            "画像サイズは5MB以下にしてください",
            "danger"
          );

          e.target.value = "";

          return;

        }

        const reader =
          new FileReader();

        reader.onload = function(event) {

          preview.innerHTML += `

            <div class="mt-4">

              <img src="${event.target.result}"
                   class="img-fluid rounded-4 shadow-sm"
                   style="max-height:300px;">

            </div>

          `;

        };

        reader.readAsDataURL(file);

      });

    // =========================
    // グループ取得
    // =========================

    async function loadGroupChat(scrollBottom = true) {

      // FormData
      const formData =
        new FormData();

      // group_id
      formData.append(
        "group_id",
        groupId
      );

      try {

        // fetch
        const response =
          await fetch(
            "../api/group/get_group_chat.php", {
              method: "POST",
              body: formData
            }
          );

        // text
        const text =
          await response.text();

        console.log(text);

        // json
        let result;

        try {

          result = JSON.parse(text);

        } catch {

          console.error(text);

          showMessage(
            "APIレスポンス形式が不正です",
            "danger"
          );

          return;

        }

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
        renderMessages(
          data.messages,
          scrollBottom
        );

        // ブログ
        renderBlogs(data.group_blogs);
      } catch (error) {

        console.error(error);

        showMessage(
          "通信エラーが発生しました",
          "danger"
        );

      }

      // メンバー一覧の表示
      const memberFormData = new FormData();
      memberFormData.append("group_id", groupId);
      memberFormData.append("csrf_token", csrfToken);
      await fetch('/api/group/get_group_info.php', {
        method: 'POST',
        body: memberFormData
      }).then(res => res.json())
        .then(result => {
          if (result.success) {
            renderMembers(result.data.members);
          } else {
            showMessage(result.message, "danger");
          }
        })
        .catch(error => {
          console.error("Error fetching group info:", error);
          showMessage("通信エラーが発生しました", "danger");
        });
    }

    // =========================
    // グループ情報
    // =========================

    function renderGroupInfo(data) {

      // 名前
      document.getElementById("groupName").textContent =
        data.group_name || "名称未設定";

      // アイコン
      document.getElementById("groupIcon").src =
        data.group_icon || "https://placehold.jp/100x100.png";

      // 編集リンク
      document.getElementById("editLink").href =
        `edit_group.php?group_id=${groupId}`;

    }

    // =========================
    // メンバー一覧表示 (新規追加)
    // =========================

    function renderMembers(members) {
      const memberList = document.getElementById("memberList");
      memberList.innerHTML = "";

      if (!members || members.length === 0) {
        memberList.innerHTML = `<div class="text-muted">メンバーはいません</div>`;
        return;
      }

      members.forEach(member => {
        memberList.innerHTML += `
          <a href="/profile/profile.php?user_id=${member.user_id_str}" 
             class="d-flex align-items-center text-decoration-none text-dark bg-white p-2 rounded-pill shadow-sm border"
             style="transition: background-color 0.2s;">
            <img src="${member.icon_url || 'https://placehold.jp/100x100.png'}" 
                 class="member-icon me-2">
            <span class="fw-bold pe-2 small">${escapeHtml(member.display_name)}</span>
          </a>
        `;
      });
    }

    // =========================
    // メッセージ表示
    // =========================

    function renderMessages(messages, scrollBottom = true) {

      const chatBox =
        document.getElementById("chatBox");

      // スクロール位置
      const isNearBottom =
        chatBox.scrollHeight -
        chatBox.scrollTop -
        chatBox.clientHeight < 150;

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

            <img src="${message.sender_icon || 'https://placehold.jp/100x100.png'}"
                 class="message-icon me-3">

            <div class="w-100">

              <a href="/profile/profile.php?user_id=${message.sender_user_id}"
                 class="fw-bold text-decoration-none">

                ${escapeHtml(message.sender_display_name)}

              </a>

              <div class="message-content shadow-sm">

                ${md.render(message.content || "")}

                ${message.image_url
                  ? `<img src="/${message.image_url}"
                          class="message-image img-fluid">`
                  : ""
                }

              </div>

              <div class="small text-muted mt-2">

                ${message.created_at || ""}

              </div>

            </div>

          </div>

        `;

      });

      // スクロール
      if (firstLoad || isNearBottom || scrollBottom) {

        chatBox.scrollTop =
          chatBox.scrollHeight;

      }

      firstLoad = false;

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

            <a href="/blog/blog_detail.php?blog_id=${blog.blog_id}"
               class="fw-bold text-decoration-none fs-5">

              ${escapeHtml(blog.title)}

            </a>

            <div class="mt-2 text-muted">

              ${escapeHtml(blog.content)}

            </div>

            <div class="mt-3">

              ${renderTags(blog.tags)}

            </div>

            <div class="small text-muted mt-3">

              ${blog.created_at || ""}

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

            ${escapeHtml(tag.trim())}

          </span>

        `)
        .join("");

    }

    // =========================
    // HTMLエスケープ
    // =========================

    function escapeHtml(str) {

      if (!str) {

        return "";

      }

      return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    }

    // =========================
    // メッセージ送信
    // =========================

    document.getElementById("messageForm")
      .addEventListener("submit", async (e) => {

        e.preventDefault();

        // ボタン
        const submitButton =
          document.getElementById("submitButton");

        // 内容
        const messageContent =
          document.getElementById("messageInput")
          .value
          .trim();

        // 画像
        const imageFile =
          document.getElementById("imageUpload")
          .files[0];

        // 空送信禁止
        if (!messageContent && !imageFile) {

          showMessage(
            "メッセージを入力してください",
            "danger"
          );

          return;

        }

        // FormData
        const formData =
          new FormData();

        // message
        formData.append(
          "message_content",
          messageContent
        );

        // image
        if (imageFile) {

          formData.append(
            "image_upload",
            imageFile
          );

        }

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

          // ボタン無効
          submitButton.disabled = true;

          submitButton.innerHTML =
            "送信中...";

          // fetch
          const response =
            await fetch(
              "../api/group/send_message.php", {
                method: "POST",
                body: formData
              }
            );

          // text
          const text =
            await response.text();

          console.log(text);

          // json
          let result;

          try {

            result = JSON.parse(text);

          } catch {

            console.error(text);

            showMessage(
              "APIレスポンス形式が不正です",
              "danger"
            );

            return;

          }

          // エラー
          if (!result.success) {

            showMessage(
              result.message ||
              "送信に失敗しました",
              "danger"
            );

            return;

          }

          // 成功
          showMessage(
            result.message ||
            "送信しました",
            "success"
          );

          // リセット
          e.target.reset();

          // preview reset
          document.getElementById("preview").innerHTML =
            "ここにMarkdownプレビューが表示されます";

          // 再読み込み
          await loadGroupChat(true);

          // フォーカス
          document.getElementById("messageInput")
            .focus();

        } catch (error) {

          console.error(error);

          showMessage(
            "通信エラーが発生しました",
            "danger"
          );

        } finally {

          // ボタン戻す
          submitButton.disabled = false;

          submitButton.innerHTML =
            "送信";

        }

      });

    // =========================
    // メッセージ表示
    // =========================

    function showMessage(message, type) {

      document.getElementById("messageBox").innerHTML = `

        <div class="alert alert-${type} shadow-sm">

          ${message}

        </div>

      `;

    }
  </script>

</body>

</html>