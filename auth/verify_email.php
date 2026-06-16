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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="メール認証ページ">
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <title>メール認証 | LiQt</title>

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../custom/custom-theme.css">

  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

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

<body onload="document.getElementById('sendTokenButton').click();">

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
    <div class="card shadow-sm verify-card">
      <div class="card-body p-5">
        <div class="text-center mb-4">
          <span class="material-symbols-outlined mb-3" style="font-size: 64px; color: #28a745;">
            mark_email_read
          </span>
          <h1 class="fw-bold mb-2">メール認証</h1>
          <p class="text-muted mb-0">認証コードを入力してください</p>
          <p class="text-muted">認証コードが届かない場合は、迷惑メールフォルダもご確認ください</p>
        </div>

        <div id="messageBox"></div>

        <form id="verifyForm">
          <div class="mb-4">
            <label class="form-label fw-semibold">認証コード</label>
            <input type="text" class="form-control form-control-lg" id="token" name="token" placeholder="認証コードを入力" required>
          </div>

          <button type="submit" id="verifyButton" class="btn btn-success btn-lg w-100 rounded-pill mb-3">
            <span class="material-symbols-outlined align-middle me-1">verified</span>
            認証する
          </button>

          <button type="button" id="sendTokenButton" class="btn btn-outline-secondary w-100 rounded-pill">
            <span class="material-symbols-outlined align-middle me-1">send</span>
            認証コードを送信
          </button>
        </form>
      </div>
    </div>
  </main>
  <script>
    let cooldown = 30;
    document.getElementById("sendTokenButton").addEventListener("click", sendVerifyToken);

    async function sendVerifyToken() {
      const button = document.getElementById("sendTokenButton");
      button.disabled = true;

      try {
        const formData = new FormData();
        formData.append("csrf_token", "<?= $csrfToken->getToken() ?>");

        const response = await fetch("/api/auth/send_verify_token.php", { method: "POST", body: formData });
        const data = await response.json();
        if (!data.success) { showMessage(data.message, "danger"); button.disabled = false; return; }

        showMessage("認証コードを送信しました", "success");
        startCooldown();
      } catch (error) {
        console.error(error);
        showMessage("通信エラーが発生しました", "danger");
        button.disabled = false;
      }
    }

    function startCooldown() {
      const button = document.getElementById("sendTokenButton");
      let timeLeft = cooldown;
      button.disabled = true;
      const timer = setInterval(() => {
        timeLeft--;
        button.innerHTML = `<span class="material-symbols-outlined align-middle me-1">timer</span>${timeLeft}秒後に再送信可能`;
        if (timeLeft <= 0) { clearInterval(timer); button.disabled = false; button.innerHTML = `<span class="material-symbols-outlined align-middle me-1">send</span>認証コードを送信`; }
      }, 1000);
    }

    document.getElementById("verifyForm").addEventListener("submit", async function(e) {
      e.preventDefault();
      const button = document.getElementById("verifyButton");
      button.disabled = true;

      button.innerHTML = `
        <span class="spinner-border spinner-border-sm align-middle me-1" role="status" aria-hidden="true"></span>
        処理中...
      `;

      try {
        const formData = new FormData();
        formData.append("token", document.getElementById("token").value);
        formData.append("csrf_token", "<?= $csrfToken->getToken() ?>");

        const response = await fetch("/api/auth/verify_email.php", { method: "POST", body: formData });
        const data = await response.json();

        if (!data.success) { 
          showMessage(data.message, "danger"); 
          return; 
        }

        showMessage("メール認証に成功しました", "success");

        // 💡 成功確認後、1秒後にホワイトアウト演出を開始
        setTimeout(() => {
          const overlay = document.getElementById("transition-overlay");
          overlay.classList.add("active");

          setTimeout(() => {
            location.href = "/dashboard/dashboard.php";
          }, 1500);
        }, 1000);

      } catch (error) {
        console.error(error);
        showMessage("通信エラーが発生しました", "danger");
      } finally {
        button.disabled = false;
        button.innerHTML = `
          <span class="material-symbols-outlined align-middle me-1">verified</span>
          認証する
        `;
      }
    });

    function showMessage(message, type) {
      const box = document.getElementById("messageBox");
      box.innerHTML = `<div class="alert alert-${type} shadow-sm">${message}</div>`;
    }
  </script>
</body>
</html>