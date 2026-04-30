<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
  <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #06C755;">
    <div class="container">

      <!-- ロゴ -->
      <a class="navbar-brand fw-bold" href="/dashboard.php">
        LiQt
      </a>

      <!-- ハンバーガー -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">

        <!-- 🔍 検索フォーム -->
        <form class="d-flex mx-auto w-50" action="/search.php" method="GET">

          <!-- 検索種類 -->
          <select name="type" class="form-select me-2">
            <option value="user">ユーザID検索</option>
            <option value="group">グループ検索</option>
            <option value="blog">ブログ検索</option>
          </select>

          <!-- 入力欄 -->
          <input 
            type="text" 
            name="keyword" 
            class="form-control me-2" 
            placeholder="検索ワード"
            required
          >

          <!-- ボタン -->
          <button class="btn btn-light" type="submit">
            検索
          </button>

        </form>

      </div>
    </div>
  </nav>
</header>