<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>オープンチャット一覧</title>

<link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
<script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

<style>
.group-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
}

.group-card {
  cursor: pointer;
  transition: 0.2s;
}

.group-card:hover {
  background-color: #f8f9fa;
}
</style>
</head>

<body>

<?php require_once __DIR__ . '/../component/header.php'; ?>

<main class="container p-4" style="max-width:800px;">

<h3 class="mb-4">オープンチャット</h3>

<!-- アラート -->
<div id="alertBox" class="alert d-none"></div>

<!-- 検索 -->
<form id="searchForm" class="mb-4">
  <div class="input-group">
    <input type="text" class="form-control" name="group_name_search" placeholder="グループ名で検索">
    <button class="btn btn-primary">検索</button>
  </div>
</form>

<!-- 一覧 -->
<div id="groupList" class="list-group"></div>

</main>

<?php require_once __DIR__ . '/../component/footer.php'; ?>

<script>

// 初期ロード
loadGroups();

// 一覧取得
function loadGroups() {
  fetch("api/get_public_groups.php")
    .then(res => res.json())
    .then(data => {
      if (!data.success) return showError(data.message);
      renderGroups(data.groups);
    });
}

// 描画
function renderGroups(groups) {
  const list = document.getElementById("groupList");
  list.innerHTML = "";

  if (groups.length === 0) {
    list.innerHTML = `<div class="text-muted">グループが見つかりません</div>`;
    return;
  }

  groups.forEach(g => {
    list.innerHTML += `
      <div class="list-group-item group-card d-flex align-items-center"
           onclick="joinGroup('${g.group_id}')">

        <img src="${g.group_icon}" class="group-icon me-3">

        <div>
          <div class="fw-bold">${g.group_name}</div>
          <div class="text-muted small">${g.latest_message || "メッセージなし"}</div>
        </div>

      </div>
    `;
  });
}

// 参加処理
async function joinGroup(groupId) {
  if (!confirm("このグループに参加しますか？")) return;

  try {
    const res = await fetch("api/join_group.php", {
      method: "POST",
      body: new URLSearchParams({ group_id: groupId })
    });

    const data = await res.json();

    if (data.success) {
      location.href = `chat.php?group_id=${groupId}`;
    } else {
      showError(data.message);
    }

  } catch (e) {
    showError("通信エラー");
  }
}

// 検索
document.getElementById("searchForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);

  const res = await fetch("api/search_groups.php", {
    method: "POST",
    body: formData
  });

  const data = await res.json();

  if (data.success) {
    renderGroups(data.groups);
  } else {
    showError(data.message);
  }
});

// エラー表示
function showError(msg) {
  const box = document.getElementById("alertBox");
  box.textContent = msg;
  box.className = "alert alert-danger";
}

</script>

</body>
</html>