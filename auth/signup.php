<?php
require_once __DIR__ . '/../component/auth_check.php';
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

  <meta name="description"
        content="新規登録">

  <meta name="keywords"
        content="LiQt,SNS,コミュニティ,BLOG">

  <meta name="author"
        content="乙成,島田,勝原">

  <title>

    サインアップ | LiQt

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

    .signup-card {

      width: 100%;
      max-width: 480px;

      border: none;
      border-radius: 24px;

    }

  </style>

</head>

<body>
  <!-- 本文 -->
  <main class="container d-flex justify-content-center align-items-center py-5"
        style="min-height: 80vh; padding-bottom: 120px;">

    <!-- カード -->
    <div class="card signup-card shadow-sm">

      <div class="card-body p-5">

        <!-- タイトル -->
        <div class="text-center mb-4">

          <span class="material-symbols-outlined mb-3"
                style="font-size: 64px; color: #06C755;">

            person_add

          </span>

          <h2 class="fw-bold mb-2">

            サインアップ

          </h2>

          <p class="text-muted mb-0">

            アカウント情報を入力してください

          </p>

        </div>

        <!-- メッセージ -->
        <div id="responseMsg"
             class="alert d-none"></div>

        <!-- フォーム -->
        <form id="signupForm">

          <!-- CSRF -->
          <input type="hidden"
                 name="csrf_token"
                 value="<?= $csrfToken->getToken() ?>">

          <!-- ユーザID -->
          <div class="mb-4">

            <label class="form-label fw-semibold">

              ユーザID

            </label>

            <input type="text"
                   name="user_id"
                   id="userid"
                   class="form-control mb-2"
                   placeholder="user123"
                   required>

            <div class="form-text">

              ※ 半角英数字 5～20文字（登録後変更不可）

            </div>

          </div>

          <!-- 表示名 -->
          <div class="mb-4">

            <label class="form-label fw-semibold">

              表示名

            </label>

            <input type="text"
                   name="display_name"
                   id="name"
                   class="form-control"
                   placeholder="名前を入力"
                   required>

          </div>

          <!-- メール -->
          <div class="mb-4">

            <label class="form-label fw-semibold">

              メールアドレス

            </label>

            <input type="email"
                   name="mail_address"
                   id="email"
                   class="form-control"
                   placeholder="example@mail.com"
                   required>

          </div>

          <!-- パスワード -->
          <div class="mb-4">

            <label class="form-label fw-semibold">

              パスワード

            </label>

            <input type="password"
                   name="password"
                   id="password"
                   class="form-control mb-2"
                   placeholder="パスワードを入力"
                   required>

            <div class="form-text mb-3 small">

              6〜20文字、英大文字・小文字・数字を含む

            </div>

            <label class="form-label fw-semibold">

              パスワード（確認）

            </label>

            <input type="password"
                   id="confirm"
                   class="form-control"
                   placeholder="もう一度入力"
                   required>

          </div>

          <!-- ボタン -->
          <button type="submit"
                  id="submitBtn"
                  class="btn btn-lg w-100 text-white rounded-pill"
                  style="background-color: #06C755;">

            <span class="material-symbols-outlined align-middle me-1">

              person_add

            </span>

            登録する

          </button>

        </form>

      </div>

    </div>

  </main>
  <script>

    // =========================
    // サインアップ処理
    // =========================

    document
      .getElementById("signupForm")
      .addEventListener("submit", async function(e) {

        e.preventDefault();

        const responseMsg =
          document.getElementById("responseMsg");

        const submitBtn =
          document.getElementById("submitBtn");

        // パスワード取得
        const password =
          document.getElementById("password").value;

        const confirm =
          document.getElementById("confirm").value;

        // パスワード確認
        if (password !== confirm) {

          showMsg(
            "パスワードが一致しません",
            "alert-danger"
          );

          return;

        }

        // FormData
        const formData =
          new FormData(this);

        // ボタン無効化
        submitBtn.disabled = true;

        submitBtn.innerHTML = `

          <span class="spinner-border spinner-border-sm me-2"></span>

          登録中...

        `;

        responseMsg.classList.add("d-none");

        try {

          // fetch
          const response =
            await fetch("/api/auth/signup.php", {

              method: "POST",
              body: formData

            });

          // json
          const result =
            await response.json();

          console.log(result);

          // 成功
          if (response.ok && result.success) {

            showMsg(
              result.message,
              "alert-success"
            );

            // メールアドレス取得
            const mailAddress =
              document.getElementById("email").value;

            // verify_email.phpへ遷移
            setTimeout(() => {

              window.location.href =
                `/auth/verify_email.php?mail_address=${encodeURIComponent(mailAddress)}`;

            }, 1500);

          }

          // エラー
          else {

            showMsg(
              result.message || "エラーが発生しました",
              "alert-danger"
            );

            submitBtn.disabled = false;

            submitBtn.innerHTML = `

              <span class="material-symbols-outlined align-middle me-1">

                person_add

              </span>

              登録する

            `;

          }

        }

        catch (error) {

          console.error(error);

          showMsg(
            "サーバーとの通信に失敗しました",
            "alert-danger"
          );

          submitBtn.disabled = false;

          submitBtn.innerHTML = `

            <span class="material-symbols-outlined align-middle me-1">

              person_add

            </span>

            登録する

          `;

        }

      });

    // =========================
    // メッセージ表示
    // =========================

    function showMsg(text, type) {

      const responseMsg =
        document.getElementById("responseMsg");

      responseMsg.innerText = text;

      responseMsg.className =
        `alert ${type}`;

      responseMsg.classList.remove("d-none");

    }

  </script>

</body>

</html>