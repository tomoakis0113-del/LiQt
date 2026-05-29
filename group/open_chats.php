<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>オープンチャット一覧</title>

  <link rel="stylesheet" href="../libs/bootstrap-5.3.8-dist/css/bootstrap.min.css">
  <script src="../libs/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      background-color: #f5f7fb;
    }

    /* 💡 グループカード全体のデザイン調整 */
    .group-card {
      transition: 0.2s;
      margin-bottom: 1.5rem;
      cursor: pointer;
    }

    .group-card:hover {
      transform: translateY(-2px);
      background-color: #f8f9fa;
    }

    /* 💡 アイコンを少し大きめ・丸型に変更 */
    .group-icon {
      width: 65px;
      height: 65px;
      border-radius: 50%;
      object-fit: cover;
    }
  </style>
</head>

<body class="bg-light">

  <?php require_once __DIR__ . '/../component/header.php'; ?>

  <main class="container p-4" style="max-width:800px; padding-bottom: 120px;">

    <h3 class="fw-bold mb-4">オープンチャット</h3>

    <div id="alertBox" class="alert d-none"></div>

    <div id="groupList"></div>

  </main>

  <?php require_once __DIR__ . '/../component/footer.php'; ?>

  <script>
    // 初期ロード
    loadGroups();

    // 一覧取得（仮データ）
    function loadGroups() {

      const dummyGroups = [

        {
          group_id: 1,
          group_name: "Web開発コミュニティ",
          group_icon: "https://placehold.jp/100x100.png",
          latest_message: "Bootstrapでモック作成中！"
        },

        {
          group_id: 2,
          group_name: "Java勉強会",
          group_icon: "https://placehold.jp/100x100.png",
          latest_message: "今日は継承について勉強します"
        }

      ];

      renderGroups(dummyGroups);
    }

    // 描画
    function renderGroups(groups) {
      const list = document.getElementById("groupList");
      list.innerHTML = "";

      if (groups.length === 0) {
        list.innerHTML = `
          <div class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-body text-center text-muted py-5">
              グループが見つかりません
            </div>
          </div>
        `;
        return;
      }

      // 💡 ダッシュボードのカードデザインに統一
      groups.forEach(g => {
        list.innerHTML += `
          <div class="card border-0 shadow-sm rounded-4 group-card" onclick="joinGroup('${g.group_id}')">
            <div class="card-body">
              <div class="d-flex align-items-center">
                
                <img src="${g.group_icon}" class="group-icon me-3">

                <div class="flex-grow-1">
                  <h5 class="fw-bold mb-2">${g.group_name}</h5>
                  <p class="text-muted mb-0 text-truncate">
                    最新メッセージ：${g.latest_message || "メッセージなし"}
                  </p>
                </div>

              </div>
            </div>
          </div>
        `;
      });
    }

    // 参加処理
    async function joinGroup(groupId) {
      if (!confirm("このグループに参加しますか？")) return;

      try {
        const res = await fetch("api/join_group.php", {
          method: "POST",
          body: new URLSearchParams({
            group_id: groupId
          })
        });

        const data = await res.json();

        if (data.success) {
          location.href = `chat.php?group_id=${groupId}`;
        } else {
          showError(data.message);
        }

      } catch (e) {
        showError("通信エラー");
      }
    }

    // エラー表示
    function showError(msg) {
      const box = document.getElementById("alertBox");
      box.textContent = msg;
      box.className = "alert alert-danger mb-4";
    }
  </script>

</body>

</html>