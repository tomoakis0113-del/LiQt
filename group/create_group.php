<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>グループ作成</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../custom/custom-theme.css">
</head>

<body>
<?php require_once __DIR__ . '/../component/header.php'; ?>

<main class="container p-4" style="max-width: 700px;">

  <h2 class="mb-4 fw-bold">グループ作成</h2>

  <!-- アラート -->
  <div id="alertBox" class="alert d-none"></div>

  <!-- フォーム -->
  <form id="createGroupForm">

    <!-- グループ名 -->
    <div class="mb-3">
      <label class="form-label">グループ名</label>
      <input type="text" class="form-control" name="group_name" required>
    </div>

    <!-- アイコン -->
    <div class="mb-3">
      <label class="form-label">アイコン</label>
      <input type="file" class="form-control" name="group_icon" accept="image/*">
    </div>

    <!-- 公開設定 -->
    <div class="mb-3">
      <label class="form-label">公開設定</label>
      <select class="form-select" name="is_public">
        <option value="1">公開</option>
        <option value="0">非公開</option>
      </select>
    </div>

    <!-- ユーザ招待 -->
    <div class="mb-3">
      <label class="form-label">ユーザ招待（ID）</label>

      <div id="inviteList"></div>

      <div class="input-group mt-2">
        <input type="text" id="inviteInput" class="form-control" placeholder="ユーザID入力">
        <button type="button" class="btn btn-outline-primary" onclick="addInvite()">追加</button>
      </div>

      <small class="text-muted">※複数追加できます</small>
    </div>

    <!-- 作成ボタン -->
    <button type="submit" class="btn btn-primary w-100">
      作成する
    </button>

  </form>
</main>

<?php require_once __DIR__ . '/../component/footer.php'; ?>

<script>
let inviteUsers = [];

// 招待追加
function addInvite() {
  const input = document.getElementById("inviteInput");
  const userId = input.value.trim();

  if (!userId) return;

  inviteUsers.push(userId);
  input.value = "";

  renderInviteList();
}

// 表示更新
function renderInviteList() {
  const list = document.getElementById("inviteList");
  list.innerHTML = "";

  inviteUsers.forEach((id, index) => {
    list.innerHTML += `
      <span class="badge bg-secondary me-2 mb-2">
        ${id}
        <button type="button" class="btn-close btn-close-white ms-2"
          onclick="removeInvite(${index})"></button>
      </span>
    `;
  });
}

// 削除
function removeInvite(index) {
  inviteUsers.splice(index, 1);
  renderInviteList();
}

// 送信
document.getElementById("createGroupForm").addEventListener("submit", async function(e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  inviteUsers.forEach(id => {
    formData.append("invite_user_ids[]", id);
  });

  try {
    // モックAPI（本番はここ変える）
    const res = await fetch("api/create_group.php", {
      method: "POST",
      body: formData
    });

    const data = await res.json();

    if (data.success) {
      location.href = "group_chat.php"; // 成功時
    } else {
      showAlert(data.message, "danger");
    }

  } catch (err) {
    showAlert("通信エラーが発生しました", "danger");
  }
});

// アラート表示
function showAlert(message, type) {
  const box = document.getElementById("alertBox");
  box.className = `alert alert-${type}`;
  box.textContent = message;
  box.classList.remove("d-none");
}
</script>

</body>
</html>