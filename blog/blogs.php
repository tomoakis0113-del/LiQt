<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>ブログ一覧</title>

<link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
<script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

<style>
.tag-badge {
  margin-right: 5px;
}

.blog-card:hover {
  background: #f8f9fa;
}
</style>
</head>

<body>

<?php require_once __DIR__ . '/../component/header.php'; ?>

<main class="container p-4" style="max-width:900px;">

<h3 class="mb-4">ブログ一覧</h3>

<!-- エラー -->
<div id="alertBox" class="alert d-none"></div>

<!-- 検索 -->
<form id="searchForm" class="row g-2 mb-4">
  <div class="col-md-6">
    <input type="text" class="form-control" name="tag_search" placeholder="タグ検索（例: Java,PHP）">
  </div>
  <div class="col-md-4">
    <input type="text" class="form-control" name="group_filter" placeholder="グループID">
  </div>
  <div class="col-md-2">
    <button class="btn btn-primary w-100">検索</button>
  </div>
</form>

<!-- タブ -->
<ul class="nav nav-tabs mb-3" id="blogTabs">
  <li class="nav-item">
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#public">公開</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#private">非公開</button>
  </li>
  <li class="nav-item">
    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#my">自分の投稿</button>
  </li>
</ul>

<div class="tab-content">

  <!-- 公開 -->
  <div class="tab-pane fade show active" id="public">
    <div id="publicList" class="list-group"></div>
  </div>

  <!-- 非公開 -->
  <div class="tab-pane fade" id="private">
    <div id="privateList" class="list-group"></div>
  </div>

  <!-- 自分 -->
  <div class="tab-pane fade" id="my">
    <div id="myList" class="list-group"></div>
  </div>

</div>

</main>

<?php require_once __DIR__ . '/../component/footer.php'; ?>

<script>

// 初期ロード
loadBlogs();

// 取得
function loadBlogs() {
  fetch("api/get_blogs.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) return showError(data.message);

      renderBlogs("publicList", data.public_blogs);
      renderBlogs("privateList", data.private_blogs);
      renderMyBlogs(data.my_blogs);
    });
}

// 共通描画
function renderBlogs(targetId, blogs) {
  const list = document.getElementById(targetId);
  list.innerHTML = "";

  if (!blogs || blogs.length === 0) {
    list.innerHTML = `<div class="text-muted">記事なし</div>`;
    return;
  }

  blogs.forEach(b => {
    list.innerHTML += `
      <a href="blog_detail.php?blog_id=${b.blog_id}"
         class="list-group-item blog-card">
        <div class="fw-bold">${b.title}</div>
        <div>${renderTags(b.tags)}</div>
      </a>
    `;
  });
}

// 自分の投稿
function renderMyBlogs(blogs) {
  const list = document.getElementById("myList");
  list.innerHTML = "";

  blogs.forEach(b => {
    list.innerHTML += `
      <a href="blog_detail.php?blog_id=${b.blog_id}"
         class="list-group-item blog-card">

        <div class="fw-bold">${b.title}</div>

        <div>${renderTags(b.tags)}</div>

        <span class="badge bg-secondary mt-1">
          ${b.visibility}
        </span>
      </a>
    `;
  });
}

// タグ表示
function renderTags(tags) {
  if (!tags) return "";

  return tags.split(",").map(tag =>
    `<span class="badge bg-primary tag-badge">${tag.trim()}</span>`
  ).join("");
}

// 検索
document.getElementById("searchForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);

  const res = await fetch("api/search_blogs.php", {
    method: "POST",
    body: formData
  });

  const data = await res.json();

  if (data.success) {
    // 全タブに検索結果表示
    renderBlogs("publicList", data.blogs);
    document.querySelector('[data-bs-target="#public"]').click();
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