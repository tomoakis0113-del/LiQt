<?php
require_once __DIR__ . '/../component/auth_check.php';
require_once __DIR__ . '/../vendor/autoload.php';
$csrfToken = new lib\CSRFToken();
?>
<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>ブログ作成</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/markdown-it/dist/markdown-it.min.js"></script>

  <style>
    .preview-box {
      border: 1px dashed #ccc;
      padding: 10px;
      background: #fff;
      min-height: 150px;
    }
  </style>
</head>

<body>

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container p-4" style="max-width:900px; padding-bottom: 120px !important;">

    <h3 class="mb-4">ブログ作成</h3>

    <div id="alertBox" class="alert d-none"></div>

    <form id="blogForm">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken->getToken(), ENT_QUOTES, 'UTF-8'); ?>">

      <div class="mb-3">
        <label class="form-label">タイトル</label>
        <input type="text" name="title" class="form-control" placeholder="ブログのタイトルを入力してください" required>
      </div>

      <div class="mb-3">
        <label class="form-label">本文（Markdown）</label>
        <textarea name="content" id="contentInput" class="form-control" rows="8" placeholder="Markdown形式で本文を入力してください" required></textarea>
        <input type="hidden" name="blog_content" id="blogContentHidden">
      </div>

      <div class="mb-3">
        <label class="form-label">プレビュー</label>
        <div id="preview" class="preview-box"></div>
      </div>

      <div class="mb-3">
        <label class="form-label">公開設定</label>
        <select name="visibility" id="visibility" class="form-select" required>
          <option value="public">公開</option>
          <option value="private">非公開</option>
          <option value="group">グループ</option>
        </select>
      </div>

      <div class="mb-3 d-none" id="groupSelectBox">
        <label class="form-label">グループ選択</label>
        <select name="group_id" id="groupSelect" class="form-select"></select>
      </div>

      <div class="mb-3">
        <label class="form-label">タグ（カンマ区切り）</label>
        <input type="text" name="tags" class="form-control" placeholder="Java,PHP,HTML">
      </div>

      <button type="submit" id="submitBtn" class="btn btn-primary w-100 mb-5">
        <span id="submitSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
        投稿する
      </button>

    </form>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    const md = window.markdownit();
    const contentInput = document.getElementById("contentInput");
    const blogContentHidden = document.getElementById("blogContentHidden");
    const visibilitySelect = document.getElementById("visibility");
    const groupSelectBox = document.getElementById("groupSelectBox");
    const groupSelect = document.getElementById("groupSelect");
    const alertBox = document.getElementById("alertBox");
    const submitBtn = document.getElementById("submitBtn");
    const submitSpinner = document.getElementById("submitSpinner");

    // 1. Markdownプレビュー & blog_contentへの同期
    contentInput.addEventListener("input", function() {
      document.getElementById("preview").innerHTML = md.render(this.value);
      blogContentHidden.value = this.value;
    });

    // 2. 公開設定切替時のグループ表示制御
    visibilitySelect.addEventListener("change", function() {
      if (this.value === "group") {
        groupSelectBox.classList.remove("d-none");
        groupSelect.setAttribute("required", "required");
        loadGroups();
      } else {
        groupSelectBox.classList.add("d-none");
        groupSelect.removeAttribute("required");
        groupSelect.value = ""; 
      }
    });

    // 3. 所属グループの非同期読み込み
    function loadGroups() {
      const formData = new FormData();
      formData.append("csrf_token", "<?php echo htmlspecialchars($csrfToken->getToken(), ENT_QUOTES, 'UTF-8'); ?>");
      fetch("/api/group/search_groups.php", {
        method: "POST",
        body: formData
      })
        .then(res => {
          if (!res.ok) throw new Error("グループ一覧の取得に失敗しました。");
          return res.json();
        })
        .then(data => {
          if (!data.success) {
            showError(data.message);
            return;
          }

          groupSelect.innerHTML = '<option value="">選択してください</option>';
          data.data.forEach(g => {
            const option = document.createElement("option");
            option.value = g.group_id;
            option.textContent = g.group_name;
            groupSelect.appendChild(option);
          });
        })
        .catch(err => {
          showError(err.message);
        });
    }

    // 4. ブログ投稿処理 (APIの統合)
    document.getElementById("blogForm").addEventListener("submit", async (e) => {
      e.preventDefault();
      clearError();

      // textareaの最終内容を隠しフィールドへ格納
      blogContentHidden.value = contentInput.value;

      // 送信中のUIロック
      setSubmitting(true);

      const formData = new FormData(e.target);

      try {
        // 仕様書通りのエンドポイントへPOST送信
        const res = await fetch("/api/blog/create_blog.php", {
          method: "POST",
          body: formData
        });
        const data = await res.json();

        if (data.success) {
          // 成功時：レスポンス構造 (data.data.blog_id) からIDを抽出し遷移
          window.location.href = `blog_detail.php?blog_id=${data.data.blog_id}`;
        } else {
          // エラー時：APIから返却されたメッセージを表示
          showError(data.message);
          setSubmitting(false);
        }
      } catch (err) {
        console.error(err);
        showError("通信エラーまたは予期せぬエラーが発生しました。");
        setSubmitting(false);
      }
    });

    // 5. ユーティリティ関数（エラー表示・ローディング制御）
    function showError(msg) {
      alertBox.textContent = msg;
      alertBox.className = "alert alert-danger mb-4";
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function clearError() {
      alertBox.textContent = "";
      alertBox.className = "alert d-none";
    }

    function setSubmitting(isSubmitting) {
      if (isSubmitting) {
        submitBtn.setAttribute("disabled", "disabled");
        submitSpinner.classList.remove("d-none");
      } else {
        submitBtn.removeAttribute("disabled");
        submitSpinner.classList.add("d-none");
      }
    }
  </script>

</body>

</html>