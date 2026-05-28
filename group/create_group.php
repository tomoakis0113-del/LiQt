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

    グループ作成 | LiQt

  </title>

  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet"
        href="../custom/custom-theme.css">

  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

  <style>

    body {

      background-color: #f5f7fb;

    }

    .invite-badge {

      font-size: 14px;

    }

    .card-custom {

      border: none;
      border-radius: 24px;

    }

    .icon-preview {

      width: 120px;
      height: 120px;

      object-fit: cover;

      border-radius: 50%;

      border: 3px solid #dee2e6;

      background-color: white;

    }

    /* 下余白を増やす */
    .main-content {

      padding-bottom: 220px;

    }

    /* フォーム間隔を少し広めに */
    .form-section {

      margin-bottom: 2.5rem;

    }

  </style>

</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container py-5 main-content"
        style="max-width: 720px;">

    <div class="mb-5">

      <h1 class="fw-bold mb-2">

        グループ作成

      </h1>

      <p class="text-muted mb-0">

        新しいグループを作成します

      </p>

    </div>

    <div id="messageBox"
         class="mb-4"></div>

    <div class="card shadow-sm card-custom mb-5">

      <div class="card-body p-5">

        <form id="createGroupForm"
              enctype="multipart/form-data">

          <div class="form-section">

            <label class="form-label fw-semibold mb-3">

              グループ名

            </label>

            <input type="text"
                   class="form-control form-control-lg"
                   name="group_name"
                   placeholder="グループ名を入力"
                   required>

          </div>

          <div class="form-section">

            <label class="form-label fw-semibold mb-3">

              グループアイコン

            </label>

            <input type="file"
                   class="form-control form-control-lg"
                   id="group_icon"
                   name="group_icon"
                   accept="image/*">

            <small class="text-muted d-block mt-2">

              ※画像ファイルを選択してください（未設定でも作成可能）

            </small>

            <div class="text-center mt-5 mb-3">

              <img id="iconPreview"
                   src="https://placehold.jp/120x120.png"
                   class="icon-preview shadow-sm">

            </div>

          </div>

          <div class="form-section">

            <label class="form-label fw-semibold mb-3">

              公開設定

            </label>

            <select class="form-select form-select-lg"
                    name="is_public">

              <option value="true">

                公開

              </option>

              <option value="false">

                非公開

              </option>

            </select>

          </div>

          <div class="form-section">

            <label class="form-label fw-semibold mb-3">

              招待ユーザID

            </label>

            <div id="inviteList"
                 class="mb-4"></div>

            <div class="input-group input-group-lg">

              <input type="text"
                     id="inviteInput"
                     class="form-control"
                     placeholder="ユーザIDを入力">

              <button type="button"
                      class="btn btn-outline-primary px-4"
                      onclick="addInvite()">

                <span class="material-symbols-outlined align-middle">

                  add

                </span>

              </button>

            </div>

            <small class="text-muted d-block mt-2">

              ※複数追加できます

            </small>

          </div>

          <input type="hidden"
                 id="csrf_token"
                 value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

          <div class="mt-5 pt-3">

            <button type="submit"
                    id="submitButton"
                    class="btn btn-primary btn-lg w-100 rounded-pill py-3">

              <span class="material-symbols-outlined align-middle me-1">

                groups

              </span>

              グループを作成

            </button>

          </div>

        </form>

      </div>

    </div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>

    // 招待ユーザ一覧
    let inviteUsers = [];
    // 💡 CSRFトークン変数をグローバルで定義（DOM構築後に安全に取得）
    let csrfToken = "";

    // =========================
    // 画面初期ロード時の初期化
    // =========================
    document.addEventListener("DOMContentLoaded", () => {
      // HTML要素の構築が完了してから安全にトークンを取得して不具合を防止
      const tokenElement = document.getElementById("csrf_token");
      if (tokenElement) {
        csrfToken = tokenElement.value;
      }
    });

    // =========================
    // アイコンプレビュー
    // =========================

    document
      .getElementById("group_icon")
      .addEventListener("change", function(e) {

        const file =
          e.target.files[0];

        // 未選択
        if (!file) {

          return;

        }

        // 画像以外
        if (!file.type.startsWith("image/")) {

          showMessage(
            "画像ファイルを選択してください",
            "danger"
          );

          e.target.value = "";

          return;

        }

        // サイズ制限
        if (file.size > 5 * 1024 * 1024) {

          showMessage(
            "画像サイズは5MB以下にしてください",
            "danger"
          );

          e.target.value = "";

          return;

        }

        // プレビュー
        const imageUrl =
          URL.createObjectURL(file);

        document
          .getElementById("iconPreview")
          .src = imageUrl;

      });

    // =========================
    // 招待追加
    // =========================

    function addInvite() {

      const input =
        document.getElementById("inviteInput");

      const userId =
        input.value.trim();

      // 空
      if (!userId) {

        return;

      }

      // 重複
      if (inviteUsers.includes(userId)) {

        showMessage(
          "同じユーザIDは追加できません",
          "warning"
        );

        return;

      }

      // 追加
      inviteUsers.push(userId);

      // 初期化
      input.value = "";

      // 再描画
      renderInviteList();

    }

    // =========================
    // 招待一覧描画
    // =========================

    function renderInviteList() {

      const list =
        document.getElementById("inviteList");

      list.innerHTML = "";

      inviteUsers.forEach((id, index) => {

        list.innerHTML += `

          <span class="badge bg-primary me-2 mb-3 p-3 invite-badge">

            ${id}

            <button type="button"
                    class="btn-close btn-close-white ms-2"
                    style="font-size:10px;"
                    onclick="removeInvite(${index})">
            </button>

          </span>

        `;

      });

    }

    // =========================
    // 招待削除
    // =========================

    function removeInvite(index) {

      inviteUsers.splice(index, 1);

      renderInviteList();

    }

    // =========================
    // フォーム送信（API連携最適化）
    // =========================

    document
      .getElementById("createGroupForm")
      .addEventListener("submit", async function(e) {

        e.preventDefault();

        const submitButton =
          document.getElementById("submitButton");

        submitButton.disabled = true;

        submitButton.innerHTML = `

          <span class="spinner-border spinner-border-sm me-2"></span>

          作成中...

        `;

        try {

          // フォーム
          const form =
            e.target;

          // FormData
          const formData =
            new FormData();

          // グループ名
          formData.append(
            "group_name",
            form.group_name.value
          );

          // アイコン画像
          if (form.group_icon.files[0]) {

            formData.append(
              "group_icon",
              form.group_icon.files[0]
            );

          }

          // 公開設定
          formData.append(
            "is_public",
            form.is_public.value
          );

          // 招待ユーザ (invite_user_ids[])
          inviteUsers.forEach(id => {

            formData.append(
              "invite_user_ids[]",
              id
            );

          });

          // CSRFトークン
          formData.append(
            "csrf_token",
            csrfToken
          );

          // fetchを使って指定のエンドポイントへ送信
          const response =
            await fetch("/api/group/create_group.php", {
              method: "POST",
              body: formData
            });

          if (!response.ok) {
            throw new Error("サーバーエラーが発生しました。");
          }

          // 💡 直接JSONとしてパース
          const data = await response.json();

          // エラーハンドリング
          if (!data.success) {

            showMessage(
              data.message || "グループの作成に失敗しました",
              "danger"
            );

            return;

          }

          // 成功処理
          showMessage(
            data.message || "グループの作成に成功しました",
            "success"
          );

          // リダイレクト
          setTimeout(() => {

            location.href =
              "/dashboard/dashboard.php";

          }, 1000);

        }

        catch (error) {

          console.error(error);

          showMessage(
            "通信エラーが発生しました",
            "danger"
          );

        }

        finally {

          submitButton.disabled = false;

          submitButton.innerHTML = `

            <span class="material-symbols-outlined align-middle me-1">

              groups

            </span>

            グループを作成

          `;

        }

      });

    // =========================
    // メッセージ表示
    // =========================

    function showMessage(message, type) {

      const box =
        document.getElementById("messageBox");

      box.innerHTML = `

        <div class="alert alert-${type} shadow-sm rounded-4">

          ${message}

        </div>

      `;

    }

  </script>

</body>

</html>