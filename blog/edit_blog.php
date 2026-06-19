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

  <title>ブログ編集</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

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

  <main class="container p-4"
    style="max-width:900px; margin-bottom:120px;">

    <h3 class="mb-4">ブログ編集</h3>

    <div id="alertBox" class="alert d-none"></div>

    <form id="editForm">
      <input type="hidden" name="csrf_token" id="csrfTokenInput" value="<?php echo htmlspecialchars($csrfToken->getToken(), ENT_QUOTES, 'UTF-8'); ?>">

      <div class="mb-3">
        <label class="form-label">タイトル</label>
        <input
          type="text"
          name="title"
          id="titleInput"
          class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">タグ</label>
        <input
          type="text"
          name="tags"
          id="tagsInput"
          class="form-control">
      </div>

      <div class="mb-3">
        <label class="form-label">本文（Markdown）</label>
        <textarea
          name="content"
          id="contentInput"
          class="form-control"
          rows="8"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">プレビュー</label>
        <div id="preview" class="preview-box"></div>
      </div>

      <div class="mb-3">
        <label class="form-label">公開設定</label>
        <select
          name="visibility"
          id="visibility"
          class="form-select">
          <option value="public">公開</option>
          <option value="private">非公開</option>
          <option value="group">グループ</option>
        </select>
      </div>

      <!-- グループ公開時だけ表示 -->
      <div class="mb-3 d-none"
        id="groupSelectArea">

        <label class="form-label">
          公開するグループ
        </label>

        <select
          name="group_id"
          id="groupSelect"
          class="form-select">

          <option value="">
            グループを選択してください
          </option>

        </select>

        <div id="groupSelectMessage"
          class="form-text"></div>

      </div>

      <button class="btn btn-primary w-100 mb-2">
        更新する
      </button>

      <button
        type="button"
        id="deleteBtn"
        class="btn btn-danger w-100">
        削除する
      </button>

    </form>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    const md = window.markdownit();

    // クエリパラメータから blog_id を取得
    const params = new URLSearchParams(location.search);
    const blogId = params.get("blog_id");

    // HTML内のhiddenフィールドからCSRFトークンを取得
    const csrfToken = document.getElementById("csrfTokenInput").value;

    const visibilitySelect =
      document.getElementById("visibility");

    const groupSelectArea =
      document.getElementById("groupSelectArea");

    const groupSelect =
      document.getElementById("groupSelect");

    const groupSelectMessage =
      document.getElementById("groupSelectMessage");

    // --- 1. 初期データ取得 (POSTリクエスト) ---
    fetch("/api/blog/get_blog_detail.php", {
        method: "POST", // POSTメソッド
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
          blog_id: blogId,
          csrf_token: csrfToken
        })
      })
      .then(res => res.json())
      .then(async data => {
        if (!data.success) {
          return redirect();
        }

        const blogData = data.data ? data.data : data;

        // フォームへ値を反映
        document.getElementById("titleInput").value = blogData.title || "";
        document.getElementById("tagsInput").value = blogData.tags || "";
        document.getElementById("contentInput").value = blogData.content || "";
        visibilitySelect.value =
          blogData.visibility || "public";

        if (visibilitySelect.value === "group") {

          groupSelectArea.classList.remove("d-none");
          groupSelect.setAttribute("required", "required");

          await loadJoinedGroups(
            blogData.group_id || ""
          );

        } else {

          groupSelectArea.classList.add("d-none");
          groupSelect.removeAttribute("required");

        }

        // 初期プレビューのレンダリング
        updatePreview();
      })
      .catch(err => {
        showError("データの読み込みに失敗しました。");
      });

    async function loadJoinedGroups(selectedGroupId = "") {

      groupSelect.disabled = true;

      groupSelect.innerHTML = `
    <option value="">
      読み込み中...
    </option>
  `;

      groupSelectMessage.textContent = "";

      const formData = new FormData();

      formData.append(
        "csrf_token",
        csrfToken
      );

      try {

        const response = await fetch(
          "/api/group/search_groups.php", {
            method: "POST",
            body: formData
          }
        );

        const text = await response.text();

        console.log(text);

        const result = JSON.parse(text);

        if (!result.success) {

          groupSelect.innerHTML = `
        <option value="">
          グループを取得できませんでした
        </option>
      `;

          groupSelectMessage.textContent =
            result.message;

          return;

        }

        const groups =
          result.data || [];

        groupSelect.innerHTML = `
      <option value="">
        グループを選択してください
      </option>
    `;

        if (groups.length === 0) {

          groupSelectMessage.textContent =
            "参加しているグループがありません";

          return;

        }

        groups.forEach(group => {

          const option =
            document.createElement("option");

          option.value =
            group.group_id;

          option.textContent =
            group.group_name;

          if (
            String(group.group_id) ===
            String(selectedGroupId)
          ) {

            option.selected = true;

          }

          groupSelect.appendChild(option);

        });

        groupSelect.disabled = false;

      } catch (error) {

        console.error(error);

        groupSelect.innerHTML = `
      <option value="">
        グループを取得できませんでした
      </option>
    `;

        groupSelectMessage.textContent =
          "グループ一覧の取得に失敗しました";

      }

    }

    visibilitySelect.addEventListener("change", async function() {

      if (this.value === "group") {

        groupSelectArea.classList.remove("d-none");
        groupSelect.setAttribute("required", "required");

        await loadJoinedGroups();

      } else {

        groupSelectArea.classList.add("d-none");
        groupSelect.removeAttribute("required");
        groupSelect.value = "";

      }

    });

    // --- 2. プレビューのリアルタイム反映 ---
    document
      .getElementById("contentInput")
      .addEventListener("input", updatePreview);

    function updatePreview() {
      const text = document.getElementById("contentInput").value;
      document.getElementById("preview").innerHTML = md.render(text);
    }

    // --- 3. ブログ更新処理 (POSTリクエスト) ---
    document
      .getElementById("editForm")
      .addEventListener("submit", async (e) => {
        e.preventDefault();

        // フォーム内の最新の値を格納
        const visibility =
          visibilitySelect.value;

        const selectedGroupId =
          groupSelect.value;

        if (
          visibility === "group" &&
          selectedGroupId === ""
        ) {

          showError(
            "公開するグループを選択してください。"
          );

          return;

        }

        const updateParams = new URLSearchParams({
          csrf_token: csrfToken,
          blog_id: blogId,
          title: document.getElementById("titleInput").value,
          content: document.getElementById("contentInput").value,
          visibility: visibility,
          group_id: visibility === "group" ?
            selectedGroupId :
            "",
          tags: document.getElementById("tagsInput").value
        });

        try {
          const res = await fetch("/api/blog/update_blog.php", {
            method: "POST", // 明示的にPOSTメソッドを指定
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: updateParams
          });

          const data = await res.json();

          if (data.success) {
            // 更新が成功したら詳細画面に遷移
            location.href = `blog_detail.php?blog_id=${blogId}`;
          } else {
            showError(data.message || "更新に失敗しました。");
          }
        } catch (err) {
          showError("通信エラーが発生しました。");
        }
      });

    // --- 4. ブログ削除処理 (POSTリクエスト) ---
    document
      .getElementById("deleteBtn")
      .addEventListener("click", async () => {
        if (!confirm("本当に削除する？")) {
          return;
        }

        try {
          const res = await fetch("/api/blog/delete_blog.php", {
            method: "POST", // POSTメソッド
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
              blog_id: blogId,
              csrf_token: csrfToken
            })
          });

          const data = await res.json();

          if (data.success) {
            location.href = "blogs.php";
          } else {
            showError(data.message || "削除に失敗しました。");
          }
        } catch (err) {
          showError("通信エラーが発生しました。");
        }
      });

    // --- 5. 汎用リダイレクト処理 ---
    function redirect() {
      location.href = `blog_detail.php?blog_id=${blogId}`;
    }

    // --- 6. エラーメッセージ表示処理 ---
    function showError(msg) {
      const box = document.getElementById("alertBox");
      box.textContent = msg;
      box.className = "alert alert-danger";
    }
  </script>

</body>

</html>