<!DOCTYPE html>
<html lang="ja">
<head>

  <!-- meta -->
  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <title>
    サインイン | LiQt
  </title>

  <!-- Bootstrap -->
  <link rel="stylesheet"
        href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <style>

    body {

      background-color: #f5f7fb;

    }

    .signin-card {

      max-width: 500px;
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

    <div class="mx-auto signin-card card border-0 shadow">

      <div class="card-body p-5">

        <!-- タイトル -->
        <h2 class="fw-bold text-center mb-4">

          サインイン

        </h2>

        <!-- エラーメッセージ -->
        <div id="messageBox"></div>

        <!-- フォーム -->
        <form id="signinForm">

          <!-- メールアドレス -->
          <div class="mb-4">

            <label class="form-label fw-bold">

              メールアドレス

            </label>

            <input type="email"
                   id="mailAddress"
                   class="form-control form-control-lg"
                   placeholder="sample@example.com">

          </div>

          <!-- パスワード -->
          <div class="mb-4">

            <label class="form-label fw-bold">

              パスワード

            </label>

            <input type="password"
                   id="password"
                   class="form-control form-control-lg"
                   placeholder="パスワード">

          </div>

          <!-- CSRF -->
          <input type="hidden"
                 id="csrfToken"
                 value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

          <!-- ボタン -->
          <div class="d-grid">

            <button type="submit"
                    class="btn btn-success btn-lg rounded-pill">

              サインイン

            </button>

          </div>

        </form>

      </div>

    </div>

  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>

    // フォーム
    const form =
      document.getElementById("signinForm");

    // submit
    form.addEventListener("submit", async (e) => {

      // 通常送信停止
      e.preventDefault();

      // 値取得
      const mailAddress =
        document.getElementById("mailAddress").value;

      const password =
        document.getElementById("password").value;

      const csrfToken =
        document.getElementById("csrfToken").value;

      // FormData
      const formData = new FormData();

      formData.append("mail_address", mailAddress);
      formData.append("password", password);
      formData.append("csrf_token", csrfToken);

      try {

        // fetch
        const response =
          await fetch("../api/auth/signin.php", {

            method: "POST",
            body: formData

          });

        // json
        const data =
          await response.json();

        // 成功
        if (data.success) {

          showMessage(
            data.message,
            "success"
          );

          // 遷移
          setTimeout(() => {

            location.href =
              "../dashboard/dashboard.php";

          }, 1000);

        }

        // エラー
        else {

          showMessage(
            data.message,
            "danger"
          );

        }

      }

      // 通信エラー
      catch (error) {

        showMessage(
          "通信エラーが発生しました",
          "danger"
        );

        console.error(error);

      }

    });

    // =========================
    // メッセージ表示
    // =========================

    function showMessage(message, type) {

      const box =
        document.getElementById("messageBox");

      box.innerHTML = `

        <div class="alert alert-${type}">

          ${message}

        </div>

      `;

    }

  </script>

</body>
</html>