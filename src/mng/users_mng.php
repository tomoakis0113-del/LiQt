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
    <title>ユーザー管理</title>

    <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../custom/custom-theme.css">

    <style>
        .user-icon {
            width: 45px;
            height: 45px;
            object-fit: cover;
        }

        .loading-area {
            display: none;
        }

        .loading-area.active {
            display: block;
        }

        html,
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
                ユーザー管理
            </h2>

            <p class="text-muted mb-0">
                ユーザーの検索、状態確認、投稿数、所属グループ数、ブロック数を確認できます。
            </p>
        </div>

        <a href="mng.php" class="btn btn-outline-secondary">
            運営管理トップへ戻る
        </a>
    </div>

    <div id="errorMessage" class="alert alert-danger d-none"></div>

    <div id="successMessage" class="alert alert-success d-none"></div>

    <!-- 検索 -->
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-bold">
            ユーザー検索
        </div>

        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">
                        ユーザー検索
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        placeholder="ユーザーID・表示名・メールアドレス"
                        id="user_search">
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        状態
                    </label>

                    <select class="form-select" id="status_filter">
                        <option value="すべて">すべて</option>
                        <option value="有効ユーザー">有効ユーザー</option>
                        <option value="停止中ユーザー">停止中ユーザー</option>
                        <option value="運営管理者">運営管理者</option>
                    </select>
                </div>

                <div class="col-md-3 d-grid align-items-end">
                    <button
                        type="button"
                        class="btn btn-primary mt-4"
                        id="searchButton">
                        検索
                    </button>
                </div>

            </div>

        </div>
    </div>

    <div id="loadingArea" class="loading-area text-center my-4">
        <div class="spinner-border text-primary" role="status"></div>
        <p class="mt-2 mb-0">
            読み込み中...
        </p>
    </div>

    <!-- 一覧 -->
    <div class="card shadow-sm">

        <div class="card-header fw-bold d-flex justify-content-between align-items-center">
            <span>ユーザー一覧</span>
            <span class="badge bg-primary" id="userTotalCount">0件</span>
        </div>

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">
                    <tr>
                        <th>アイコン</th>
                        <th>ユーザーID</th>
                        <th>表示名</th>
                        <th>メール</th>
                        <th>状態</th>
                        <th>管理者</th>
                        <th>ブログ</th>
                        <th>グループ</th>
                        <th>ブロック</th>
                        <th>操作</th>
                    </tr>
                </thead>

                <tbody id="usersTableBody">
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            読み込み中...
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>

</main>

<?php require_once __DIR__ . '/../component/footer.php'; ?>

<script>
    const csrfToken = document.getElementById('csrf_token').value;

    document.addEventListener('DOMContentLoaded', function () {
        loadUsers();
    });

    document.getElementById('searchButton').addEventListener('click', function () {
        loadUsers();
    });

    document.getElementById('user_search').addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            loadUsers();
        }
    });

    async function loadUsers() {

        hideMessage();
        showLoading();

        const userSearch =
            document.getElementById('user_search').value;

        const statusFilter =
            document.getElementById('status_filter').value;

        const result = await postApi(
            '../api/mng/get_users.php',
            {
                csrf_token: csrfToken,
                user_search: userSearch,
                status_filter: statusFilter
            }
        );

        hideLoading();

        if (!result) {
            showError('APIから正しいレスポンスが返ってきませんでした');
            return;
        }

        if (!result.success) {
            showError(result.message);
            renderEmptyUsers();
            return;
        }

        const users = result.data.users || [];

        renderUsers(users);

        showSuccess('ユーザー一覧を取得しました');
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

    function renderUsers(users) {

        const tbody =
            document.getElementById('usersTableBody');

        const totalCount =
            document.getElementById('userTotalCount');

        tbody.innerHTML = '';

        totalCount.textContent =
            users.length + '件';

        if (users.length === 0) {
            renderEmptyUsers();
            return;
        }

        users.forEach(function (user) {

            const iconUrl =
                user.icon_url && user.icon_url !== ''
                    ? user.icon_url
                    : '../images/default_icon.png';

            const activeBadge =
                Number(user.is_active) === 1
                    ? '<span class="badge bg-success">有効</span>'
                    : '<span class="badge bg-danger">停止</span>';

            const adminBadge =
                user.is_admin
                    ? '<span class="badge bg-warning text-dark">管理者</span>'
                    : '<span class="text-muted">-</span>';

            const tr =
                document.createElement('tr');

            tr.innerHTML = `
                <td>
                    <img
                        src="${escapeHtml(iconUrl)}"
                        class="rounded-circle user-icon"
                        alt="icon">
                </td>

                <td>${escapeHtml(user.user_id)}</td>

                <td>${escapeHtml(user.display_name)}</td>

                <td>${escapeHtml(user.mail_address)}</td>

                <td>${activeBadge}</td>

                <td>${adminBadge}</td>

                <td>${Number(user.blog_count).toLocaleString('ja-JP')}</td>

                <td>${Number(user.group_count).toLocaleString('ja-JP')}</td>

                <td>${Number(user.block_count).toLocaleString('ja-JP')}</td>

                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary"
                        onclick="openUserDetail('${escapeJs(user.user_id)}')">
                        詳細
                    </button>

                    <button
                        type="button"
                        class="btn btn-sm btn-danger"
                        onclick="openDeleteUser('${escapeJs(user.user_id)}')">
                        停止
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
        });
    }

    function renderEmptyUsers() {

        const tbody =
            document.getElementById('usersTableBody');

        const totalCount =
            document.getElementById('userTotalCount');

        totalCount.textContent = '0件';

        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center text-muted py-4">
                    ユーザーが見つかりません
                </td>
            </tr>
        `;
    }

    function openUserDetail(userId) {
        alert('次に get_user_detail.php と結合します。対象: ' + userId);
    }

    function openDeleteUser(userId) {
        alert('次に delete_user.php と結合します。対象: ' + userId);
    }

    function showLoading() {
        document.getElementById('loadingArea').classList.add('active');
    }

    function hideLoading() {
        document.getElementById('loadingArea').classList.remove('active');
    }

    function showError(message) {
        const errorMessage =
            document.getElementById('errorMessage');

        errorMessage.textContent = message;
        errorMessage.classList.remove('d-none');
    }

    function showSuccess(message) {
        const successMessage =
            document.getElementById('successMessage');

        successMessage.textContent = message;
        successMessage.classList.remove('d-none');

        setTimeout(function () {
            successMessage.classList.add('d-none');
        }, 2000);
    }

    function hideMessage() {
        document.getElementById('errorMessage').classList.add('d-none');
        document.getElementById('successMessage').classList.add('d-none');
    }

    function escapeHtml(value) {

        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function escapeJs(value) {

        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replaceAll('\\', '\\\\')
            .replaceAll("'", "\\'");
    }
</script>

</body>

</html>