<?php
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * ブログ検索API
 * 必要なパラメータ:
 * - csrf_token
 * - tag_search
 * - group_filter
 * 
 * レスポンス:
 * - 成功: { "success": true, "message": "検索結果を取得しました" }
 *{
 *  "success": true,
 *  "message": {
 *    "blogs": [
 *      {
 *        "blog_id": ブログID,
 *        "title": "ブログタイトル",
 *        "tags": "タグID"
 *      }
 *    ]
 *  },
 *  "data": "検索結果を取得しました"
 *}
 *
 *  - 失敗: { "success": false, "message": "検索結果を取得できません" }
 */

try{
    //POST以外を拒否
    if($_SERVER['REQUEST_METHOD'] !== 'POST'){
        lib\Util::responseError(405,'許可されていないリクエストです');
    }

    //セッション管理
    $sessionHandler = new lib\Session();

    //サインイン確認
    if(!$sessionHandler->isSignedIn()){
        lib\Util::responseError(401,'サインインが必要です');
    }

    //パラメータを受け取り
    $sentToken   = $_POST['csrf_token'] ?? '';  
    $search      = trim($_POST['search'] ?? '');
    $groupFilter = trim($_POST['group_filter'] ?? '');
    //CSRFチェック
    $csrfToken = new lib\CSRFToken();

    if(!$csrfToken->isValid($sentToken)){
        lib\Util::responseError(400,'不正なリクエストです');
    }

    //バリデーション
    if($search === '' && $groupFilter === ''){
    lib\Util::responseError(400,'検索条件を指定してください');
    }


    if($groupFilter !== '' && !ctype_digit($groupFilter)){
        lib\Util::responseError(400,'グループIDの形式が正しくありません');
    }

    //ログイン中ユーザーID取得
    $currentUserId = $sessionHandler->getCurrentUserID();

    //DB検索準備
    $query = models\Blog::query();

    //検索
    if($search !== ''){
        $query->where(function($q) use ($search){
            $q->where('title', 'LIKE', '%' . $search . '%')
            ->orWhere('tags', 'LIKE', '%' . $search . '%');

            // 数字ならブログIDとしても検索
            if(ctype_digit($search)){
                $q->orWhere('id', (int)$search);
            }
        });
    }
    //グループ絞り込み
    if($groupFilter !== ''){
        $groupId = (int)$groupFilter;

        //そのグループに所属しているか確認
        $member = models\GroupMember::query()
            ->where('user_id', $currentUserId)
            ->where('group_id', $groupId)
            ->first(['group_id']);

        if(!$member){
            lib\Util::responseError(403,'このグループのブログは検索できません');
        }

        $query->where('group_id', $groupId);
    }

    //閲覧できるブログだけ取得
    $query->where(function($q) use ($currentUserId){
        $q->where('visibility', 'public')
          ->orWhere(function($q2) use ($currentUserId){
              $q2->where('visibility', 'private')
                 ->where('author_id', $currentUserId);
          })
          ->orWhere(function($q3) use ($currentUserId){
              $q3->where('visibility', 'group')
                 ->whereIn('group_id', function($sub) use ($currentUserId){
                     $sub->select('group_id')
                         ->from('group_members')
                         ->where('user_id', $currentUserId);
                 });
          });
    });

    //ブログ取得
    $blogs = $query
        ->orderBy('created_at', 'desc')
        ->get(['id', 'title', 'tags']);

    //レスポンス用に整形
    $blogList = [];

    foreach($blogs as $blog){
        $blogList[] = [
            'blog_id' => $blog->id,
            'title'   => $blog->title,
            'tags'    => $blog->tags ?? ''
        ];
    }

    $data = [
        'blogs' => $blogList
    ];

    lib\Util::responseSuccess('検索結果を取得しました',$data);

}catch(\Throwable $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500,'サーバーエラーが発生しました');
}
?>