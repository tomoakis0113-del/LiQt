<?php
require_once __DIR__.'/../../vendor/autoload.php';

/**
 * グループ作成API
 * 必要なパラメータ:
 * - group_id: グループID
 * 
 * レスポンス:
 * - 成功: 
 * {
 *  "success": true,
 *  "data": {
 *      "group_name": "グループ名",
 *      "group_icon": "グループアイコンURL",
 *      "group_blogs": [
 *          {
 *              "blog_id": ブログID,
 *              "title": "ブログタイトル",
 *              "content": "ブログ内容",
 *              "tags": "タグ1,タグ2,タグ3",
 *              "created_at": "作成日時"
 *          },
 *          ...
 *      ],
 *      "messages": [
 *          {
 *              "message_id": メッセージID,
 *              "sender_user_id": 送信者ユーザーID,
 *              "sender_display_name": 送信者表示名,
 *              "sender_icon": 送信者アイコンURL,
 *              "content": "メッセージ内容",
 *              "image_url": "メッセージ画像URL",
 *              "created_at": "作成日時"
 *          },
 *          ...
 *      ]
 *  }
 * }
 * - エラー: { "success": false, "message": "エラーメッセージ" }
 */
try{
    // データ受け取り
    $group_id     = $_POST['group_id'] ?? null;

    // バリデーション
    if(!$group_id){
        lib\Util::responseError(400,'グループIDが必要です');
    }

    // グループが存在するかどうかチェック
    $group = models\Group::where('id', $group_id)->first(['id', 'name', 'group_icon_url', 'is_public', 'created_at']);
    if(!$group || !$group->id){
        lib\Util::responseError(404, 'グループが見つかりません');
    }

    // ブログ取得
    $blogs = models\Blog::where('group_id', $group_id)
        ->orderBy('created_at', 'desc')
        ->get([
            'id as blog_id', 
            'title', 
            'content', 
            'tags', 
            'created_at'
        ]);

    // メッセージ取得
    $messages = models\Chat::join('profiles', 'chats.sender_id', '=', 'profiles.user_id')    
    ->join('users', 'chats.sender_id', '=', 'users.id')
    ->where('group_id', $group_id)
        ->orderBy('created_at', 'asc')
        ->get([
            'chats.id as message_id', 
            'users.user_id as sender_user_id', 
            'profiles.display_name as sender_display_name',
            'profiles.icon_url as sender_icon',
            'chats.content', 
            'chats.image_url', 
            'chats.created_at'
        ]);

    lib\Util::responseSuccess('成功しました。',[
        'group_name' => $group->name,
        'group_icon' => $group->group_icon_url,
        'group_blogs' => $blogs,
        'messages' => $messages
    ]);
} catch (Exception $e){
    error_log("エラーが発生しました: " . $e->getMessage());
    lib\Util::responseError(500, 'サーバーエラーが発生しました');
}