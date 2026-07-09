<?php
require_once __DIR__ . '/../vendor/autoload.php';

$sessionHandler = new lib\Session();
if($sessionHandler->isSignedIn()){
    header('Location: ../dashboard/dashboard.php');
    exit;
}

// CSRF生成
$csrfToken = new lib\CSRFToken();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>サインイン | LiQt</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      background-color: #f5f7fb;
    }

    .signin-card {
      max-width: 500px;
      border-radius: 24px;
    }

    /* ==========================================
       💫 演出：緑と白のホワイトアウト遷移レイヤー
    ========================================== */
    #transition-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100vw;
      height: 100vh;
      background-color: rgba(255, 255, 255, 0); /* 最初は透明 */
      z-index: 10000;
      display: none;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }

    /* 緑と白の爆発エネルギーレイヤー */
    .energy-ring {
      position: absolute;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: radial-gradient(circle, #ffffff 10%, #28a745 50%, rgba(40, 167, 69, 0.2) 70%, rgba(255,255,255,0) 100%);
      box-shadow: 0 0 80px #28a745, inset 0 0 40px #ffffff;
      transform: scale(0);
      opacity: 0;
    }

    /* 画像と文字を横並びにするコンテナ */
    .transition-logo-combo {
      position: absolute;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 24px;
      opacity: 0;
      transform: scale(0.5);
      z-index: 10001;
      filter: drop-shadow(0 0 15px rgba(255, 255, 255, 1)) drop-shadow(0 4px 20px rgba(0, 0, 0, 0.15));
    }

    .logo-svg-area {
      width: 140px;
      height: 140px;
    }
    .logo-svg-area svg {
      width: 100%;
      height: 100%;
    }

    /* 右側の「LiQt」文字スタイル */
    .logo-text-area {
      font-size: 5.5rem;
      font-weight: 900;
      letter-spacing: 2px;
      color: #11141a; /* 白背景で見えやすくするためクールなダークグレーに */
      text-shadow: 0 0 20px rgba(40, 167, 69, 0.4);
      line-height: 1;
    }

    /* アニメーション起動時 */
    #transition-overlay.active {
      display: flex;
      animation: bgWhiteout 1.5s cubic-bezier(0.25, 1, 0.2, 1) forwards;
    }

    #transition-overlay.active .energy-ring {
      animation: flashExpandWhite 1.5s cubic-bezier(0.25, 1, 0.2, 1) forwards;
    }

    #transition-overlay.active .transition-logo-combo {
      animation: logoComboPopWhite 1.5s cubic-bezier(0.15, 0.85, 0.35, 1) forwards;
    }

    /* 背景自体を真っ白に染め上げる */
    @keyframes bgWhiteout {
      0% { background-color: rgba(255, 255, 255, 0); }
      35% { background-color: rgba(255, 255, 255, 0.3); }
      70% { background-color: #ffffff; }
      100% { background-color: #ffffff; }
    }

    /* 緑と白の光が画面を包み込む */
    @keyframes flashExpandWhite {
      0% { transform: scale(0); opacity: 0; }
      15% { opacity: 1; }
      60% { background: radial-gradient(circle, #ffffff 40%, #28a745 70%, rgba(255,255,255,1) 90%); opacity: 1; }
      100% { transform: scale(80); opacity: 0; }
    }

    /* ロゴが飛び出して、最後は白い光の中にフェードアウト */
    @keyframes logoComboPopWhite {
      0% { opacity: 0; transform: scale(0.4); filter: blur(8px); }
      20% { opacity: 1; transform: scale(1.05); filter: blur(0px); }
      35% { transform: scale(1.0); }
      60% { opacity: 1; filter: blur(0px); }
      100% { opacity: 0; transform: scale(0.8); filter: blur(10px); }
    }
  </style>
</head>

<body>

  <div id="transition-overlay">
    <div class="energy-ring"></div>
    <div class="transition-logo-combo">
      <div class="logo-svg-area">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
          <defs>
            <linearGradient id="liqt-bg-grad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#4ae17a" />
              <stop offset="50%" stop-color="#28a745" />
              <stop offset="100%" stop-color="#3b82f6" />
            </linearGradient>
            <filter id="subtle-shadow" x="-10%" y="-10%" width="120%" height="120%">
              <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#000000" flood-opacity="0.15" />
            </filter>
          </defs>
          <rect x="32" y="32" width="448" height="448" rx="110" fill="url(#liqt-bg-grad)" filter="url(#subtle-shadow)" />
          <path d="M110 290c0-65 55-115 125-115s125 50 125 115c0 35-15 65-42 85l12 45-50-25c-15 7-31 10-45 10-70 0-125-50-125-115z" fill="#ffffff" opacity="0.95" />
          <path d="M220 250c0-55 50-100 110-100s110 45 110 100c0 30-15 58-38 75l10 40-45-22c-12 5-25 7-37 7-60 0-110-45-110-100z" fill="rgba(255, 255, 255, 0.15)" stroke="#ffffff" stroke-width="12" stroke-linecap="round" stroke-linejoin="round" />
          <line x1="265" y1="285" x2="295" y2="215" stroke="#ffffff" stroke-width="18" stroke-linecap="round" />
          <path d="M195 230l-25 20l25 20" fill="none" stroke="#ffffff" stroke-width="16" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M365 230l25 20l-25 20" fill="none" stroke="#ffffff" stroke-width="16" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <div class="logo-text-area">LiQt</div>
    </div>
  </div>

  <main class="container py-5" style="padding-bottom: 120px;">
    <div class="card border-0 shadow signin-card mx-auto">
      <div class="card-body p-5">
        <h2 class="fw-bold text-center mb-4">サインイン</h2>
        <div id="messageBox"></div>

        <form id="signinForm">
          <div class="mb-4">
            <label class="form-label fw-bold">メールアドレス</label>
            <input type="email" id="mailAddress" class="form-control form-control-lg" placeholder="sample@example.com" required>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold">パスワード</label>
            <input type="password" id="password" class="form-control form-control-lg" placeholder="パスワード" required>
          </div>

          <input type="hidden" id="csrf_token" name="csrf_token" value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

          <div class="d-grid">
            <button type="submit" class="btn btn-success btn-lg rounded-pill">サインイン</button>
          </div>
        </form>

        <div class="text-center mt-4">
          <span>アカウントをお持ちでないですか？</span>
          <a href="/auth/signup.php" class="text-decoration-none fw-bold">サインアップ</a>
        </div>
        <div class="text-center mt-2">
          <a href="/auth/reset_password.php" class="text-decoration-none text-muted">パスワードを忘れた場合</a>
        </div>
      </div>
    </div>
  </main>
  
  <script>
    const form = document.getElementById("signinForm");
    const disableInput = () => {
      form.querySelectorAll("input, button").forEach(el => el.disabled = true);
    };
    const enableInput = () => {
      form.querySelectorAll("input, button").forEach(el => el.disabled = false);
    };

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      disableInput();

      const mailAddress = document.getElementById("mailAddress").value;
      const password = document.getElementById("password").value;
      const csrfToken = document.getElementById("csrf_token").value;

      const formData = new FormData();
      formData.append("mail_address", mailAddress);
      formData.append("password", password);
      formData.append("csrf_token", csrfToken);

      try {
        const response = await fetch("../api/auth/signin.php", {
          method: "POST",
          body: formData
        });

        const text = await response.text();
        const data = JSON.parse(text);

        if (data.success === true) {
          showMessage(data.message, "success");

          // 💡 成功したら1秒後にホワイトアウト演出を開始する
          setTimeout(() => {
            const overlay = document.getElementById("transition-overlay");
            overlay.classList.add("active");

            // 演出中の1.5秒後に画面遷移
            setTimeout(() => {
              location.href = "../dashboard/dashboard.php";
            }, 1500);

          }, 1000);
        } else {
          showMessage(data.message || "ログインに失敗しました", "danger");
          enableInput();
        }
      } catch (error) {
        console.error(error);
        showMessage("通信エラーが発生しました", "danger");
        enableInput();
      }
    });

    function showMessage(message, type) {
      const box = document.getElementById("messageBox");
      box.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
    }
  </script>
</body>
</html>