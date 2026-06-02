<?php
  require_once __DIR__ . '/auth_check.php';
?>
<header>
  <nav class="navbar navbar-expand-lg navbar-dark shadow-sm" style="background-color: #06C755;">
    <div class="container">

      <!-- ロゴ -->
      <a class="navbar-brand fw-bold fs-2" href="/dashboard/dashboard.php">
        LiQt
      </a>

      <!-- ハンバーガー -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">

        <form class="d-flex mx-auto w-50" action="/search.php" method="GET">
          <div class="input-group">

            <select name="type" class="form-select border-end-0" style="max-width: 130px; z-index: 1;" required>
              <option value="user">ユーザID</option>
              <option value="group">グループ</option>
            </select>

            <input
              type="text"
              name="keyword"
              class="form-control border-start-0"
              placeholder="検索ワードを入力..."
              required>

            <button type="submit" class="btn btn-light border-start-0 text-success" style="background-color: #fff;">
              <svg xmlns="http://www.w3.org/2000/svg" style="width: 18px; height: 18px;" fill="currentColor" class="bi bi-search align-middle" viewBox="0 0 16 16">
                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"></path>
              </svg>
            </button>
          </div>
        </form>
      </div>
    </div>
  </nav>
</header>