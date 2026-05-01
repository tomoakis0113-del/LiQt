<!DOCTYPE html>
<html lang="ja">

<head>
  <!-- meta -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="サインイン">
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <!-- title -->
  <title>サインイン</title>

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

      <h3 class="text-center mb-4">サインイン</h3>

      <!-- エラーメッセージ（モック） -->
      <div id="errorMsg" class="alert alert-danger d-none">
        メールアドレスまたはパスワードが間違っています
      </div>

      <form id="loginForm">

        <!-- メール -->
        <div class="mb-3">
          <label class="form-label">メールアドレス</label>
          <input type="email" id="email" class="form-control" placeholder="メールアドレスを入力">
        </div>

        <!-- パスワード -->
        <div class="mb-3">
          <label class="form-label">パスワード</label>
          <input type="password" id="password" class="form-control" placeholder="パスワードを入力">
        </div>

        <!-- ログインボタン -->
        <button type="submit" class="btn w-100 text-white" style="background-color: #06C755;">
          サインインする
        </button>

      </form>

      <!-- リンク -->
      <div class="text-center mt-3">
        <a href="#" class="text-decoration-none d-block">パスワードをお忘れですか？</a>
        <a href="signup.php" class="text-decoration-none">新規登録はこちら</a>
      </div>

    </div>

  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <!-- 🔹 モック用JS -->
  <script>
    document.getElementById("loginForm").addEventListener("submit", function(e) {
      e.preventDefault();

      const email = document.getElementById("email").value;
      const password = document.getElementById("password").value;

      // モック判定（適当）
      if (email === "test@test.com" && password === "1234") {
        // 成功 → ダッシュボードへ
        window.location.href = "dashboard.php";
      } else {
        // 失敗 → エラー表示
        document.getElementById("errorMsg").classList.remove("d-none");
      }
    });
  </script>

</body>

</html>