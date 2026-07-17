<?php require_once __DIR__ . '/../component/auth_check.php'; ?>
<!DOCTYPE html>

<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="LiQt ユーザー管理">
  <meta name="keywords" content="LiQt,SNS,管理者">
  <meta name="author" content="乙成,島田,勝原">

  <title>ユーザー管理</title>

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../custom/custom-theme.css">
</head>

<body>

<?php require_once __DIR__ . '/../component/header.php'; ?>

<main class="container py-4">

    <h2 class="fw-bold mb-4">
        ユーザー管理
    </h2>

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
                        <option>すべて</option>
                        <option>有効ユーザー</option>
                        <option>停止中ユーザー</option>
                        <option>運営管理者</option>
                    </select>
                </div>

                <div class="col-md-3 d-grid align-items-end">
                    <button class="btn btn-primary mt-4">
                        検索
                    </button>
                </div>

            </div>

        </div>
    </div>

    <!-- 一覧 -->
    <div class="card shadow-sm">

        <div class="card-header fw-bold">
            ユーザー一覧
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

                <tbody>

                <tr>

                    <td>
                        <img
                                src="../images/default_icon.png"
                                width="45"
                                class="rounded-circle">
                    </td>

                    <td>tanaka</td>

                    <td>田中 太郎</td>

                    <td>tanaka@example.com</td>

                    <td>
                        <span class="badge bg-success">
                            有効
                        </span>
                    </td>

                    <td>

                        <span class="badge bg-warning text-dark">
                            管理者
                        </span>

                    </td>

                    <td>12</td>

                    <td>4</td>

                    <td>2</td>

                    <td>

                        <button
                                class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#detailModal">

                            詳細

                        </button>

                        <button
                                class="btn btn-sm btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal">

                            停止

                        </button>

                    </td>

                </tr>

                <tr>

                    <td>
                        <img
                                src="../images/default_icon.png"
                                width="45"
                                class="rounded-circle">
                    </td>

                    <td>suzuki</td>

                    <td>鈴木 花子</td>

                    <td>suzuki@example.com</td>

                    <td>

                        <span class="badge bg-danger">

                            停止

                        </span>

                    </td>

                    <td>-</td>

                    <td>5</td>

                    <td>2</td>

                    <td>0</td>

                    <td>

                        <button
                                class="btn btn-sm btn-primary"
                                data-bs-toggle="modal"
                                data-bs-target="#detailModal">

                            詳細

                        </button>

                        <button
                                class="btn btn-sm btn-danger">

                            停止

                        </button>

                    </td>

                </tr>

                </tbody>

            </table>

        </div>

    </div>

</main>

<!-- 詳細モーダル -->
<div class="modal fade" id="detailModal">

    <div class="modal-dialog modal-xl">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    ユーザー詳細

                </h5>

                <button class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                <div class="row">

                    <div class="col-md-3 text-center">

                        <img src="../images/default_icon.png"
                             class="rounded-circle mb-3"
                             width="120">

                    </div>

                    <div class="col-md-9">

                        <table class="table">

                            <tr>
                                <th width="180">ユーザーID</th>
                                <td>tanaka</td>
                            </tr>

                            <tr>
                                <th>表示名</th>
                                <td>田中 太郎</td>
                            </tr>

                            <tr>
                                <th>メール</th>
                                <td>tanaka@example.com</td>
                            </tr>

                            <tr>
                                <th>自己紹介</th>
                                <td>よろしくお願いします。</td>
                            </tr>

                            <tr>
                                <th>タグ</th>
                                <td>

                                    <span class="badge bg-secondary">PHP</span>

                                    <span class="badge bg-secondary">Bootstrap</span>

                                    <span class="badge bg-secondary">Java</span>

                                </td>
                            </tr>

                        </table>

                    </div>

                </div>

                <hr>

                <ul class="nav nav-tabs">

                    <li class="nav-item">
                        <button
                                class="nav-link active"
                                data-bs-toggle="tab"
                                data-bs-target="#blogTab">

                            ブログ

                        </button>
                    </li>

                    <li class="nav-item">
                        <button
                                class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#groupTab">

                            グループ

                        </button>
                    </li>

                    <li class="nav-item">
                        <button
                                class="nav-link"
                                data-bs-toggle="tab"
                                data-bs-target="#blockTab">

                            ブロック

                        </button>
                    </li>

                </ul>

                <div class="tab-content border border-top-0 p-3">

                    <div class="tab-pane fade show active" id="blogTab">

                        <table class="table">

                            <tr>

                                <th>タイトル</th>

                                <th>公開</th>

                                <th>作成日</th>

                                <th></th>

                            </tr>

                            <tr>

                                <td>PHP入門</td>

                                <td>公開</td>

                                <td>2026/7/14</td>

                                <td>

                                    <button class="btn btn-danger btn-sm">

                                        削除

                                    </button>

                                </td>

                            </tr>

                        </table>

                    </div>

                    <div class="tab-pane fade" id="groupTab">

                        <table class="table">

                            <tr>

                                <th>グループ名</th>

                                <th>権限</th>

                                <th></th>

                            </tr>

                            <tr>

                                <td>LiQt開発班</td>

                                <td>オーナー</td>

                                <td>

                                    <button class="btn btn-danger btn-sm">

                                        削除

                                    </button>

                                </td>

                            </tr>

                        </table>

                    </div>

                    <div class="tab-pane fade" id="blockTab">

                        <div class="row">

                            <div class="col">

                                <h6>

                                    ブロックしている

                                </h6>

                                <ul class="list-group">

                                    <li class="list-group-item">

                                        yamada

                                    </li>

                                </ul>

                            </div>

                            <div class="col">

                                <h6>

                                    ブロックされている

                                </h6>

                                <ul class="list-group">

                                    <li class="list-group-item">

                                        suzuki

                                    </li>

                                </ul>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- 停止確認 -->
<div class="modal fade" id="deleteModal">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title">

                    ユーザー停止

                </h5>

                <button class="btn-close" data-bs-dismiss="modal"></button>

            </div>

            <div class="modal-body">

                このユーザーを停止しますか？

            </div>

            <div class="modal-footer">

                <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                    キャンセル

                </button>

                <button class="btn btn-danger">

                    停止する

                </button>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/../component/footer.php'; ?>

</body>

</html>