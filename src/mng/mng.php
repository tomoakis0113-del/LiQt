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
    <title>運営管理</title>

    <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../custom/custom-theme.css">

    <style>
        .mng-card {
            transition: 0.2s;
            cursor: pointer;
            height: 100%;
        }

        .mng-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .mng-count {
            font-size: 2.2rem;
            font-weight: bold;
        }

        .mng-icon {
            font-size: 2rem;
        }

        .loading-area {
            display: none;
        }

        .loading-area.active {
            display: block;
        }

        body {
    height: auto;
    min-height: 100%;
    overflow-y: auto !important;
}

main {
    min-height: 100vh;
    padding-bottom: 80px;
}

.user-table-area {
    max-height: 65vh;
    overflow-y: auto;
    overflow-x: auto;
}

.user-table-area thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background-color: #f8f9fa;
}
    </style>
</head>

<body>

<?php require_once __DIR__ . '/../component/header.php'; ?>

<main class="container py-4">

    <input
        type="hidden"
        id="csrf_token"
        value="<?php echo htmlspecialchars($csrfToken->getToken(), ENT_QUOTES, 'UTF-8'); ?>">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                運営管理
            </h2>

            <p class="text-muted mb-0">
                LiQt全体の状態を確認できます。
            </p>
        </div>

        <button
            type="button"
            class="btn btn-primary"
            onclick="loadMngDashboard()">

            最新情報を取得

        </button>

    </div>

    <div id="errorMessage" class="alert alert-danger d-none"></div>

    <div id="successMessage" class="alert alert-success d-none"></div>

    <div id="loadingArea" class="loading-area text-center my-4">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 mb-0">
            読み込み中...
        </p>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-md-3">
            <div class="card shadow-sm text-center mng-card" onclick="location.href='users_mng.php'">
                <div class="card-body">
                    <div class="mng-icon mb-2">👤</div>
                    <h6 class="text-muted">ユーザー総数</h6>
                    <div id="userCount" class="mng-count">-</div>
                    <small class="text-muted">ユーザー管理へ</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm text-center mng-card" onclick="location.href='blogs_mng.php'">
                <div class="card-body">
                    <div class="mng-icon mb-2">📝</div>
                    <h6 class="text-muted">ブログ総数</h6>
                    <div id="blogCount" class="mng-count">-</div>
                    <small class="text-muted">ブログ管理へ</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm text-center mng-card" onclick="location.href='groups_mng.php'">
                <div class="card-body">
                    <div class="mng-icon mb-2">💬</div>
                    <h6 class="text-muted">グループ総数</h6>
                    <div id="groupCount" class="mng-count">-</div>
                    <small class="text-muted">グループ管理へ</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm text-center mng-card">
                <div class="card-body">
                    <div class="mng-icon mb-2">🚫</div>
                    <h6 class="text-muted">ブロック総数</h6>
                    <div id="blockCount" class="mng-count">-</div>
                    <small class="text-muted">ブロック状況</small>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">
            管理メニュー
        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-4">
                    <a href="users_mng.php" class="btn btn-primary w-100">
                        ユーザー管理ページ
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="blogs_mng.php" class="btn btn-outline-primary w-100">
                        ブログ管理ページ
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="groups_mng.php" class="btn btn-outline-primary w-100">
                        グループ管理ページ
                    </a>
                </div>

            </div>

        </div>
    </div>

    <div class="text-end text-muted">
        最終更新：
        <span id="lastUpdated">未取得</span>
    </div>

</main>

<?php require_once __DIR__ . '/../component/footer.php'; ?>

<script>
    const csrfToken = document.getElementById('csrf_token').value;

    document.addEventListener('DOMContentLoaded', function () {
        loadMngDashboard();
    });

    async function loadMngDashboard() {

        hideMessage();
        showLoading();

        const result = await postApi(
            '../api/mng/get_mng_dashboard.php',
            {
                csrf_token: csrfToken
            }
        );

        hideLoading();

        if (!result) {
            showError('APIから正しいレスポンスが返ってきませんでした');
            return;
        }

        if (!result.success) {
            showError(result.message);
            return;
        }

        setCount('userCount', result.data.user_count);
        setCount('blogCount', result.data.blog_count);
        setCount('groupCount', result.data.group_count);
        setCount('blockCount', result.data.block_count);

        document.getElementById('lastUpdated').textContent =
            new Date().toLocaleString('ja-JP');

        showSuccess('運営管理情報を更新しました');
    }

    async function postApi(url, params) {

        const formData = new FormData();

        for (const key in params) {
            formData.append(key, params[key]);
        }

        try {

            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            const text = await response.text();

            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('JSONではないレスポンス:', text);
                return {
                    success: false,
                    message: 'JSONではないレスポンスが返ってきました。APIを確認してください。'
                };
            }

        } catch (error) {

            console.error(error);

            return {
                success: false,
                message: '通信エラーが発生しました'
            };

        }
    }

    function setCount(elementId, value) {

        const target = document.getElementById(elementId);

        if (!target) {
            return;
        }

        target.textContent = Number(value).toLocaleString('ja-JP');

    }

    function showLoading() {
        document.getElementById('loadingArea').classList.add('active');
    }

    function hideLoading() {
        document.getElementById('loadingArea').classList.remove('active');
    }

    function showError(message) {

        const errorMessage = document.getElementById('errorMessage');

        errorMessage.textContent = message;
        errorMessage.classList.remove('d-none');

    }

    function showSuccess(message) {

        const successMessage = document.getElementById('successMessage');

        successMessage.textContent = message;
        successMessage.classList.remove('d-none');

        setTimeout(function () {
            successMessage.classList.add('d-none');
        }, 2500);

    }

    function hideMessage() {

        document.getElementById('errorMessage').classList.add('d-none');
        document.getElementById('successMessage').classList.add('d-none');

    }
</script>

</body>

</html>