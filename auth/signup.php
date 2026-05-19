<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="新規登録">
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <title>サインアップ</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../custom/custom-theme.css">
</head>

<body>
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">

    <div class="card shadow-sm p-4" style="width: 100%; max-width: 450px;">

      <h3 class="text-center mb-4">サインアップ</h3>

      <div id="responseMsg" class="alert d-none"></div>

      <form id="signupForm">
        <input type="hidden" name="csrf_token" value="dummy_token">

        <div class="mb-4">
          <label class="form-label">ユーザID</label>
          <input type="text" name="user_id" id="userid" class="form-control mb-2" placeholder="user123" required>
          <div class="form-text mb-3">※ 半角英数字 5～20 文字（登録後変更不可）</div>

          <label class="form-label">表示名</label>
          <input type="text" name="display_name" id="name" class="form-control" placeholder="名前を入力" required>
        </div>

        <div class="mb-4">
          <label class="form-label">メールアドレス</label>
          <input type="email" name="mail_address" id="email" class="form-control" placeholder="example@mail.com" required>
        </div>

        <div class="mb-4">
          <label class="form-label">パスワード</label>
          <input type="password" name="password" id="password" class="form-control mb-2" placeholder="パスワードを入力" required>
          <div class="form-text mb-3 small">6-20文字、英大文字・小文字・数字を含む</div>

          <label class="form-label">パスワード（確認）</label>
          <input type="password" id="confirm" class="form-control" placeholder="もう一度入力" required>
        </div>

        <button type="submit" id="submitBtn" class="btn w-100 text-white" style="background-color: #06C755;">
          登録する
        </button>

      </form>

    </div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    document.getElementById("signupForm").addEventListener("submit", async function(e) {
      e.preventDefault();

      const responseMsg = document.getElementById("responseMsg");
      const submitBtn = document.getElementById("submitBtn");
      
      // クライアント側での簡易バリデーション（パスワード一致確認）
      const password = document.getElementById("password").value;
      const confirm = document.getElementById("confirm").value;

      if (password !== confirm) {
        showMsg("パスワードが一致しません", "alert-danger");
        return;
      }

      // フォームデータの準備
      const formData = new FormData(this);
      
      // 二重送信防止
      submitBtn.disabled = true;
      responseMsg.classList.add("d-none");

      try {
        // APIへリクエスト送信
        const response = await fetch('/api/auth/signup.php', { // 実際のパスに合わせて調整してください
          method: 'POST',
          body: formData
        });

        const result = await response.json();

        if (response.ok && result.success) {
          // 成功時
          showMsg(result.message, "alert-success");
          setTimeout(() => {
            window.location.href = "verify-email.php";
          }, 2000);
        } else {
          // APIからのエラー時
          showMsg(result.message || "エラーが発生しました", "alert-danger");
          submitBtn.disabled = false;
        }
      } catch (error) {
        console.error("Error:", error);
        showMsg("サーバーとの通信に失敗しました", "alert-danger");
        submitBtn.disabled = false;
      }
    });

    /**
     * メッセージ表示用ユーティリティ
     */
    function showMsg(text, type) {
      const responseMsg = document.getElementById("responseMsg");
      responseMsg.innerText = text;
      responseMsg.className = `alert ${type}`;
      responseMsg.classList.remove("d-none");
    }
  </script>

</body>

</html>