<?php

session_start();

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
        content="メール認証ページ">

  <meta name="keywords"
        content="LiQt,SNS,コミュニティ,BLOG">

  <meta name="author"
        content="乙成,島田,勝原">

  <!-- title -->
  <title>

    メール認証 | LiQt

  </title>

  <!-- js -->
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- css -->
  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <link rel="stylesheet"
        href="../custom/custom-theme.css">

  <!-- Google Icons -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

  <style>

    body {

      background-color: #f5f7fb;

    }

    .verify-card {

      max-width: 520px;
      margin: auto;
      border: none;
      border-radius: 24px;

    }

  </style>

</head>

<body>

  <!-- ヘッダー -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <!-- 本文 -->
  <main class="container py-5"
        style="padding-bottom: 120px;">

    <!-- カード -->
    <div class="card shadow-sm verify-card">

      <div class="card-body p-5">

        <!-- タイトル -->
        <div class="text-center mb-4">

          <span class="material-symbols-outlined mb-3"
                style="font-size: 64px; color: #0d6efd;">

            mark_email_read

          </span>

          <h1 class="fw-bold mb-2">

            メール認証

          </h1>

          <p class="text-muted mb-0">

            メールアドレスと認証コードを入力してください

          </p>

        </div>

        <!-- メッセージ -->
        <div id="messageBox"></div>

        <!-- フォーム -->
        <form id="verifyForm">

          <!-- メール -->
          <div class="mb-4">

            <label class="form-label fw-semibold">

              メールアドレス

            </label>

            <input type="email"
                   class="form-control form-control-lg"
                   id="mail_address"
                   name="mail_address"
                   placeholder="example@mail.com"
                   required>

          </div>

          <!-- トークン -->
          <div class="mb-4">

            <label class="form-label fw-semibold">

              認証コード

            </label>

            <input type="text"
                   class="form-control form-control-lg"
                   id="token"
                   name="token"
                   placeholder="認証コードを入力"
                   required>

          </div>

          <!-- 認証ボタン -->
          <button type="submit"
                  id="verifyButton"
                  class="btn btn-primary btn-lg w-100 rounded-pill mb-3">

            <span class="material-symbols-outlined align-middle me-1">

              verified

            </span>

            認証する

          </button>

          <!-- トークン送信 -->
          <button type="button"
                  id="sendTokenButton"
                  class="btn btn-outline-secondary w-100 rounded-pill">

            <span class="material-symbols-outlined align-middle me-1">

              send

            </span>

            認証コードを送信

          </button>

        </form>

      </div>

    </div>

  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>

    // =========================
    // クールダウン秒数
    // =========================

    let cooldown = 30;

    // =========================
    // トークン送信
    // =========================

    document
      .getElementById("sendTokenButton")
      .addEventListener("click", sendVerifyToken);

    async function sendVerifyToken() {

      const button =
        document.getElementById("sendTokenButton");

      const mailAddress =
        document.getElementById("mail_address").value;

      // 未入力
      if (!mailAddress) {

        showMessage(
          "メールアドレスを入力してください",
          "danger"
        );

        return;

      }

      // disabled
      button.disabled = true;

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
          "<?= $csrfToken->getToken() ?>"
        );

        // fetch
        const response =
          await fetch("/api/auth/send_verify_token.php", {

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

          button.disabled = false;

          return;

        }

        // 成功
        showMessage(
          "認証コードを送信しました",
          "success"
        );

        // クールダウン開始
        startCooldown();

      }

      catch (error) {

        console.error(error);

        showMessage(
          "通信エラーが発生しました",
          "danger"
        );

        button.disabled = false;

      }

    }

    // =========================
    // クールダウン
    // =========================

    function startCooldown() {

      const button =
        document.getElementById("sendTokenButton");

      let timeLeft = cooldown;

      button.disabled = true;

      button.innerHTML = `

        <span class="material-symbols-outlined align-middle me-1">

          timer

        </span>

        ${timeLeft}秒後に再送信可能

      `;

      const timer = setInterval(() => {

        timeLeft--;

        button.innerHTML = `

          <span class="material-symbols-outlined align-middle me-1">

            timer

          </span>

          ${timeLeft}秒後に再送信可能

        `;

        // 終了
        if (timeLeft <= 0) {

          clearInterval(timer);

          button.disabled = false;

          button.innerHTML = `

            <span class="material-symbols-outlined align-middle me-1">

              send

            </span>

            認証コードを送信

          `;

        }

      }, 1000);

    }

    // =========================
    // メール認証
    // =========================

    document
      .getElementById("verifyForm")
      .addEventListener("submit", async function(e) {

        e.preventDefault();

        const button =
          document.getElementById("verifyButton");

        button.disabled = true;

        button.innerHTML = `

          <span class="spinner-border spinner-border-sm me-2"></span>

          認証中...

        `;

        try {

          // FormData
          const formData =
            new FormData();

          formData.append(
            "mail_address",
            document.getElementById("mail_address").value
          );

          formData.append(
            "token",
            document.getElementById("token").value
          );

          formData.append(
          "csrf_token",
          "<?= $csrfToken->getToken() ?>"
        );

          // fetch
          const response =
            await fetch("/api/auth/verify_email.php", {

              method: "POST",
              body: formData

            });

          // json
          const data =
            await response.json();

          console.log(data);

          // 失敗
          if (!data.success) {

            showMessage(
              data.message,
              "danger"
            );

            return;

          }

          // 成功
          showMessage(
            "メール認証に成功しました",
            "success"
          );

          // 遷移
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

          button.disabled = false;

          button.innerHTML = `

            <span class="material-symbols-outlined align-middle me-1">

              verified

            </span>

            認証する

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