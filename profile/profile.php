<?php

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

// CSRF生成
$csrfToken = new lib\CSRFToken();

?>

<!DOCTYPE html>
<html lang="ja">

<head>

  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <title>

    プロフィール | LiQt

  </title>

  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

  <style>

    body {

      background-color: #f5f7fb;

    }

    .avatar-image {

      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;

    }

    .tag-badge {

      margin-right: 6px;
      margin-bottom: 6px;

    }

    .blog-card {

      transition: 0.2s;

    }

    .blog-card:hover {

      transform: translateY(-2px);

    }

  </style>

</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container py-4"
        style="max-width: 900px; padding-bottom: 180px !important;">

    <div id="messageBox"></div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">

      <div class="card-body text-center p-4">

        <div class="mb-3">

          <img id="profileIcon"
               src="https://placehold.jp/120x120.png"
               class="avatar-image">

        </div>

        <h2 id="displayName"
            class="fw-bold mb-1">

          読み込み中...

        </h2>

        <p id="userId"
           class="text-muted mb-3">

          @loading

        </p>

        <p id="introduction"
           class="mb-4">

          読み込み中...

        </p>

        <div id="tagArea"
             class="mb-4"></div>

        <div id="buttonArea"
             class="d-flex justify-content-center gap-2 flex-wrap"></div>

      </div>

    </div>

    <div class="card border-0 shadow-sm rounded-4">

    <div class="card-header bg-white border-0 pt-4 pb-0">

      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

        <h4 class="fw-bold mb-0">

          投稿一覧

        </h4>

        <div class="d-flex gap-2">

          <input type="text"
                id="blogSearch"
                class="form-control"
                placeholder="ブログID・タイトル・タグ検索"
                style="width: 240px;">

          <button type="button"
                  class="btn btn-success"
                  onclick="searchBlogs()">

            検索

          </button>

        </div>
      </div>

    </div>

      <div class="card-body"
           id="blogList">

        <div class="text-muted">

          読み込み中...

        </div>

      </div>

    </div>

  </main>

  <div class="modal fade"
       id="editModal"
       tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

      <div class="modal-content border-0 rounded-4 shadow">

        <form id="editProfileForm">

          <div class="modal-header">

            <h5 class="modal-title fw-bold">

              プロフィール編集

            </h5>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"></button>

          </div>

          <div class="modal-body">

            <div class="mb-3">

              <label class="form-label fw-bold">

                表示名

              </label>

              <input type="text"
                     name="display_name"
                     id="editDisplayName"
                     class="form-control"
                     required>

            </div>

            <div class="mb-3">

              <label class="form-label fw-bold">

                自己紹介

              </label>

              <textarea name="introduction"
                        id="editIntroduction"
                        class="form-control"
                        rows="4"></textarea>

            </div>

            <div class="mb-3">

              <label class="form-label fw-bold">

                タグ

              </label>

              <input type="text"
                     name="tags"
                     id="editTags"
                     class="form-control"
                     placeholder="Java,PHP,Bootstrap">

            </div>

            <div class="mb-3">

              <label class="form-label fw-bold">

                アイコン

              </label>

              <input type="file"
                     name="icon"
                     class="form-control"
                     accept="image/*">

            </div>

          </div>

          <div class="modal-footer">

            <button type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">

              キャンセル

            </button>

            <button type="submit"
                    class="btn btn-success">

              保存

            </button>

          </div>

        </form>

      </div>

    </div>

  </div>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <input type="hidden"
         id="csrf_token"
         value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <script>

    // URLパラメータ
    const params =
      new URLSearchParams(location.search);

    // user_id
    const userId =
      params.get("user_id")??"";

    // CSRF
    const csrfToken =
      document.getElementById("csrf_token").value;

    // 最初に読み込んだ投稿一覧を保存する
    let originalBlogs = [];

    // 初期ロード
    loadProfile();

    // =========================
    // プロフィール取得
    // =========================

    async function loadProfile() {

      const formData =
        new FormData();

      formData.append(
        "user_id",
        userId
      );

      formData.append(
        "csrf_token",
        csrfToken
      );

      try {

        const response =
          await fetch(
            "../api/profile/get_profile.php",
            {
              method: "POST",
              body: formData
            }
          );

        const text =
          await response.text();

        console.log(text);

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

        // 表示
        renderProfile(data);

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
  // ブログ検索
  // =========================

  async function searchBlogs() {

    const search =
      document.getElementById("blogSearch").value.trim();

    // 空白ならAPIを呼ばずに、検索前の投稿一覧へ戻す
    if (search === "") {

      renderBlogs(originalBlogs);

      document.getElementById("messageBox").innerHTML = "";

      return;

    }

    const formData =
      new FormData();

    formData.append(
      "csrf_token",
      csrfToken
    );

    formData.append(
      "search",
      search
    );

    formData.append(
      "group_filter",
      ""
    );

    try {

      const response =
        await fetch(
          "/api/blog/search_blogs.php",
          {
            method: "POST",
            body: formData
          }
        );

      const text =
        await response.text();

      console.log(text);

      const result =
        JSON.parse(text);

      if (!result.success) {

        showMessage(
          result.message,
          "danger"
        );

        return;

      }

      // search_blogs.php の形式を変えないため、両方に対応する
      const blogs =
        result.data?.blogs ?? result.message?.blogs ?? [];

      renderSearchBlogs(blogs);

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
  // 検索結果表示
  // =========================

  function renderSearchBlogs(blogs) {

    const list =
      document.getElementById("blogList");

    list.innerHTML = "";

    if (!blogs || blogs.length === 0) {

      list.innerHTML = `

        <div class="text-muted">

          検索結果はありません

        </div>

      `;

      return;

    }

    blogs.forEach(blog => {

      list.innerHTML += `

        <a href="/blog/blog_detail.php?blog_id=${blog.blog_id}"
          class="text-decoration-none text-dark">

          <div class="border rounded-4 p-3 mb-3 blog-card">

            <div class="fw-bold">

              ${blog.title}

            </div>

            <div class="text-muted small mb-2">

              ブログID : ${blog.blog_id}

            </div>

            <div>

              ${renderBlogSearchTags(blog.tags)}

            </div>

          </div>

        </a>

      `;

    });

  }


  // =========================
// 検索結果用タグ表示
// =========================

function renderBlogSearchTags(tags) {

  if (!tags) {

    return "";

  }

  return tags.split(",").map(tag => `

    <span class="badge bg-success tag-badge">

      ${tag.trim()}

    </span>

  `).join("");

}

    // =========================
    // 表示
    // =========================

    function renderProfile(data) {

      // アイコン
      document.getElementById("profileIcon").src =
        data.icon_url || "https://placehold.jp/120x120.png";

      // 表示名
      document.getElementById("displayName").textContent =
        data.display_name;

      // ユーザーID
      document.getElementById("userId").textContent =
        "@" + data.user_id;

      // 自己紹介
      document.getElementById("introduction").textContent =
        data.introduction || "自己紹介はありません";

      // タグ
      renderTags(data.tags);

      // ボタン
      renderButtons(data);

      // 最初の投稿一覧を保存
      originalBlogs = data.blogs || [];

      // 投稿
      renderBlogs(originalBlogs);
      // 編集フォーム
      if (data.is_mine) {

        document.getElementById("editDisplayName").value =
          data.display_name;

        document.getElementById("editIntroduction").value =
          data.introduction;

        document.getElementById("editTags").value =
          data.tags;

      }

    }

    // =========================
    // タグ
    // =========================

    function renderTags(tags) {

      const area =
        document.getElementById("tagArea");

      area.innerHTML = "";

      if (!tags || tags.trim() === "") {

        return;

      }

      tags.split(",").forEach(tag => {

        area.innerHTML += `

          <span class="badge bg-success tag-badge">

            ${tag.trim()}

          </span>

        `;

      });

    }

    // =========================
    // ボタン
    // =========================

    function renderButtons(data) {

      const area =
        document.getElementById("buttonArea");

      area.innerHTML = "";

      // 自分
      if (data.is_mine) {

        area.innerHTML = `

          <div class="d-grid gap-2" style="width: 220px;">

            <button class="btn btn-success px-4"
                    data-bs-toggle="modal"
                    data-bs-target="#editModal">

              編集

            </button>

            <a href="/auth/signout.php"
              class="btn btn-outline-success px-4">

              サインアウト

            </a>

          </div>

        `;

      }

      // 他人
      else {

        area.innerHTML = `

          <button class="btn btn-outline-primary"
                  onclick="startChat('${data.user_id}')">

            チャット

          </button>

          <button class="btn btn-outline-danger"
                  onclick="blockUser('${data.user_id}', ${data.is_blocked})">

            ${data.is_blocked ? "ブロック解除" : "ブロック"}

          </button>
        `;
      }

    }

    // =========================
    // 投稿
    // =========================

    function renderBlogs(blogs) {

      const list =
        document.getElementById("blogList");

      list.innerHTML = "";

      // 投稿なし
      if (!blogs || blogs.length === 0) {

        list.innerHTML = `

          <div class="text-muted">

            投稿はありません

          </div>

        `;

        return;

      }

      blogs.forEach(([blogId, blogTitle]) => {

        list.innerHTML += `

          <a href="/blog/blog_detail.php?blog_id=${blogId}"
             class="text-decoration-none text-dark">

            <div class="border rounded-4 p-3 mb-3 blog-card">

              <div class="fw-bold">

                ${blogTitle}

              </div>

              <div class="text-muted small">

                詳細を見る

              </div>

            </div>

          </a>

        `;

      });

    }

    // =========================
    // チャット開始
    // =========================

    async function startChat(targetUserId) {

      const formData =
        new FormData();

      formData.append(
        "csrf_token",
        csrfToken
      );

      formData.append(
        "user_id",
        targetUserId
      );

      try {

        const response =
          await fetch(
            "/api/profile/start_chat.php",
            {
              method: "POST",
              body: formData
            }
          );

        const text =
          await response.text();

        console.log(text);

        const result =
          JSON.parse(text);

        if (!result.success) {

          showMessage(
            result.message,
            "danger"
          );

          return;

        }

        showMessage(
          result.message,
          "success"
        );

        // 遷移
        location.href =
          result.data.chat_link;

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
    // ブロック
    // =========================

    async function blockUser(targetUserId, isCurrentlyBlocked) {

      if (!confirm(`このユーザーを${isCurrentlyBlocked ? "ブロック解除" : "ブロック"}しますか？`)) {

        return;

      }

      const formData =
        new FormData();

      formData.append(
        "csrf_token",
        csrfToken
      );

      formData.append(
        "user_id",
        targetUserId
      );

      try {

        const response =
          await fetch(
            "/api/profile/block_user.php",
            {
              method: "POST",
              body: formData
            }
          );

        const text =
          await response.text();

        console.log(text);

        const result =
          JSON.parse(text);

        if (!result.success) {

          showMessage(
            result.message,
            "danger"
          );

          return;

        }

        showMessage(
          result.message,
          "success"
        );

        // 再取得
        loadProfile();

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
    // 更新
    // =========================

    document
      .getElementById("editProfileForm")
      .addEventListener("submit", async (e) => {

        e.preventDefault();

        const formData =
          new FormData(e.target);

        formData.append(
          "csrf_token",
          csrfToken
        );

        try {

          const response =
            await fetch(
              "/api/profile/update_profile.php",
              {
                method: "POST",
                body: formData
              }
            );

          const text =
            await response.text();

          console.log(text);

          const result =
            JSON.parse(text);

          if (!result.success) {

            showMessage(
              result.message,
              "danger"
            );

            return;

          }

          showMessage(
            result.message,
            "success"
          );

          // モーダル閉じる
          bootstrap.Modal
            .getInstance(
              document.getElementById("editModal")
            )
            .hide();

          // 再取得
          loadProfile();

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
    // メッセージ
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