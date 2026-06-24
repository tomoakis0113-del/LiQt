<?php
require_once __DIR__ . '/../component/auth_check.php';
$csrfToken = new lib\CSRFToken();
?>

<!DOCTYPE html>
<html lang="ja">

<head>

  <!-- meta -->
  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <title>

    グループ編集 | LiQt

  </title>

  <!-- bootstrap -->
  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <style>

    body {

      background: #f8f9fa;

    }

    .card {

      border-radius: 24px;

    }

    .group-icon {

      width: 90px;
      height: 90px;
      object-fit: cover;
      border-radius: 24px;

    }

    .member-icon {

      width: 50px;
      height: 50px;
      border-radius: 50%;
      object-fit: cover;

    }

    .list-group-item {

      border-radius: 18px !important;
      margin-bottom: 14px;
      border: none;

    }

    .alert {

      border-radius: 18px;

    }

    .member-action-btn {

      min-width: 90px;

    }

  </style>

</head>

<body>

  <!-- header -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container py-5"
        style="max-width: 950px;">

    <!-- alert -->
    <div id="alertBox"></div>

    <!-- title -->
    <h2 class="fw-bold mb-4">

      グループ編集

    </h2>

    <!-- group -->
    <div class="card shadow-sm border-0 mb-5">

      <div class="card-body p-4 d-flex align-items-center">

        <!-- icon -->
        <img id="groupIcon"
             src="https://placehold.jp/150x150.png"
             class="group-icon me-4">

        <div>

          <!-- name -->
          <h3 id="groupName"
              class="fw-bold mb-2">

            読み込み中...

          </h3>

          <!-- public -->
          <div id="groupPublic"
               class="text-muted">

          </div>

        </div>

      </div>

    </div>

    <!-- group edit -->
    <form id="editForm"
          class="card shadow-sm border-0 mb-5 d-none"
          enctype="multipart/form-data">

      <div class="card-body p-4">

        <h4 class="fw-bold mb-4">

          グループ設定

        </h4>

        <!-- group name -->
        <div class="mb-4">

          <label class="form-label fw-bold">

            グループ名

          </label>

          <input type="text"
                 class="form-control"
                 id="inputName"
                 name="group_name">

        </div>

        <!-- group icon -->
        <div class="mb-4">

          <label class="form-label fw-bold">

            グループアイコン

          </label>

          <input type="file"
                 class="form-control"
                 id="groupIconInput"
                 name="group_icon"
                 accept="image/*">

        </div>

        <!-- public -->
        <div class="mb-4">

          <label class="form-label fw-bold">

            公開設定

          </label>

          <select class="form-select"
                  id="inputPublic"
                  name="is_public">

            <option value="1">

              公開

            </option>

            <option value="0">

              非公開

            </option>

          </select>

        </div>

        <!-- update -->
        <button id="updateButton"
                class="btn btn-primary w-100 py-3 rounded-pill">

          グループ設定を更新する

        </button>

        <!-- delete -->
        <button type="button"
                id="deleteBtn"
                class="btn btn-danger w-100 py-3 rounded-pill mt-3 d-none">

          グループ削除

        </button>

      </div>

    </form>

    <!-- member manage -->
    <form id="memberManageForm"
          class="card shadow-sm border-0 mb-5 d-none">

      <div class="card-body p-4">

        <h4 class="fw-bold mb-4">

          メンバー管理

        </h4>

        <!-- add user -->
        <div class="mb-4">

          <label class="form-label fw-bold">

            メンバー追加（ユーザーIDをカンマ区切り）

          </label>

          <input type="text"
                 class="form-control"
                 id="addUserIds"
                 placeholder="1,2,3">

        </div>

        <!-- member list -->
        <ul id="memberList"
            class="list-group mb-4">

          <li class="list-group-item">

            読み込み中...

          </li>

        </ul>

        <!-- update -->
        <button id="memberUpdateButton"
                type="submit"
                class="btn btn-primary w-100 py-3 rounded-pill">

          メンバー情報を更新する

        </button>

      </div>

    </form>

    <!-- leave -->
    <div id="memberOnly"
         class="d-none mb-5">

      <button class="btn btn-warning w-100 py-3 rounded-pill"
              onclick="leaveGroup()">

        グループを退会する

      </button>

    </div>

  </main>

  <!-- footer -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <!-- csrf -->
  <input type="hidden"
         id="csrf_token"
         value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <script>

    // params
    const params =
      new URLSearchParams(location.search);

    // group id
    const groupId =
      params.get("group_id");

    // csrf
    const csrfToken =
      document.getElementById("csrf_token").value;

    // role
    let myRole =
      "member";

    // remove ids
    let removeUserIds =
      [];

    // role changes
    let roleChanges =
      [];

    // =========================
    // check
    // =========================

    if (!groupId) {

      showAlert(
        "グループIDが存在しません",
        "danger"
      );

      throw new Error(
        "group_id not found"
      );

    }

    // =========================
    // init
    // =========================

    loadUserRole();

    // =========================
    // load role
    // =========================

    async function loadUserRole() {

      const formData =
        new FormData();

      formData.append(
        "csrf_token",
        csrfToken
      );

      formData.append(
        "group_id",
        groupId
      );

      try {

        const response =
          await fetch(
            "../api/group/get_user_role.php",
            {
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

        }

        catch {

          console.error(text);

          showAlert(
            "APIレスポンス形式が不正です",
            "danger"
          );

          return;

        }

        if (!result.success) {

          showAlert(
            result.message,
            "danger"
          );

          return;

        }

        myRole =
          result.data.role;

        // edit
        if (
          myRole === "owner" ||
          myRole === "manager"
        ) {

          document.getElementById("editForm")
            .classList
            .remove("d-none");

          document.getElementById("memberManageForm")
            .classList
            .remove("d-none");

        }

        // delete
        if (myRole === "owner") {

          document.getElementById("deleteBtn")
            .classList
            .remove("d-none");

        }

        // member
        if (myRole === "member") {

          document.getElementById("memberOnly")
            .classList
            .remove("d-none");

        }

        loadGroup();

      }

      catch (error) {

        console.error(error);

        showAlert(
          "通信エラーが発生しました",
          "danger"
        );

      }

    }

    // =========================
    // load group
    // =========================

    async function loadGroup() {

      const formData =
        new FormData();

      formData.append(
        "csrf_token",
        csrfToken
      );

      formData.append(
        "group_id",
        groupId
      );

      try {

        const response =
          await fetch(
            "../api/group/get_group_info.php",
            {
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

        }

        catch {

          console.error(text);

          showAlert(
            "APIレスポンス形式が不正です",
            "danger"
          );

          return;

        }

        if (!result.success) {

          showAlert(
            result.message,
            "danger"
          );

          return;

        }

        const data =
          result.data;

        renderGroupInfo(data);

        renderMembers(data.members);

      }

      catch (error) {

        console.error(error);

        showAlert(
          "通信エラーが発生しました",
          "danger"
        );

      }

    }

    // =========================
    // render group
    // =========================

    function renderGroupInfo(data) {

      document.getElementById("groupName").textContent =
        data.group_name || "名称未設定";

      document.getElementById("groupIcon").src =
        data.group_icon ||
        "https://placehold.jp/150x150.png";

      document.getElementById("groupPublic").textContent =
        data.is_public
          ? "公開グループ"
          : "非公開グループ";

      document.getElementById("inputName").value =
        data.group_name || "";

      document.getElementById("inputPublic").value =
        data.is_public
          ? 1
          : 0;

    }

    // =========================
    // render members
    // =========================

    function renderMembers(members) {

      const memberList =
        document.getElementById("memberList");

      memberList.innerHTML = "";

      if (!members || members.length === 0) {

        memberList.innerHTML = `

          <li class="list-group-item">

            メンバーはいません

          </li>

        `;

        return;

      }

      members.forEach(member => {

        const canManage =
          myRole === "owner" ||
          (
            myRole === "manager" &&
            member.role !== "owner"
          );

        let actionButtons = "";

        if (canManage) {

          actionButtons += `

            <div class="d-flex flex-column gap-2">

              <select class="form-select form-select-sm"
                      onchange="changeRole(${member.user_id}, this.value)">

                <option value="member"
                  ${member.role === 'member' ? 'selected' : ''}>

                  member

                </option>

                <option value="manager"
                  ${member.role === 'manager' ? 'selected' : ''}>

                  manager

                </option>

                ${myRole === "owner" ? `

                  <option value="owner"
                    ${member.role === 'owner' ? 'selected' : ''}>

                    owner

                  </option>

                ` : ""}

              </select>

              <button type="button"
                      class="btn btn-outline-danger btn-sm member-action-btn"
                      onclick="removeMember(${member.user_id})">

                削除

              </button>

            </div>

          `;

        }

        memberList.innerHTML += `

          <li class="list-group-item d-flex align-items-center justify-content-between shadow-sm">

            <div class="d-flex align-items-center">

              <img src="${member.icon_url || 'https://placehold.jp/100x100.png'}"
                   class="member-icon me-3">

              <div>

                <a href="/profile/profile.php?user_id=${member.user_id}"
                   class="fw-bold text-decoration-none">

                  ${escapeHtml(member.display_name)}

                </a>

                <div class="small text-muted mt-1">

                  ${escapeHtml(member.role)}

                </div>

              </div>

            </div>

            ${actionButtons}

          </li>

        `;

      });

    }

    // =========================
    // remove member
    // =========================

    function removeMember(userId) {

      if (!confirm("このメンバーを削除しますか？")) {

        return;

      }

      if (!removeUserIds.includes(String(userId))) {

        removeUserIds.push(String(userId));

      }

      showAlert(
        "メンバー更新ボタンを押すと反映されます",
        "warning"
      );

    }

    // =========================
    // change role
    // =========================

    function changeRole(userId, role) {

      const exists =
        roleChanges.find(
          item => item.user_id == userId
        );

      if (exists) {

        exists.role = role;

      }

      else {

        roleChanges.push({
          user_id: userId,
          role: role
        });

      }

      showAlert(
        "メンバー更新ボタンを押すと反映されます",
        "warning"
      );

    }

    // =========================
    // group update
    // =========================

    document.getElementById("editForm")
      .addEventListener("submit", async (e) => {

        e.preventDefault();

        const button =
          document.getElementById("updateButton");

        const formData =
          new FormData(e.target);

        formData.append(
          "csrf_token",
          csrfToken
        );

        formData.append(
          "group_id",
          groupId
        );

        try {

          button.disabled = true;

          button.innerHTML =
            "更新中...";

          const response =
            await fetch(
              "../api/group/update_group.php",
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

            showAlert(
              result.message,
              "danger"
            );

            return;

          }

          showAlert(
            result.message,
            "success"
          );

          loadGroup();

        }

        catch (error) {

          console.error(error);

          showAlert(
            "通信エラーが発生しました",
            "danger"
          );

        }

        finally {

          button.disabled = false;

          button.innerHTML =
            "グループ設定を更新する";

        }

      });

    // =========================
    // member update
    // =========================

    document.getElementById("memberManageForm")
      .addEventListener("submit", async (e) => {

        e.preventDefault();

        const button =
          document.getElementById("memberUpdateButton");

        const formData =
          new FormData();

        formData.append(
          "csrf_token",
          csrfToken
        );

        formData.append(
          "group_id",
          groupId
        );

        // add user ids
        const addUserIds =
          document.getElementById("addUserIds")
            .value
            .trim();

        if (addUserIds) {

          formData.append(
            "add_user_ids",
            addUserIds
          );

        }

        // remove users
        if (removeUserIds.length > 0) {

          formData.append(
            "remove_user_ids",
            removeUserIds.join(",")
          );

        }

        // role changes
        if (roleChanges.length > 0) {

          formData.append(
            "change_role_user_ids",
            roleChanges.map(
              item => item.user_id
            ).join(",")
          );

          formData.append(
            "new_roles",
            roleChanges.map(
              item => item.role
            ).join(",")
          );

        }

        try {

          button.disabled = true;

          button.innerHTML =
            "更新中...";

          const response =
            await fetch(
              "../api/group/update_group.php",
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

            showAlert(
              result.message,
              "danger"
            );

            return;

          }

          showAlert(
            result.message,
            "success"
          );

          removeUserIds = [];

          roleChanges = [];

          document.getElementById("addUserIds")
            .value = "";

          loadGroup();

        }

        catch (error) {

          console.error(error);

          showAlert(
            "通信エラーが発生しました",
            "danger"
          );

        }

        finally {

          button.disabled = false;

          button.innerHTML =
            "メンバー情報を更新する";

        }

      });

    // =========================
    // delete
    // =========================

    document.getElementById("deleteBtn")
      .addEventListener("click", async () => {

        if (!confirm("本当にグループを削除しますか？")) {

          return;

        }

        try {

          const formData =
            new FormData();

          formData.append(
            "csrf_token",
            csrfToken
          );

          formData.append(
            "group_id",
            groupId
          );

          const response =
            await fetch(
              "../api/group/delete_group.php",
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

            showAlert(
              result.message,
              "danger"
            );

            return;

          }

          showAlert(
            result.message,
            "success"
          );

          setTimeout(() => {

            location.href = "/dashboard/dashboard.php";

          }, 1000);

        }

        catch (error) {

          console.error(error);

          showAlert(
            "通信エラーが発生しました",
            "danger"
          );

        }

      });

    // =========================
    // leave
    // =========================

    async function leaveGroup() {

      if (!confirm("グループを退会しますか？")) {

        return;

      }

      try {

        const formData =
          new FormData();

        formData.append(
          "csrf_token",
          csrfToken
        );

        formData.append(
          "group_id",
          groupId
        );

        const response =
          await fetch(
            "../api/group/leave_group.php",
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

          showAlert(
            result.message,
            "danger"
          );

          return;

        }

        location.href = "/dashboard/dashboard.php";

      }

      catch (error) {

        console.error(error);

        showAlert(
          "通信エラーが発生しました",
          "danger"
        );

      }

    }

    // =========================
    // escape
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
    // alert
    // =========================

    function showAlert(message, type) {

      document.getElementById("alertBox").innerHTML = `

        <div class="alert alert-${type} shadow-sm">

          ${message}

        </div>

      `;

    }

  </script>

</body>

</html>
```
