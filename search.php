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
  <meta name="description" content="ユーザーやグループを条件から検索できるページです。">
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

  <input type="hidden" id="csrf_token" value="<?= htmlspecialchars($csrfToken->getToken()) ?>">

  <?php require_once __DIR__ . '/component/header.php'; ?>

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
        apiUrl = "/api/group/search_group.php";
        formData.append("keyword", query);
      }

      try {
        const response = await fetch(apiUrl, {
          method: "POST",
          body: formData
        });

        const result = await response.json();

        if (!result.success) {
          showMessage(result.message || "検索処理に失敗しました", "danger");
          return;
        }

        // 💡 1件だけの時も自動遷移せず、必ず一覧表示用の関数（render）を呼び出します
        if (type === "user") {
          const users = Array.isArray(result.data) ? result.data : (result.data ? [result.data] : []);
          renderUserResult(users);
        } else if (type === "group") {
          renderGroupResult(result.data);
        }

      } catch (error) {
        console.error("Search Error:", error);
        showMessage("通信エラーが発生しました", "danger");
      }
    }

    // ==========================================
    // ユーザー検索結果の描画（常に一覧表示）
    // ==========================================
    function renderUserResult(users) {
      const resultList = document.getElementById("searchResultList");

      if (!