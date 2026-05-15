<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>プロフィール | LiQt</title>
  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
  <style>
    .avatar-preview { width: 100px; height: 100px; object-fit: cover; }
  </style>
</head>

<body>
  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container py-4" style="padding-bottom: 120px; max-width: 900px;">

    <div class="card shadow-sm border-0 rounded-4 mb-4">
      <div class="card-body text-center p-4">
        <div class="mb-3 position-relative">
          <span class="material-symbols-outlined bg-success text-white rounded-circle p-3" style="font-size: 70px;">
            person
          </span>
        </div>

        <h2 id="display-name" class="fw-bold mb-1">山田 太郎</h2>
        <p id="user-id-text" class="text-muted mb-3">@taro_yamada</p>
        <p id="introduction-text" class="mb-4">
          Webエンジニアを目指して勉強中です。<br>
          Java・PHP・Bootstrap を勉強しています！
        </p>

        <div class="d-flex justify-content-center gap-2">
          <button class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#editModal">編集</button>
          <button class="btn btn-outline-primary" onclick="handleChatStart('taro_yamada')">チャット</button>
          <button class="btn btn-outline-danger" onclick="handleBlockUser('taro_yamada')">ブロック</button>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-5">
      <div class="card-header bg-white border-0 pt-4 pb-0">
        <h4 class="fw-bold">投稿一覧</h4>
      </div>
      <div class="card-body">
        <div class="border rounded-4 p-3 mb-3">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">Bootstrapでプロフィールページを作ってみた</h5>
            <small class="text-muted">2026/05/14</small>
          </div>
          <p class="text-muted mb-2">Bootstrapを使ってレスポンシブ対応のプロフィールページを作成しました。</p>
          <a href="#" class="text-success text-decoration-none fw-bold">詳細を見る</a>
        </div>
      </div>
    </div>
  </main>

  <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 rounded-4 shadow">
        <form id="editProfileForm">
          <div class="modal-header">
            <h5 class="modal-title fw-bold">プロフィール編集</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="csrf_token" value="dummy_token">
            
            <div class="mb-3">
              <label class="form-label small fw-bold">アイコン</label>
              <input type="file" name="icon" class="form-control" accept="image/*">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">表示名</label>
              <input type="text" name="display_name" class="form-control" value="山田 太郎" required>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">自己紹介</label>
              <textarea name="introduction" class="form-control" rows="4">Webエンジニアを目指して勉強中です。</textarea>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-bold">タグ（カンマ区切り）</label>
              <input type="text" name="tags" class="form-control" placeholder="Java,PHP,Bootstrap">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">キャンセル</button>
            <button type="submit" class="btn btn-success px-4">保存する</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    /**
     * プロフィール更新処理
     */
    document.getElementById('editProfileForm').addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
      
      try {
        const response = await fetch('../api/update_profile.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
          alert('プロフィールを更新しました！');
          location.reload(); // 反映のためリロード
        } else {
          alert('エラー: ' + result.message);
        }
      } catch (error) {
        console.error('Error:', error);
        alert('通信に失敗しました。');
      }
    });

    /**
     * チャット開始処理
     */
    async function handleChatStart(targetId) {
      const formData = new FormData();
      formData.append('csrf_token', 'dummy_token');
      formData.append('user_id', 'ABCDEF12'); // 本来は動的な8文字ID

      try {
        const response = await fetch('../api/start_chat.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
          window.location.href = result.data.chat_link;
        } else {
          alert('チャットを開始できません: ' + result.message);
        }
      } catch (error) {
        alert('エラーが発生しました。');
      }
    }

    /**
     * ブロック処理
     */
    async function handleBlockUser(targetId) {
      if (!confirm('このユーザーをブロックしますか？')) return;

      const formData = new FormData();
      formData.append('csrf_token', 'dummy_token');
      formData.append('user_id', 'ABCDEF12'); // 本来は動的な8文字ID

      try {
        const response = await fetch('../api/block_user.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();

        alert(result.message);
      } catch (error) {
        alert('エラーが発生しました。');
      }
    }
  </script>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>
</body>
</html>