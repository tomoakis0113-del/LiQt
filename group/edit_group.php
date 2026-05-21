<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>グループ編集</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <style>
    .member-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
    }
  </style>
</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container p-4" style="max-width:800px;">

    <h3 class="mb-4">グループ編集</h3>

    <div id="alertBox" class="alert d-none"></div>

    <!-- グループ情報 -->
    <div class="mb-4 d-flex align-items-center">
      <img id="groupIcon" width="60" height="60" class="rounded me-3">
      <div>
        <h5 id="groupName"></h5>
        <div id="groupPublic"></div>
      </div>
    </div>

    <!-- 編集フォーム（オーナー・管理者のみ） -->
    <form id="editForm" class="d-none">

      <div class="mb-3">
        <label>グループ名</label>
        <input type="text" class="form-control" name="group_name" id="inputName">
      </div>

      <div class="mb-3">
        <label>アイコン</label>
        <input type="file" class="form-control" name="group_icon">
      </div>

      <div class="mb-3">
        <label>公開設定</label>
        <select class="form-select" name="is_public" id="inputPublic">
          <option value="1">公開</option>
          <option value="0">非公開</option>
        </select>
      </div>

      <!-- メンバー追加 -->
      <div class="mb-3">
        <label>メンバー追加</label>
        <div class="input-group">
          <input type="text" id="addUserInput" class="form-control" placeholder="ユーザID">
          <button type="button" class="btn btn-outline-primary" onclick="addUser()">追加</button>
        </div>
      </div>

      <button class="btn btn-primary w-100 mb-2">更新</button>

      <!-- 削除（オーナーのみ） -->
      <button type="button" id="deleteBtn" class="btn btn-danger w-100 d-none">
        グループ削除
      </button>

    </form>

    <!-- メンバー一覧 -->
    <h5>メンバー一覧</h5>
    <ul id="memberList" class="list-group"></ul>

    <!-- メンバー用 -->
    <div id="memberOnly" class="d-none mt-4">
      <button class="btn btn-warning w-100" onclick="leaveGroup()">退会する</button>
    </div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    const params = new URLSearchParams(location.search);
    const groupId = params.get("group_id");

    let myRole = "";

    // ロール取得
    fetch(`api/get_user_role.php?group_id=${groupId}`)
      .then(res => res.json())
      .then(data => {
        if (!data.success) return alert(data.message);

        myRole = data.role;

        if (myRole === "owner" || myRole === "manager") {
          document.getElementById("editForm").classList.remove("d-none");
        } else {
          document.getElementById("memberOnly").classList.remove("d-none");
        }

        if (myRole === "owner") {
          document.getElementById("deleteBtn").classList.remove("d-none");
        }

        loadGroup();
      });

    // グループ情報取得
    function loadGroup() {
      fetch(`api/get_group_info.php?group_id=${groupId}`)
        .then(res => res.json())
        .then(data => {
          if (!data.success) return alert(data.message);

          const d = data.data;

          document.getElementById("groupName").textContent = d.group_name;
          document.getElementById("groupIcon").src = d.group_icon;
          document.getElementById("groupPublic").textContent =
            d.is_public ? "公開グループ" : "非公開グループ";

          document.getElementById("inputName").value = d.group_name;
          document.getElementById("inputPublic").value = d.is_public ? 1 : 0;

          renderMembers(d.manage_members);
        });
    }

    // メンバー表示
    function renderMembers(members) {
      const list = document.getElementById("memberList");
      list.innerHTML = "";

      members.forEach(m => {

        let controls = "";

        if (myRole === "owner" || myRole === "manager") {

          // 削除
          if (m.role !== "owner") {
            controls += `<button class="btn btn-sm btn-danger ms-2"
          onclick="removeUser('${m.user_id}')">削除</button>`;
          }

          // 権限変更
          if (myRole === "owner" || (myRole === "manager" && m.role !== "owner")) {
            controls += `
          <select onchange="changeRole('${m.user_id}', this.value)" class="ms-2">
            <option value="member">メンバー</option>
            <option value="manager">管理者</option>
          </select>
        `;
          }
        }

        list.innerHTML += `
      <li class="list-group-item d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
          <img src="${m.icon_url}" class="member-icon me-2">
          <div>
            <a href="profile.php?user_id=${m.user_id}">
              ${m.display_name}
            </a>
            <div class="small text-muted">${m.role}</div>
          </div>
        </div>
        <div>${controls}</div>
      </li>
    `;
      });
    }

    // 更新
    document.getElementById("editForm").addEventListener("submit", async e => {
      e.preventDefault();

      const formData = new FormData(e.target);
      formData.append("group_id", groupId);

      const res = await fetch("api/update_group.php", {
        method: "POST",
        body: formData
      });

      const data = await res.json();

      if (data.success) {
        location.href = `chat.php?group_id=${groupId}`;
      } else {
        showAlert(data.message);
      }
    });

    // 追加
    function addUser() {
      const id = document.getElementById("addUserInput").value;
      alert("モック: " + id + " を追加");
    }

    // 削除
    function removeUser(id) {
      alert("モック: " + id + " を削除");
    }

    // 権限変更
    function changeRole(id, role) {
      alert("モック: " + id + " → " + role);
    }

    // 削除
    document.getElementById("deleteBtn").addEventListener("click", async () => {
      if (!confirm("本当に削除する？")) return;

      const res = await fetch("api/delete_group.php", {
        method: "POST",
        body: new URLSearchParams({
          group_id: groupId
        })
      });

      const data = await res.json();

      if (data.success) {
        location.href = "dashboard.php";
      } else {
        showAlert(data.message);
      }
    });

    // 退会
    function leaveGroup() {
      if (!confirm("退会する？")) return;

      fetch("api/leave_group.php", {
          method: "POST",
          body: new URLSearchParams({
            group_id: groupId
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            location.href = "dashboard.php";
          } else {
            showAlert(data.message);
          }
        });
    }

    // アラート
    function showAlert(msg) {
      const box = document.getElementById("alertBox");
      box.textContent = msg;
      box.className = "alert alert-danger";
    }
  </script>

</body>

</html>