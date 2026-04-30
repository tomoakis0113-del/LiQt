<!DOCTYPE html>
<html lang="ja">
<head>
  <!-- meta -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="メール認証">
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <!-- title -->
  <title>メール認証</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <!-- custom -->
  <link rel="stylesheet" href="../custom/custom-theme.css">
</head>

<body>
  <!-- ヘッダー -->
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <!-- 本文 -->
  <main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">

    <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">

      <h3 class="text-center mb-4">メール認証</h3>

      <!-- エラー -->
      <div id="errorMsg" class="alert alert-danger d-none">
        認証に失敗しました
      </div>

      <!-- 成功（モック用） -->
      <div id="successMsg" class="alert alert-success d-none">
        認証成功！ログインページへ移動します
      </div>

      <form id="verifyForm">

        <!-- メール -->
        <div class="mb-3">
          <label class="form-label">メールアドレス</label>
          <input type="email" id="email" class="form-control" placeholder="メールアドレスを入力">
        </div>

        <!-- トークン -->
        <div class="mb-3">
          <label class="form-label">認証コード</label>
          <input type="text" id="token" class="form-control" placeholder="6桁コードを入力">
        </div>

        <!-- 認証ボタン -->
        <button type="submit" class="btn w-100 text-white mb-2" style="background-color: #06C755;">
          認証する
        </button>

      </form>

      <!-- 再送 -->
      <button id="resendBtn" class="btn btn-outline-secondary w-100">
        コードを再送する
      </button>

      <!-- カウント表示 -->
      <div id="cooldownText" class="text-center mt-2 small text-muted d-none">
        再送まで 30秒
      </div>

    </div>

  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <!-- 🔹 モックJS -->
  <script>
    const form = document.getElementById("verifyForm");
    const errorMsg = document.getElementById("errorMsg");
    const successMsg = document.getElementById("successMsg");
    const resendBtn = document.getElementById("resendBtn");
    const cooldownText = document.getElementById("cooldownText");

    // 認証処理
    form.addEventListener("submit", function(e) {
      e.preventDefault();

      const email = document.getElementById("email").value;
      const token = document.getElementById("token").value;

      if (email === "test@test.com" && token === "123456") {
        successMsg.classList.remove("d-none");

        setTimeout(() => {
          window.location.href = "signin.php";
        }, 1500);
      } else {
        errorMsg.classList.remove("d-none");
      }
    });

    // 再送処理
    resendBtn.addEventListener("click", function() {
      let time = 30;

      resendBtn.disabled = true;
      cooldownText.classList.remove("d-none");

      const interval = setInterval(() => {
        cooldownText.textContent = `再送まで ${time}秒`;
        time--;

        if (time < 0) {
          clearInterval(interval);
          resendBtn.disabled = false;
          cooldownText.classList.add("d-none");
        }
      }, 1000);
    });
  </script>

</body>
</html>