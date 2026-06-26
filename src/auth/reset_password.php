<?php
require_once __DIR__ . '/../vendor/autoload.php';

// CSRF生成
$csrfToken = new lib\CSRFToken();
?>

<!DOCTYPE html>

<html lang="ja">

<head>

  <!-- meta -->
  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <meta name="description"
        content="パスワードリセットページ">

  <meta name="keywords"
        content="LiQt,SNS,コミュニティ,BLOG">

  <meta name="author"
        content="乙成,島田,勝原">

  <!-- title -->
  <title>

    パスワードリセット | LiQt

  </title>

  <!-- Bootstrap -->
  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- CSS -->
  <link rel="stylesheet"
        href="../custom/custom-theme.css">

  <!-- Google Icons -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

  <style>

    body {

      background-color: #f5f7fb;

    }

    .reset-card {

      max-width: 560px;
      margin: auto;

      border: none;
      border-radius: 24px;

    }

    .hidden-section {

      display: none;

    }

  </style>

</head>

<body>
  <!-- 本文 -->
  <main class="container py-5"
        style="padding-bottom: 120px;">

    <!-- カード -->
    <div class="card shadow-sm reset-card">

      <div class="card-body p-5">

        <!-- タイトル -->
        <div class="text-center mb-4">

          <span class="material-symbols-outlined mb-3"
                style="font-size: 64px; color: #dc3545;">

            lock_reset

          </span>

          <h1 class="fw-bold mb-2">

            パスワードリセット

          </h1>

          <p class="text-muted mb-0">

            メール認証後、新しいパスワードを設定してください

          </p>

        </div>

        <!-- メッセージ -->
        <div id="messageBox"></div>

        <!-- STEP1 -->
        <div id="requestSection">

          <h5 class="fw-bold mb-3">

            STEP1：認証メール送信

          </h5>

          <div class="mb-4">

            <label class="form-label fw-semibold">

              メールアドレス

            </label>

            <input type="email"
                   class="form-control form-control-lg"
                   id="mail_address"
                   placeholder="example@mail.com"
                   value="<?= htmlspecialchars($_GET['mail_address'] ?? '') ?>"
                   required>

          </div>

          <button type="button"
                  id="requestButton"
                  class="btn btn-primary btn-lg w-100 rounded-pill">

            <span class="material-symbols-outlined align-middle me-1">

              send

            </span>

            リセットメールを送信

          </button>

        </div>

        <!-- STEP2 -->
        <div id="resetSection"
             class="hidden-section mt-5">

          <hr class="my-4">

          <h5 class="fw-bold mb-3">

            STEP2：パスワード再設定

          </h5>

          <form id="resetPasswordForm">

            <!-- トークン -->
            <div class="mb-4">

              <label class="form-label fw-semibold">

                リセットトークン

              </label>

              <input type="text"
                     class="form-control form-control-lg"
                     id="token"
                     placeholder="メールに届いたトークンを入力"
                     required>

            </div>

            <!-- パスワード -->
            <div class="mb-4">

              <label class="form-label fw-semibold">

                新しいパスワード

              </label>

              <input type="password"
                     class="form-control form-control-lg mb-2"
                     id="new_password"
                     placeholder="新しいパスワード"
                     required>

              <div class="form-text">

                6〜20文字、英大文字・小文字・数字を含めてください

              </div>

            </div>

            <!-- パスワード確認 -->
            <div class="mb-4">

              <label class="form-label fw-semibold">

                新しいパスワード（確認）

              </label>

              <input type="password"
                     class="form-control form-control-lg"
                     id="new_password_confirm"
                     placeholder="もう一度入力"
                     required>

            </div>

            <!-- CSRF -->
            <input type="hidden"
                   id="csrf_token"
                   value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

            <!-- ボタン -->
            <button type="submit"
                    id="resetButton"
                    class="btn btn-danger btn-lg w-100 rounded-pill">

              <span class="material-symbols-outlined align-middle me-1">

                lock_reset

              </span>

              パスワードをリセット

            </button>

          </form>

        </div>

      </div>

    </div>

  </main>
  
  <script>

    // =========================
    // リセットメール送信
    // =========================

    document
      .getElementById("requestButton")
      .addEventListener("click", async function() {

        const button =
          document.getElementById("requestButton");

        const mailAddress =
          document.getElementById("mail_address").value;

        const csrfToken =
          document.getElementById("csrf_token").value;

        // 空チェック
        if (!mailAddress) {

          showMessage(
            "メールアドレスを入力してください",
            "danger"
          );

          return;

        }

        // disabled
        button.disabled = true;

        button.innerHTML = `

          <span class="spinner-border spinner-border-sm me-2"></span>

          送信中...

        `;

        try {

          // FormData
          const formData =
            new FormData();

          formData.append(
            "mail_address",
            mailAddress
          );

          formData.append(
            "csrf_token",
            csrfToken
          );

          // fetch
          const response =
            await fetch("/api/auth/request_password_reset.php", {

              method: "POST",
              body: formData

            });

          // json
          const data =
            await response.json();

          // エラー
          if (!data.success) {

            showMessage(
              data.message,
              "danger"
            );

            return;

          }

          // 成功
          showMessage(
            data.message,
            "success"
          );

          // STEP2表示
          document
            .getElementById("resetSection")
            .style.display = "block";

        }

        catch (error) {

          console.error(error);

          showMessage(
            "通信エラーが発生しました",
            "danger"
          );

        }

        finally {

          button.disabled = false;

          button.innerHTML = `

            <span class="material-symbols-outlined align-middle me-1">

              send

            </span>

            リセットメールを送信

          `;

        }

      });

    // =========================
    // パスワードリセット
    // =========================

    document
      .getElementById("resetPasswordForm")
      .addEventListener("submit", async function(e) {

        e.preventDefault();

        const button =
          document.getElementById("resetButton");

        // 値取得
        const mailAddress =
          document.getElementById("mail_address").value;

        const token =
          document.getElementById("token").value;

        const newPassword =
          document.getElementById("new_password").value;

        const confirmPassword =
          document.getElementById("new_password_confirm").value;

        const csrfToken =
          document.getElementById("csrf_token").value;

        // パスワード一致確認
        if (newPassword !== confirmPassword) {

          showMessage(
            "パスワードが一致しません",
            "danger"
          );

          return;

        }

        // パスワードルール
        const passwordRegex =
          /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,20}$/;

        if (!passwordRegex.test(newPassword)) {

          showMessage(
            "パスワードは6〜20文字で英大文字・小文字・数字を含めてください",
            "danger"
          );

          return;

        }

        // disabled
        button.disabled = true;

        button.innerHTML = `

          <span class="spinner-border spinner-border-sm me-2"></span>

          リセット中...

        `;

        try {

          // FormData
          const formData =
            new FormData();

          formData.append(
            "mail_address",
            mailAddress
          );

          formData.append(
            "token",
            token
          );

          formData.append(
            "new_password",
            newPassword
          );

          formData.append(
            "csrf_token",
            csrfToken
          );

          // fetch
          const response =
            await fetch("/api/auth/reset_password.php", {

              method: "POST",
              body: formData

            });

          // json
          const data =
            await response.json();

          // エラー
          if (!data.success) {

            showMessage(
              data.message,
              "danger"
            );

            return;

          }

          // 成功
          showMessage(
            data.message,
            "success"
          );

          // サインインへ
          setTimeout(() => {

            location.href =
              "/auth/signin.php";

          }, 1500);

        }

        catch (error) {

          console.error(error);

          showMessage(
            "通信エラーが発生しました",
            "danger"
          );

        }

        finally {

          button.disabled = false;

          button.innerHTML = `

            <span class="material-symbols-outlined align-middle me-1">

              lock_reset

            </span>

            パスワードをリセット

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

        <div class="alert alert-${type} shadow-sm">

          ${message}

        </div>

      `;

    }

  </script>

</body>

</html>