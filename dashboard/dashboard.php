<?php
session_start();

// 🔐 認証チェック
require_once __DIR__ . '/../component/auth_check.php';

// モックAPIレスポンス（本来は api/get_dashboard.php をfetch）
$response = [
    'success' => true,
    'message' => '',
    'data' => [
        'joined_groups' => [
            [
                'group_id' => 1,
                'group_name' => '開発チーム',
                'group_icon' => '👨‍💻',
                'latest_message' => 'APIの仕様どうする？'
            ],
            [
                'group_id' => 2,
                'group_name' => '雑談部屋',
                'group_icon' => '💬',
                'latest_message' => '今日ラーメン行く？'
            ],
            [
                'group_id' => 3,
                'group_name' => 'ゲーム仲間',
                'group_icon' => '🎮',
                'latest_message' => '昨日の試合やばかった'
            ]
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ダッシュボード</title>

  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
</head>

<!-- ✅ フッター固定 -->
<body class="d-flex flex-column min-vh-100">

<!-- ヘッダー -->
<?php require_once __DIR__ . '/../component/header.php'; ?>

<!-- メイン -->
<main class="container p-4 flex-grow-1">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold">ダッシュボード</h2>

    <!-- グループ作成 -->
    <a href="/create_group.php" class="btn btn-success">
      ＋ グループ作成
    </a>
  </div>

  <?php if (!$response['success']): ?>
    <!-- エラー -->
    <div class="alert alert-danger">
      <?php echo htmlspecialchars($response['message']); ?>
    </div>
  <?php else: ?>

    <div class="row g-3">

      <?php foreach ($response['data']['joined_groups'] as $group): ?>

        <div class="col-md-6 col-lg-4">

          <!-- グループカード -->
          <a href="/chat.php?group_id=<?php echo $group['group_id']; ?>" 
             class="text-decoration-none text-dark">

            <div class="card shadow-sm h-100 hover-shadow">

              <div class="card-body d-flex align-items-center">

                <!-- アイコン -->
                <div class="fs-2 me-3">
                  <?php echo htmlspecialchars($group['group_icon']); ?>
                </div>

                <!-- 情報 -->
                <div>
                  <h5 class="mb-1">
                    <?php echo htmlspecialchars($group['group_name']); ?>
                  </h5>

                  <small class="text-muted">
                    <?php echo htmlspecialchars($group['latest_message']); ?>
                  </small>
                </div>

              </div>

            </div>

          </a>

        </div>

      <?php endforeach; ?>

    </div>

  <?php endif; ?>

</main>

<!-- フッター -->
<?php require_once __DIR__ . '/../component/footer.php'; ?>

</body>
</html>