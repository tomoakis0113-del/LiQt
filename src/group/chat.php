<?php
require_once __DIR__ . '/../component/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php';

// CSRF
$csrfToken = new lib\CSRFToken();

// ログイン中ユーザーID取得
$sessionHandler = new lib\Session();
$currentUserId = $sessionHandler->getCurrentUserID();

?>

<!DOCTYPE html>
<html lang="ja">

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta name="description" content="グループチャットページ">

  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">

  <meta name="author" content="乙成,島田,勝原">

  <title>
    グループチャット | LiQt
  </title>

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <link rel="stylesheet" href="../custom/custom-theme.css">

  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>

  <style>
    html,
    body {
      height: 100%;
    }

    body {
      background: #eef5ff;
      overflow: hidden;
    }

    main {
      height: calc(100vh - 120px);
      padding: 0 !important;
    }

    .chat-page-wrapper {
      height: 100%;
      max-width: none;
      margin: 0;
    }

    #messageBox {
      position: fixed;
      top: 75px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 9999;
      width: min(90%, 700px);
    }

    .alert {
      border-radius: 16px;
    }

    /* LINE風チャット全体 */
    .line-chat-card {
      height: 100%;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border-radius: 24px;
      background: #ffffff;
    }

    /* 上のグループ情報 */
    .line-chat-header {
      background: #ffffff;
      border-bottom: 1px solid #d8e6f7;
      padding: 14px 18px;
      flex-shrink: 0;
    }

    .group-icon {
      width: 48px;
      height: 48px;
      object-fit: cover;
      border-radius: 50%;
    }

    /* メッセージ一覧だけスクロール */
    .chat-box {
      flex: 1;
      overflow-y: auto;
      background: linear-gradient(180deg, #ddecff 0%, #f7fbff 100%);
      padding: 20px;
      min-height: 0;
      scroll-behavior: smooth;
    }

    .empty-message {
      text-align: center;
      color: #6c757d;
      margin-top: 40px;
    }

    /* メッセージ全体 */
    .message {
      display: flex;
      align-items: flex-end;
      gap: 10px;
      margin-bottom: 16px;
      width: 100%;
    }

    .message.other {
      justify-content: flex-start;
    }

    .message.mine {
      justify-content: flex-end;
    }

    .message-icon {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      object-fit: cover;
      flex-shrink: 0;
    }

    .message-body {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      max-width: 70%;
    }

    .message.mine .message-body {
      align-items: flex-end;
      margin-left: auto;
    }
    .message.mine .message-content {
  text-align: left;
}

    .message.other .message-body {
      align-items: flex-start;
    }

    .message-name {
      font-size: 0.8rem;
      color: #6c757d;
      margin-bottom: 4px;
    }

    .message.mine .message-name {
      text-align: right;
    }

    .message-content {
      display: inline-block;
      max-width: 100%;
      width: auto;
      padding: 10px 14px;
      border-radius: 18px;
      text-align: left;

      word-break: break-word;
      white-space: pre-wrap;

      box-shadow: 0 2px 8px rgba(0,0,0,.08);
    }

    .message-content p,
    .message-content ul,
    .message-content ol,
    .message-content pre,
    .message-content blockquote {
        margin: 0;
        padding: 0;
    }
    .message.other .message-body {
        align-items: flex-start;
    }

  .message.other .message-content {
      text-align: left;
  }

    .message-content p:last-child {
      margin-bottom: 0;
    }

    .message.other .message-content {
      background: #ffffff;
      border-top-left-radius: 4px;
    }

    .message.mine .message-content {
      background: #8DE055;
      color: #000;
      border-top-right-radius: 4px;
    }

    .message-time {
      font-size: 0.72rem;
      color: #6c757d;
      margin-top: 4px;
    }

    .message.mine .message-time {
      text-align: right;
    }

    .message-image {
      max-width: 220px;
      border-radius: 14px;
      margin-top: 8px;
    }

    /* 下の入力欄 */
    .chat-input-area {
      background: #ffffff;
      border-top: 1px solid #d8e6f7;
      padding: 12px 12px 28px 12px;
      flex-shrink: 0;
    }

    .chat-input-row {
      display: flex;
      align-items: flex-end;
      gap: 10px;
    }

    .chat-textarea {
      resize: none;
      border-radius: 22px;
      min-height: 44px;
      max-height: 110px;
      padding: 10px 15px;
    }

    .image-label {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background: #eef6ff;
      color: #0d6efd;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      flex-shrink: 0;
      font-weight: bold;
      font-size: 22px;
      border: 1px solid #d8e6f7;
    }

    .image-label:hover {
      background: #dceeff;
    }

    #imageUpload {
      display: none;
    }

    .send-button {
      width: 72px;
      height: 44px;
      border-radius: 22px;
      flex-shrink: 0;
    }

    .selected-image-name {
      font-size: 0.8rem;
      color: #6c757d;
      margin-top: 6px;
      padding-left: 56px;
    }

    /* MarkdownプレビューはLINE風では邪魔なので非表示 */
    .preview-box {
      display: none;
    }

    @media (max-width: 576px) {

      main {
        height: calc(100vh - 105px);
        padding: 10px !important;
      }

      .line-chat-card {
        border-radius: 16px;
      }

      .message-body {
        max-width: 78%;
      }

      .send-button {
        width: 62px;
      }

    }

    /* 右側にくっつくブログタブ */
    .blog-floating-button {
      position: absolute;
      right: 16px;
      bottom: 95px;

      width: 50px;
      height: 58px;

      border: none;
      border-radius: 18px 0 0 18px;

      background: #aea9e3;
      color: #ffffff;

      font-weight: bold;
      font-size: 14px;

      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
      z-index: 20;

      overflow: hidden;
      white-space: nowrap;

      display: flex;
      align-items: center;
      justify-content: flex-start;
      gap: 8px;

      padding-left: 12px;

      transition: width 0.25s ease, background 0.25s ease;
    }

    .blog-floating-button:hover {
      width: 120px;
      background: #8f89d6;
    }

    .blog-floating-icon {
      font-size: 18px;
      flex-shrink: 0;
    }

    .blog-floating-text {
      opacity: 0;
      transition: opacity 0.2s ease;
    }

    .blog-floating-button:hover .blog-floating-text {
      opacity: 1;
    }

    .blog-floating-button:hover {
      background: #3f7ec4;
    }

    /* ブログ一覧のカード */
    .blog-modal-item {
      border: 1px solid #d8e6f7;
      border-radius: 16px;
      padding: 14px;
      margin-bottom: 12px;
      background: #ffffff;
    }

    .blog-modal-item:hover {
      background: #f4f9ff;
    }
  </style>

