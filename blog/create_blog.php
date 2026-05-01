<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ブログ作成</title>

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

<h3 class="mb-4">ブログ作成</h3>

<!-- エラー -->
<div id="alertBox" class="alert d-none"></div>

<form id="blogForm">

  <!-- タイトル -->
  <div class="mb-3">
    <label class="form-label">タイトル</label>
    <input type="text" name="title" class="form-control" required>
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

  <!-- グループ選択 -->
  <div class="mb-3 d-none" id="groupSelectBox">
    <label class="form-label">グループ選択</label>
    <select name="group_id" id="groupSelect" class="form-select"></select>
  </div>

  <!-- タグ -->
  <div class="mb-3">
    <label class="form-label">タグ（カンマ区切り）</label>
    <input type="text" name="tags" class="form-control" placeholder="Java,PHP,HTML">
  </div>

  <!-- 投稿 -->
  <button class="btn btn-primary w-100">投稿する</button>

</form>

</main>

<?php require_once __DIR__ . '/../component/footer.php'; ?>

<script>
const md = window.markdownit();

// Markdownプレビュー
document.getElementById("contentInput").addEventListener("input", function() {
  document.getElementById("preview").innerHTML = md.render(this.value);
});

// 公開設定切替
document.getElementById("visibility").addEventListener("change", function() {
  const box = document.getElementById("groupSelectBox");

  if (this.value === "group") {
    box.classList.remove("d-none");
    loadGroups();
  } else {
    box.classList.add("d-none");
  }
});

// グループ取得（所属グループ）
function loadGroups() {
  fetch("api/get_my_groups.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) return showError(data.message);

      const select = document.getElementById("groupSelect");
      select.innerHTML = "";

      data.groups.forEach(g => {
        select.innerHTML += `
          <option value="${g.group_id}">
            ${g.group_name}
          </option>
        `;
      });
    });
}

// 投稿
document.getElementById("blogForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);

  const res = await fetch("api/create_blog.php", {
    method: "POST",
    body: formData
  });

  const data = await res.json();

  if (data.success) {
    location.href = `blog_detail.php?blog_id=${data.blog_id}`;
  } else {
    showError(data.message);
  }
});

// エラー
function showError(msg) {
  const box = document.getElementById("alertBox");
  box.textContent = msg;
  box.className = "alert alert-danger";
}
</script>

</body>
</html>