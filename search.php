<?php
session_start();
require_once __DIR__ . '/vendor/autoload.php';

// CSRFトークンの生成
$csrfToken = new lib\CSRFToken();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="ユーザー、グループ、ブログを条件から検索できるページです。">
  <meta name="keywords" content="LiQt,SNS,コミュニティ,BLOG">
  <meta name="author" content="乙成,島田,勝原">

  <title>検索結果 | LiQt</title>

  <script src="libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="custom/custom-theme.css">
  
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />

  <style>
    body {
      background-color: #f5f7fb;
    }

    /* 各種結果カードの共通スタイル */
    .result-card {
      transition: 0.2s;
      margin-bottom: 1.5rem;
    }

    .result-card:hover {
      transform: translateY(-2px);
    }

    /* アイコンスタイル */
    .search-icon {
      width: 65px;
      height: 65px;
      border-radius: 50%;
      object-fit: cover;
    }

    /* タグの余白 */
    .tag-badge {
      margin-right: 4px;
      margin-bottom: 4px;
    }
  </style>
</head>
<body class="bg-light">

  <?php require_once __DIR__ . '/component/header.php'; ?>
  <input type="hidden" id="csrf_token" value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <main class="container py-4" style="max-width: 800px; padding-bottom: 120px;">

    <div class="card border-0 shadow-sm rounded-4 mb-4">
      <div class="card-body p-4">
        <h3 class="fw-bold mb-3">検索</h3>
        <form id="searchForm">
          <div class="row g-3">
            <div class="col-md-3">
              <select class="form-select" name="type" id="searchType">
                <option value="user">ユーザーID</option>
                <option value="group">グループ</option>
                <option value="blog">ブログ</option>
              </select>
            </div>
            <div class="col-md-9">
              <div class="input-group">
                <input type="text" class="form-control" name="query" id="searchQuery" placeholder="検索キーワードを入力">
                <button type="submit" class="btn btn-primary px-4">
                  <span class="material-symbols-outlined align-middle me-1">search</span>検索
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <div id="messageBox" class="mb-4"></div>

    <h4 id="resultHeading" class="fw-bold mb-4 d-none">検索結果</h4>

    <div id="searchResultList"></div>

  </main>

  <?php require_once __DIR__ . '/component/footer.php'; ?>

  <script>
    // URLパラメータの取得
    const params = new URLSearchParams(location.search);
    const paramType = params.get("type");
    const paramQuery = params.get("query");

    // CSRFトークン取得
    const csrfToken = document.getElementById("csrf_token").value;

    // 画面ロード時の処理（URLにパラメータがあれば自動検索）
    window.addEventListener("DOMContentLoaded", () => {
      if (paramType) {
        document.getElementById("searchType").value = paramType;
      }
      if (paramQuery) {
        document.getElementById("searchQuery").value = paramQuery;
      }

      if (paramType && paramQuery) {
        executeSearch(paramType, paramQuery);
      }
    });

    // フォーム送信イベント
    document.getElementById("searchForm").addEventListener("submit", (e) => {
      e.preventDefault();
      const type = document.getElementById("searchType").value;
      const query = document.getElementById("searchQuery").value.trim();

      if (!query) {
        showMessage("検索キーワードを入力してください", "warning");
        return;
      }

      // URLのパラメータを書き換えて履歴に残す（画面リロードは発生しません）
      const newUrl = `${location.pathname}?type=${encodeURIComponent(type)}&query=${encodeURIComponent(query)}`;
      history.pushState(null, '', newUrl);

      executeSearch(type, query);
    });

    // ==========================================
    // 検索処理実行
    // ==========================================
    async function executeSearch(type, query) {
      const resultList = document.getElementById("searchResultList");
      const heading = document.getElementById("resultHeading");
      const messageBox = document.getElementById("messageBox");
      
      // 画面表示の初期化（前回の結果をクリア）
      resultList.innerHTML = "";
      messageBox.innerHTML = "";
      heading.classList.remove("d-none");

      const formData = new FormData();
      formData.append("csrf_token", csrfToken);

      let apiUrl = "";

      if (type === "user") {
        apiUrl = "/api/user/search.php";
        formData.append("query", query);
      } else if (type === "group") {
        apiUrl = "/api/group/search_groups.php";
        formData.append("keyword", query);
      } else if (type === "blog") {
        // 💡 ブログ一覧の仕様に合わせて、エンドポイント名と複製マッピング（search/tag_search）を構築
        apiUrl = "/api/blog/search_blogs.php";
        formData.append("search", query);
        formData.append("tag_search", query);
        formData.append("group_filter", "");
      }

      try {
        const response = await fetch(apiUrl, {
          method: "POST",
          body: formData
        });

        const result = await response.json();

        // ブログAPI（HTTP200でsuccess:falseを返す、または通信自体のエラー検知）の精査
        if (type !== "blog" && !result.success) {
          showMessage(result.message || "検索処理に失敗しました", "danger");
          return;
        }

        if (type === "user") {
          const users = Array.isArray(result.data) ? result.data : (result.data ? [result.data] : []);
          renderUserResult(users);
        } else if (type === "group") {
          renderGroupResult(result.data);
        } else if (type === "blog") {
          // 💡 ブログ一覧API固有のエラーハンドリングと、入れ子データ構造（result.message.blogs）の展開
          if (!result.success) {
            showMessage(result.message || "ブログの検索結果を取得できませんでした。", "danger");
            return;
          }
          const blogs = (result.message && result.message.blogs) ? result.message.blogs : [];
          renderBlogResult(blogs);
        }

      } catch (error) {
        console.error("Search Error:", error);
        showMessage("通信エラーが発生しました", "danger");
      }
    }

    // ==========================================
    // ユーザー検索結果の描画
    // ==========================================
    function renderUserResult(users) {
      const resultList = document.getElementById("searchResultList");

      if (!users || users.length === 0) {
        showMessage("該当するユーザーが見つかりませんでした", "info");
        return;
      }

      users.forEach(user => {
        const iconSrc = user.icon_url ? user.icon_url : 'libs/bootstrap-5.3.8-dist/img/default-icon.png';
        const displayName = escapeHtml(user.display_name || '名無しさん');
        const userId = escapeHtml(user.user_id);
        const intro = escapeHtml(user.introduction || '自己紹介文はまだありません。');

        let tagsHtml = '';
        if (user.tags) {
          let tagsArray = [];
          if (Array.isArray(user.tags)) {
            tagsArray = user.tags;
          } else if (typeof user.tags === 'string') {
            tagsArray = user.tags.split(',').map(t => t.trim());
          }

          tagsArray.forEach(tag => {
            if (tag) {
              tagsHtml += `<span class="badge bg-secondary text-white tag-badge">#${escapeHtml(tag)}</span>`;
            }
          });
        }

        const card = document.createElement('div');
        card.className = 'card border-0 shadow-sm rounded-4 result-card';
        card.innerHTML = `
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <img src="${iconSrc}" class="search-icon me-3" alt="${displayName}のアイコン" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'65\' height=\'65\'><rect width=\'65\' height=\'65\' fill=\'%23ccc\'/><text x=\'50%\' y=\'55%\' font-size=\'12\' text-anchor=\'middle\' fill=\'%23666\'>No Image</text></svg>';">
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h5 class="fw-bold mb-0">${displayName}</h5>
                    <small class="text-muted">@${userId}</small>
                  </div>
                  <a href="/profile/profile.php?user_id=${encodeURIComponent(user.user_id)}" class="btn btn-outline-success btn-sm rounded-pill px-3">プロフィール</a>
                </div>
                <p class="text-secondary small mt-2 mb-2 text-truncate" style="max-width: 600px;">${intro}</p>
                <div>${tagsHtml}</div>
              </div>
            </div>
          </div>
        `;
        resultList.appendChild(card);
      });
    }

    // ==========================================
    // グループ検索結果の描画
    // ==========================================
    function renderGroupResult(groups) {
      const resultList = document.getElementById("searchResultList");

      if (!groups || groups.length === 0) {
        showMessage("該当する公開グループが見つかりません", "info");
        return;
      }

      groups.forEach(group => {
        const iconSrc = group.group_icon && group.group_icon !== "" ? group.group_icon : "https://placehold.jp/80x80.png";
        const groupName = escapeHtml(group.group_name || '無名グループ');
        const latestMessage = escapeHtml(group.latest_message && group.latest_message !== "" ? group.latest_message : "まだメッセージはありません");
        const groupId = group.group_id;

        const card = document.createElement('div');
        card.className = 'card border-0 shadow-sm rounded-4 result-card';
        card.innerHTML = `
          <div class="card-body p-3">
            <div class="d-flex align-items-center">
              <img src="${iconSrc}" class="search-icon me-3" alt="${groupName}のアイコン" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'65\' height=\'65\'><rect width=\'65\' height=\'65\' fill=\'%23ccc\'/><text x=\'50%\' y=\'55%\' font-size=\'12\' text-anchor=\'middle\' fill=\'%23666\'>No Image</text></svg>';">
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                  <h5 class="fw-bold mb-0 text-truncate" style="max-width: 450px;">${groupName}</h5>
                  <a href="/group/chat.php?group_id=${encodeURIComponent(groupId)}" class="btn btn-outline-primary btn-sm rounded-pill px-3">チャットを開く</a>
                </div>
                <p class="text-muted small mt-2 mb-0 text-truncate" style="max-width: 600px;">最新メッセージ：${latestMessage}</p>
              </div>
            </div>
          </div>
        `;
        resultList.appendChild(card);
      });
    }

    // ==========================================
    // 💡 ブログ検索結果の描画（ブログ一覧の仕様に完全準拠）
    // ==========================================
    function renderBlogResult(blogs) {
      const resultList = document.getElementById("searchResultList");

      if (!blogs || blogs.length === 0) {
        showMessage("該当するブログが見つかりませんでした", "info");
        return;
      }

      blogs.forEach(blog => {
        const title = escapeHtml(blog.title || '無題のブログ');
        const blogId = blog.blog_id;

        // ブログ一覧の仕様に合わせたカンマ区切りタグのパース処理
        const tagsHtml = blog.tags ? blog.tags.split(",").map(tag => 
          `<span class="badge bg-success tag-badge">${escapeHtml(tag.trim())}</span>`
        ).join("") : "";

        const card = document.createElement('div');
        card.className = 'card border-0 shadow-sm rounded-4 result-card';
        card.innerHTML = `
          <div class="card-body p-4">
            <div class="d-flex align-items-center">
              <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h4 class="fw-bold mb-0 text-truncate" style="max-width: 500px;">${title}</h4>
                  <a href="/blog/blog_detail.php?blog_id=${encodeURIComponent(blogId)}" class="btn btn-outline-success btn-sm rounded-pill px-4">記事を読む</a>
                </div>
                <div class="card-tags">${tagsHtml}</div>
              </div>
            </div>
          </div>
        `;
        resultList.appendChild(card);
      });
    }

    // ==========================================
    // メッセージ表示ユーティリティ
    // ==========================================
    function showMessage(text, type = "info") {
      const messageBox = document.getElementById("messageBox");
      messageBox.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show rounded-3 shadow-sm" role="alert">
          ${escapeHtml(text)}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      `;
    }

    // ==========================================
    // XSS対策：HTMLエスケープ（型エラー対策強化版）
    // ==========================================
    function escapeHtml(str) {
      if (str === null || str === undefined) return '';
      const stringValue = String(str);
      return stringValue.replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
    }
  </script>
</body>
</html>