</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container-fluid">

    <div id="messageBox"></div>

    <div class="chat-page-wrapper">

      <div class="card border-0 shadow-sm line-chat-card">

        <!-- 上：グループ情報 -->
        <div class="line-chat-header d-flex align-items-center justify-content-between">

          <div class="d-flex align-items-center">

            <img id="groupIcon" src="https://placehold.jp/100x100.png" class="group-icon me-3">

            <div>

              <h5 id="groupName" class="fw-bold mb-0">
                読み込み中...
              </h5>

              <div class="text-muted small">
                グループチャット
              </div>

            </div>

          </div>

          <div class="d-flex gap-2">

            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal"
              data-bs-target="#memberModal">
              メンバー
            </button>

            <a id="editLink" href="#" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
              編集
            </a>

          </div>

        </div>

        <button type="button" class="blog-floating-button" data-bs-toggle="modal" data-bs-target="#blogModal">
          ブログ
        </button>

        <!-- 中央：メッセージ一覧 -->
        <div class="chat-box" id="chatBox">

          <div class="empty-message">
            読み込み中...
          </div>

        </div>

        <!-- 下：入力欄 -->
        <form id="messageForm" class="chat-input-area" enctype="multipart/form-data">

          <div class="chat-input-row">

            <label for="imageUpload" class="image-label">
              ＋
            </label>

            <input type="file" id="imageUpload" name="image_upload" accept="image/*">

            <textarea class="form-control chat-textarea" id="messageInput" name="message_content" rows="1"
              placeholder="メッセージを入力"></textarea>

            <button id="submitButton" class="btn btn-success send-button">
              送信
            </button>

          </div>

          <div id="selectedImageName" class="selected-image-name"></div>

          <div id="preview" class="preview-box">
            ここにMarkdownプレビューが表示されます
          </div>

        </form>

      </div>

    </div>

  </main>

  <div class="modal fade" id="blogModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-scrollable modal-lg">

      <div class="modal-content rounded-4 border-0 shadow">

        <div class="modal-header">

          <h5 class="modal-title fw-bold">
            グループブログ
          </h5>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

        </div>

        <div class="modal-body" id="blogList">

          <div class="text-muted">
            読み込み中...
          </div>

        </div>

      </div>

    </div>

  </div>


  <div class="modal fade" id="memberModal" tabindex="-1">

    <div class="modal-dialog modal-dialog-scrollable">

      <div class="modal-content rounded-4 border-0 shadow">

        <div class="modal-header">

          <h5 class="modal-title fw-bold">
            グループメンバー
          </h5>

          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

        </div>

        <div class="modal-body" id="memberList">

          <div class="text-muted">
            読み込み中...
          </div>

        </div>

      </div>

    </div>

  </div>

  <div class="modal-dialog modal-dialog-scrollable modal-lg">

    <div class="modal-content rounded-4 border-0 shadow">

      <div class="modal-header">

        <h5 class="modal-title fw-bold">
          グループブログ
        </h5>

        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

      </div>

      <div class="modal-body" id="blogList">

        <div class="text-muted">
          読み込み中...
        </div>

      </div>

    </div>

  </div>

  </div>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <input type="hidden" id="csrf_token" value="<?= htmlspecialchars($csrfToken->getToken(), ENT_QUOTES, 'UTF-8') ?>">

  <script>
    // markdown
    const md =
      window.markdownit({
        html: false,
        linkify: true,
        typographer: true
      });

    // URLパラメータ
    const params =
      new URLSearchParams(location.search);

    const groupId =
      params.get("group_id");

    // CSRF
    const csrfToken =
      document.getElementById("csrf_token").value;

    // ログイン中ユーザーID
    const currentUserId =
      "<?= htmlspecialchars((string) $currentUserId, ENT_QUOTES, 'UTF-8') ?>";

    // 初回ロード判定
    let firstLoad =
      true;

    // group_idチェック
    if (!groupId) {

      showMessage(
        "グループIDが存在しません",
        "danger"
      );

      throw new Error("group_id not found");

    }

    // 初期ロード
    loadGroupChat();

    // 3秒ごと更新
    setInterval(() => {

      loadGroupChat(false);

    }, 3000);

    // メッセージ入力時の高さ調整
    document
      .getElementById("messageInput")
      .addEventListener("input", function () {

        this.style.height =
          "auto";

        this.style.height =
          Math.min(this.scrollHeight, 110) + "px";

        document.getElementById("preview").innerHTML =
          md.render(this.value);

      });

    // 画像選択
    document
      .getElementById("imageUpload")
      .addEventListener("change", function (e) {

        const file =
          e.target.files[0];

        const selectedImageName =
          document.getElementById("selectedImageName");

        selectedImageName.textContent =
          "";

        if (!file) {

          return;

        }

        if (!file.type.startsWith("image/")) {

          showMessage(
            "画像ファイルを選択してください",
            "danger"
          );

          e.target.value =
            "";

          return;

        }

        if (file.size > 5 * 1024 * 1024) {

          showMessage(
            "画像サイズは5MB以下にしてください",
            "danger"
          );

          e.target.value =
            "";

          return;

        }

        selectedImageName.textContent =
          "選択中：" + file.name;

      });

    // =========================
    // グループチャット取得
    // =========================

    async function loadGroupChat(scrollBottom = true) {

      const formData =
        new FormData();

      formData.append(
        "group_id",
        groupId
      );

      try {

        const response =
          await fetch(
            "../api/group/get_group_chat.php", {
            method: "POST",
            body: formData
          }
          );

        const text =
          await response.text();

        console.log(text);

        let result;

        try {

          result =
            JSON.parse(text);

        } catch {

          console.error(text);

          showMessage(
            "APIレスポンス形式が不正です",
            "danger"
          );

          return;

        }

        if (!result.success) {

          showMessage(
            result.message,
            "danger"
          );

          return;

        }

        const data =
          result.data;

        renderGroupInfo(data);

        renderMessages(
          data.messages,
          scrollBottom
        );
        renderBlogs(data.group_blogs);
        loadGroupMembers();


      } catch (error) {

        console.error(error);

        showMessage(
          "通信エラーが発生しました",
          "danger"

        );

      }

    }

    function renderMembers(members) {

      const memberList =
        document.getElementById("memberList");

      memberList.innerHTML =
        "";

      if (!members || members.length === 0) {

        memberList.innerHTML = `
      <div class="text-muted">
        メンバーはいません
      </div>
    `;

        return;

      }

      members.forEach(member => {

        memberList.innerHTML += `

      <a href="/profile/profile.php?user_id=${escapeHtml(member.user_id_str || member.user_id || "")}"
         class="d-flex align-items-center text-decoration-none text-dark border rounded-4 p-3 mb-2">

        <img src="${member.icon_url || 'https://placehold.jp/100x100.png'}"
             style="width:40px; height:40px; object-fit:cover; border-radius:50%;"
             class="me-3">

        <div>

          <div class="fw-bold">
            ${escapeHtml(member.display_name || "名前なし")}
          </div>

          <div class="text-muted small">
            ${escapeHtml(member.role || "member")}
          </div>

        </div>

      </a>

    `;

      });

    }

    // =========================
    // グループ情報表示
    // =========================

    function renderGroupInfo(data) {

      document.getElementById("groupName").textContent =
        data.group_name || "名称未設定";

      document.getElementById("groupIcon").src =
        data.group_icon || "https://placehold.jp/100x100.png";

      document.getElementById("editLink").href =
        `edit_group.php?group_id=${groupId}`;

    }

    // =========================
    // メッセージ表示
    // =========================

    function renderMessages(messages, scrollBottom = true) {

      const chatBox =
        document.getElementById("chatBox");

      const isNearBottom =
        chatBox.scrollHeight -
        chatBox.scrollTop -
        chatBox.clientHeight < 150;

      chatBox.innerHTML =
        "";

      if (!messages || messages.length === 0) {

        chatBox.innerHTML = `

          <div class="empty-message">
            メッセージはありません
          </div>

        `;

        return;

      }

      messages.forEach(message => {

        const senderId =
          message.sender_id ??
          message.user_id ??
          "";

        const isMine =
          message.is_mine === true ||
          String(senderId) === String(currentUserId);
          console.log({
          senderId,
          currentUserId,
          isMine,
          message
        });

        chatBox.innerHTML += `

          <div class="message ${isMine ? "mine" : "other"}">

            ${!isMine
            ? `
                  <a href="/profile/profile.php?user_id=${escapeHtml(message.sender_user_id || "")}">
                    <img src="${message.sender_icon || 'https://placehold.jp/100x100.png'}"
                         class="message-icon">
                  </a>
                `
            : ""
          }

            <div class="message-body">

             ${!isMine
            ? `
      <div class="message-name">
        ${escapeHtml(message.sender_display_name || "")}
      </div>
    `
            : ""
          }

              <div class="message-content">${md.renderInline(message.content || "")}</div>

              <div class="message-time">
                ${escapeHtml(message.created_at || "")}
              </div>

            </div>

          </div>

        `;

      });

      if (firstLoad || isNearBottom || scrollBottom) {

        chatBox.scrollTop =
          chatBox.scrollHeight;

      }

      firstLoad =
        false;

    }

    function renderBlogs(blogs) {

      const blogList =
        document.getElementById("blogList");

      blogList.innerHTML =
        "";

      if (!blogs || blogs.length === 0) {

        blogList.innerHTML = `

        <div class="text-muted">
          ブログはありません
        </div>

      `;

        return;

      }

      blogs.forEach(blog => {

        blogList.innerHTML += `

        <a href="/blog/blog_detail.php?blog_id=${blog.blog_id}"
          class="text-decoration-none text-dark">

          <div class="blog-modal-item">

            <div class="fw-bold mb-1">
              ${escapeHtml(blog.title)}
            </div>

            <div class="text-muted small mb-2">
              ${escapeHtml(blog.content || "")}
            </div>

            <div class="small text-muted">
              ${escapeHtml(blog.created_at || "")}
            </div>

          </div>

        </a>

      `;

      });

    }

    async function loadGroupMembers() {

      const formData =
        new FormData();

      formData.append(
        "group_id",
        groupId
      );

      formData.append(
        "csrf_token",
        csrfToken
      );

      try {

        const response =
          await fetch(
            "/api/group/get_group_info.php", {
            method: "POST",
            body: formData
          }
          );

        const result =
          await response.json();

        if (!result.success) {

          document.getElementById("memberList").innerHTML = `
        <div class="text-muted">
          メンバーを取得できませんでした
        </div>
      `;

          return;

        }

        renderMembers(result.data.members || []);

      } catch (error) {

        console.error(error);

        document.getElementById("memberList").innerHTML = `
      <div class="text-muted">
        通信エラーが発生しました
      </div>
    `;

      }

    }

    // =========================
    // メッセージ送信
    // =========================

    document
      .getElementById("messageForm")
      .addEventListener("submit", async (e) => {

        e.preventDefault();

        const submitButton =
          document.getElementById("submitButton");

        const messageInput =
          document.getElementById("messageInput");

        const imageUpload =
          document.getElementById("imageUpload");

        const messageContent =
          messageInput.value.trim();

        const imageFile =
          imageUpload.files[0];

        if (!messageContent && !imageFile) {

          showMessage(
            "メッセージを入力してください",
            "danger"
          );

          return;

        }

        const formData =
          new FormData();

        formData.append(
          "message_content",
          messageContent
        );

        if (imageFile) {

          formData.append(
            "image_upload",
            imageFile
          );

        }

        formData.append(
          "group_id",
          groupId
        );

        formData.append(
          "csrf_token",
          csrfToken
        );

        try {

          submitButton.disabled =
            true;

          submitButton.innerHTML =
            "送信中";

          const response =
            await fetch(
              "../api/group/send_message.php", {
              method: "POST",
              body: formData
            }
            );

          const text =
            await response.text();

          console.log(text);

          let result;

          try {

            result =
              JSON.parse(text);

          } catch {

            console.error(text);

            showMessage(
              "APIレスポンス形式が不正です",
              "danger"
            );

            return;

          }

          if (!result.success) {

            showMessage(
              result.message ||
              "送信に失敗しました",
              "danger"
            );

          }

          e.target.reset();

          messageInput.style.height =
            "auto";

          document.getElementById("selectedImageName").textContent =
            "";

          document.getElementById("preview").innerHTML =
            "ここにMarkdownプレビューが表示されます";

          await loadGroupChat(true);

          messageInput.focus();

        } catch (error) {

          console.error(error);

          showMessage(
            "通信エラーが発生しました",
            "danger"
          );

        } finally {

          submitButton.disabled =
            false;

          submitButton.innerHTML =
            "送信";

        }

      });

    // =========================
    // HTMLエスケープ
    // =========================

    function escapeHtml(str) {

      if (!str) {

        return "";

      }

      return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

    }

    // =========================
    // メッセージ表示
    // =========================

    function showMessage(message, type) {

      document.getElementById("messageBox").innerHTML = `

        <div class="alert alert-${type} shadow-sm">
          ${escapeHtml(message)}
        </div>

      `;

      setTimeout(() => {

        document.getElementById("messageBox").innerHTML =
          "";

      }, 3000);

    }
  </script>

</body>

</html>