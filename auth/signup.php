<!DOCTYPE html>
<html lang="ja">

<head>
  <!-- meta -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="新規登録">
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <!-- title -->
  <title>サインアップ</title>

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

    <div class="card shadow-sm p-4" style="width: 100%; max-width: 450px;">

      <h3 class="text-center mb-4">サインアップ</h3>

      <!-- エラー表示 -->
      <div id="errorMsg" class="alert alert-danger d-none">
        入力内容に不備があります
      </div>

      <form id="signupForm">

        <!-- 🔸 ユーザー情報 -->
        <div class="mb-4">
          <label class="form-label">ユーザID</label>
          <input type="text" id="userid" class="form-control mb-2" placeholder="user123">
          <div class="form-text mb-3">※ 登録後は変更できません</div>

          <label class="form-label">表示名</label>
          <input type="text" id="name" class="form-control" placeholder="名前を入力">
        </div>

        <!-- 🔸 メール -->
        <div class="mb-4">
          <label class="form-label">メールアドレス</label>
          <input type="email" id="email" class="form-control" placeholder="メールアドレスを入力">
        </div>

        <!-- 🔸 パスワード -->
        <div class="mb-4">
          <label class="form-label">パスワード</label>
          <input type="password" id="password" class="form-control mb-2" placeholder="パスワードを入力">

          <label class="form-label">パスワード（確認）</label>
          <input type="password" id="confirm" class="form-control" placeholder="もう一度入力">
        </div>

        <!-- ボタン -->
        <button type="submit" class="btn w-100 text-white" style="background-color: #06C755;">
          登録する
        </button>

      </form>

    </div>

  </main>

  <!-- フッター -->
  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <!-- 🔹 モックJS -->
  <script>
    document.getElementById("signupForm").addEventListener("submit", function(e) {
      e.preventDefault();

      const user = document.getElementById("userid").value;
      const name = document.getElementById("name").value;
      const email = document.getElementById("email").value;
      const pass = document.getElementById("password").value;
      const confirm = document.getElementById("confirm").value;

      // 簡易チェック
      if (!user || !name || !email || !pass || pass !== confirm) {
        document.getElementById("errorMsg").classList.remove("d-none");
      } else {
        // 成功 → メール認証ページへ
        window.location.href = "verify-email.php";
      }
    });
  </script>

</body>

</html>