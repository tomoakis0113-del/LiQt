<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>グループチャット</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- markdown -->
  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>

  <style>
    .chat-box {
      height: 400px;
      overflow-y: auto;
      border: 1px solid #ddd;
      padding: 10px;
      background: #f8f9fa;
    }

    .message {
      margin-bottom: 15px;
    }

    .message img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
    }

    .preview-box {
      background: #fff;
      border: 1px dashed #ccc;
      padding: 10px;
      margin-top: 10px;
    }
  </style>
</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container p-4">

    <!-- グループ情報 -->
    <div class="d-flex align-items-center mb-4">
      <img id="groupIcon" src="" width="60" height="60" class="rounded me-3">
      <div>
        <h4 id="groupName"></h4>
        <a id="editLink" class="btn btn-sm btn-outline-secondary">編集</a>
      </div>
    </div>

    <!-- ブログ一覧 -->
    <div class="mb-4">
      <h5>グループブログ</h5>
      <ul id="blogList" class="list-group"></ul>
    </div>

    <!-- チャット -->
    <div class="chat-box mb-3" id="chatBox"></div>

    <!-- 投稿フォーム -->
    <form id="messageForm">

      <textarea class="form-control mb-2" name="message_content" id="messageInput" placeholder="メッセージ（Markdown対応）"></textarea>

      <input type="file" class="form-control mb-2" name="image_upload">

      <!-- プレビュー -->
      <div class="preview-box" id="preview"></div>

      <button class="btn btn-primary w-100">送信</button>
    </form>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    const md = window.markdownit();

    // URLからgroup_id取得
    const params = new URLSearchParams(location.search);
    const groupId = params.get("group_id");

    // 初期ロード
    fetch(`api/get_group_chat.php?group_id=${groupId}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) {
          alert(data.message);
          return;
        }

        const d = data.data;

        // グループ情報
        document.getElementById("groupName").textContent = d.group_name;
        document.getElementById("groupIcon").src = d.group_icon;
        document.getElementById("editLink").href = `edit_group.php?group_id=${groupId}`;

        // ブログ
        const blogList = document.getElementById("blogList");
        d.group_blogs.forEach(blog => {
          blogList.innerHTML += `
        <li class="list-group-item">
          <a href="blog_detail.php?blog_id=${blog.blog_id}">${blog.title}</a>
          <div class="text-muted small">${blog.tags}</div>
        </li>
      `;
        });

        // メッセージ
        renderMessages(d.messages);
      });

    // メッセージ描画
    function renderMessages(messages) {
      const box = document.getElementById("chatBox");
      box.innerHTML = "";

      messages.forEach(msg => {
        box.innerHTML += `
      <div class="message d-flex">
        <img src="${msg.sender_icon}" class="me-2">
        <div>
          <a href="profile.php?user_id=${msg.sender_user_id}">
            ${msg.sender_display_name}
          </a>
          <div>${md.render(msg.content)}</div>
        </div>
      </div>
    `;
      });

      box.scrollTop = box.scrollHeight;
    }

    // プレビュー
    document.getElementById("messageInput").addEventListener("input", function() {
      document.getElementById("preview").innerHTML = md.render(this.value);
    });

    // 送信
    document.getElementById("messageForm").addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(e.target);
      formData.append("group_id", groupId);

      const res = await fetch("api/send_message.php", {
        method: "POST",
        body: formData
      });

      const data = await res.json();

      if (data.success) {
        // 再取得（簡易モック）
        location.reload();
      } else {
        alert(data.message);
      }
    });
  </script>

</body>

</html>