<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>フッターナビゲーション</title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
</head>
<body>

  <main style="padding-bottom: 80px;">
    </main>

  <footer class="border-top fixed-bottom py-2" style="background-color: #06C755;">
    <div class="container">
      <div class="row text-center align-items-center">

        <div class="col">
          <a href="/profile/profile.php"
             class="text-white text-decoration-none d-flex flex-column align-items-center justify-content-center w-100">
            <span class="material-symbols-outlined" style="font-size: 36px; line-height: 1;">
              account_circle
            </span>
            <small style="font-size: 13px;">
              プロフィール
            </small>
          </a>
        </div>

        <div class="col">
          <a href="/dashboard/dashboard.php"
             class="text-white text-decoration-none d-flex flex-column align-items-center justify-content-center w-100">
            <span class="material-symbols-outlined" style="font-size: 36px; line-height: 1;">
              home
            </span>
            <small style="font-size: 13px;">
              ダッシュボード
            </small>
          </a>
        </div>

        <div class="col">
          <a href="/blog/blogs.php"
             class="text-white text-decoration-none d-flex flex-column align-items-center justify-content-center w-100">
            <span class="material-symbols-outlined" style="font-size: 36px; line-height: 1;">
              article
            </span>
            <small style="font-size: 13px;">
              ブログ
            </small>
          </a>
        </div>

        <div class="col">
          <a href="/search.php"
             class="text-white text-decoration-none d-flex flex-column align-items-center justify-content-center w-100">
            <span class="material-symbols-outlined" style="font-size: 36px; line-height: 1;">
              search
            </span>
            <small style="font-size: 13px;">
              検索
            </small>
          </a>
        </div>

      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>