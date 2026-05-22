# LiQt各ページにおける項目
このドキュメントでは、LiQt の各ページにおいて必要な項目や機能について説明します。各ページの内容を充実させるために、以下の項目を参照してください。
README.md の内容をもとに、各ページに必要な項目をリストアップしています。必要に応じて、追加の項目や機能を検討してください。

## 語句定義
- サインイン    ：ユーザーが LiQt にサインインすること。
- サインアップ  ：ユーザーが LiQt に新規登録すること。
- サインアウト  ：ユーザーが LiQt からサインアウトすること。
- プロフィール  ：ユーザーの基本情報や自己紹介を表示するページ。
- ユーザー      ：LiQt を利用する個人。プロフィールを持ち、グループに参加し、ブログを投稿できる。
- グループ      ：ユーザーが参加するコミュニティ。公開/非公開の設定が可能。
- チャット      ：グループ内でのメッセージのやり取り。テキスト、画像、ファイルの送信が可能。
- ブログ        ：ユーザーが投稿する記事。公開設定（public / private / group）が可能。
- タグ          ：ブログやユーザープロフィールに付与するキーワード。検索やフィルタリングに使用。
- 通知          ：ユーザーに対するイベントの知らせ。新着、申請、コメント、招待などを含む。

## 注意事項
- 各フォームには適切なバリデーションを実装してください。
- ユーザーフレンドリーなエラーメッセージを表示してください。
- ページ名: ページ名.php は実際のファイル名を示します。
- APIエンドポイント: api/endpoint.php は実際の API ファイル名を示します。
- 項目名: 項目名 はコードやデータベースで使用する `name` 属性や変数名を示します。
- レスポンス項目: レスポンス項目 は API から返されるデータの項目名を示します。
- フォーム項目: フォーム項目 はユーザーが入力するフォームの項目名を示します。
- () の中はリンク先を示します。
- `[]` は配列（複数の要素）を示します。
- **太字** は特に重要な項目や注意点を示します。
- レスポンス項目の「～表示」は、画面に表示される項目であり、表示と書かれていない項目は内部的な項目（url や id など）です。

## API 開発での注意事項
- `vendor/autoload.php` は API ファイル内で必ず `require_once` してください（Composer ライブラリの読み込み）。
- 各 API は通常 `POST` リクエストを想定しますが、用途に応じ `GET` を使うことも明記してください。
- レスポンスは JSON 形式で返し、適切な HTTP ステータスコードを設定してください。
- エラーハンドリングを実装し、内部情報を漏らさないようにしてください。
- セキュリティ対策を必ず行ってください（例: プレースホルダを使った SQL 実行、CSRF トークン検証、出力時のエスケープなど）。
- ライブラリ呼び出しはプロジェクトの命名規則に従ってください（例: `lib/CSRFToken()`、`lib/Util::connectDB()`）。
- API ファイルの冒頭に受け取るフォーム項目と返すレスポンス項目をコメントで明記してください。
    - 例: // フォーム項目: user_id, password  レスポンス項目: success, message, data
- API の標準レスポンス形式の例:

```json
{
    "success": true,
    "message": "",
    "data": {}
}
```

## 認証系ページ authentication

### サインインページ(`signin.php`)
- フォーム
    - メールアドレス入力: `mail_address`
    - パスワード入力    : `password` **英大文字・小文字・数字を含む 6～20 文字が推奨**

- サインインボタン
    - サインイン処理(`api/auth/signin.php`)
        - レスポンス:`success`->[true/false], `message`->エラーメッセージ
        - 成功：ダッシュボードページへ遷移
        - 失敗：エラーメッセージ表示

    - リンク
        - パスワード再設定案内（`forgot_password.php` へのリンク）
        - サインアップページへのリンク（`signup.php`）

### 新規登録ページ(`signup.php`)
- フォーム
    - ユーザID          : `user_id` **プライマリ且つ変更不可** **ユーザIDは半角英数字で 5～20 文字**
    - 表示名            : `display_name`
    - メールアドレス    : `mail_address`
    - パスワード        : `password` **英大文字・小文字・数字を含む 6～20 文字が推奨**
    - パスワード確認    : `password_confirm`

- サインアップボタン
    - サインアップ処理(`api/auth/signup.php`)
        - レスポンス:`success`->[true/false], `message`->エラーメッセージ
        - 成功：メール認証ページへ遷移
        - 失敗：エラーメッセージ表示

- リンク
    - サインインページへのリンク(`signin.php`)

### メール認証ページ(`verify_email.php`)
- トークン送信処理
    - トークン送信処理（`api/auth/send_verify_token.php`）
    - 30 秒のクールダウンタイム
    
- フォーム
    - メールアドレス入力: `mail_address`
    - トークン入力      : `token`

- 認証ボタン
    - 認証処理(`api/auth/verify_email.php`)
        - レスポンス:`success`->[true/false], `message`->エラーメッセージ
        - 成功：サインインページへ遷移
        - 失敗：エラーメッセージ表示

        - トークン再送信ボタン
            - トークン再送信処理（`api/auth/send_verify_token.php`）
            - 30 秒のクールダウンタイム

## ページ
- サインイン検証コンポーネント（`component/auth_check.php`）
    - サインイン状態の検証
    - 認証が必要なページへのアクセス制御
        - 認証されていないユーザーはサインインページへリダイレクト（`signin.php`）

- ヘッダ（`component/header.php`）
    - ロゴ
    - 検索
        - ユーザID検索
        - グループ検索
        - ブログ検索

- フッター（`component/footer.php`）
    - プロフィールページリンク（`profile.php`）
    - ダッシュボードページリンク（`dashboard.php`）
    - ブログページリンク（`blogs.php`）

### プロフィールページ(`profile.php`)
- GET リクエストで `user_id` を受け取ることを前提とします。
    - `profile.php?user_id=xxx`

- GETパラメータがない場合は自分のプロフィールを表示することを前提とすること。
    - `profile.php`

- レスポンス（`api/profile/get_profile.php`）
    - `success` -> true/false
    - `message` -> エラーメッセージ
    - `data` -> プロフィール情報
        - アイコン表示      : `icon_url`
        - ユーザID          : `user_id`
        - 表示名            : `display_name`
        - 自己紹介表示      : `introduction`
        - タグ表示          : `tags` **カンマ区切りの文字列（半角カンマ）**
        - ブログ一覧表示    : `blogs[]` **ブログ ID 配列。URL に変換して表示：/blog_detail.php?blog_id=xxx**

    - 自分のプロフィールの場合
        - 編集ボタン
            - 編集処理（`api/profile/update_profile.php`）
                - フォーム
                    - アイコンアップロード : `icon`
                    - 表示名入力           : `display_name`
                    - 自己紹介入力         : `introduction`
                    - タグ入力             : `tags` **カンマ区切りの文字列（半角カンマ）**
                - レスポンス: `success`->[true/false], `message`->エラーメッセージ
                - 成功：プロフィールページを更新して表示
                - 失敗：エラーメッセージ表示

    - 他人のプロフィールの場合
        - チャットを開始
            - チャット開始処理（`api/profile/start_chat.php`）
                - レスポンス: `success`->true/false, `message`->エラーメッセージ, `chat_link`
                - 成功：チャットページへ遷移（`chat.php?group_id=xxx`）
                - 失敗：エラーメッセージ表示

        - ブロックボタン
            - ブロック処理（`api/profile/block_user.php`）
                - レスポンス: `success`->true/false, `message`->エラーメッセージ
                - 成功：プロフィールページを更新して表示
                - 失敗：エラーメッセージ表示

        - ブロック解除ボタン
            - ブロック解除処理（`api/profile/unblock_user.php`）
                - レスポンス: `success`->true/false, `message`->エラーメッセージ
                - 成功：プロフィールページを更新して表示
                - 失敗：エラーメッセージ表示

### ダッシュボードページ

#### ダッシュボードトップ(`dashboard.php`)
- レスポンス(`api/dashboard/get_dashboard.php`)
    - success -> [true/false]
    - message -> エラーメッセージ
    - data -> ダッシュボード情報
        - グループ一覧 : `joined_groups[]`
            - グループID: `group_id`
            - グループ名表示            : `group_name` **クリックで `chat.php?group_id=xxx` に遷移**
            - アイコン表示              : `group_icon`
            - 最新のチャット内容表示    : `latest_message`

- グループ作成ページへのリンク(`create_group.php`)

#### グループ作成ページ（`create_group.php`）
- フォーム
    - グループ名入力                     : `group_name`
    - アイコンアップロード               : `group_icon`
    - 公開/非公開設定                    : `is_public`
    - ユーザ招待（ユーザID入力 検索も可）: `invite_user_ids[]`

    - 作成ボタン
        - 作成処理（`api/group/create_group.php`）
            - レスポンス: `success`->[true/false], `message`->エラーメッセージ
            - 成功：グループチャットページへ遷移
            - 失敗：エラーメッセージ表示

#### グループチャットページ（`chat.php`）
- GETリクエストで`group_id`を受け取ることを前提とすること。
    - `chat.php?group_id=xxx`

- レスポンス(`api/group/get_group_chat.php`)
    - success -> [true/false]
    - message -> エラーメッセージ
    - data -> グループチャット情報
        - グループ名表示                     : `group_name`
        - アイコン表示                       : `group_icon`
        - グループのブログ一覧 : `group_blogs[]`
            - ブログID: `blog_id`
            - タイトル表示 : `title` **タイトルをクリックでページ遷移(`blog_detail.php?blog_id=xxx`)**
            - タグ表示   : `tags` **カンマ区切りの文字列**
            - 作成日時表示 : `created_at`

        - メッセージ一覧 : `messages[]`
            - メッセージID      : `message_id`
            - 送信者ユーザID    : `sender_user_id`
            - 送信者名表示      : `sender_display_name` **ユーザIDではなく表示名を表示すること。表示名をクリックでプロフィールページへ遷移(`profile.php?user_id=xxx`)**
            - 送信者アイコン表示: `sender_icon`
            - メッセージ内容表示: `content`
            - 送信日時表示      : `created_at`
            - 添付写真表示      : `image_url` **画像がない場合は null**
        
    - フォーム
        - メッセージ投稿（Markdown）         : `message_content` **作成中にプレビューを表示（例: markdown-it など）**
        - 画像アップロード                   : `image_upload`

    - 送信ボタン
        - メッセージ送信処理（`api/group/send_message.php`）
            - レスポンス: `success`->[true/false], `message`->エラーメッセージ
            - 成功：メッセージをチャットに表示
            - 失敗：エラーメッセージ表示

- グループ編集ページへのリンク(`edit_group.php?group_id=xxx`)

#### グループ編集ページ（`edit_group.php`）
- GETリクエストで`group_id`を受け取ることを前提とすること。
    - `edit_group.php?group_id=xxx`

- オーナと管理者、メンバーで表示内容や操作できる内容が異なることを前提とすること。
- 自分のロールを確認すること
    - ロール確認処理（`api/group/get_user_role.php`）
        - レスポンス: `success`->true/false, `message`->エラーメッセージ, `role`->[owner/manager/member]

- オーナー、管理者
    - レスポンス(`api/group/get_group_info.php`)
        - グループ名表示   : `group_name`
        - アイコン表示     : `group_icon`
        - 公開/非公開表示  : `is_public`
        - メンバ一覧表示   : `members[]`
            - アイコン表示      : `icon_url`
            - ユーザID          : `user_id`
            - ユーザ名表示      : `display_name` **ユーザIDではなく表示名を表示すること。表示名をクリックでプロフィールページへ遷移(`profile.php?user_id=xxx`)**
            - 権限[オーナー/管理者/メンバー] : `role`
        
    - フォーム
        - グループ名入力        : `group_name`
        - アイコンアップロード  : `group_icon`
        - 公開/非公開設定       : `is_public`
        - メンバ管理
            - メンバ追加 : `add_user_ids` **ユーザID入力（検索可）**
            - メンバ削除 : `remove_user_ids` **自分以外を削除可能**
            - 権限変更   : `change_role_user_ids` + `new_roles` **ユーザID入力（検索可） + 新しいロール[owner/manager/member]**
                - オーナーは自分の権限を管理者に変更可能（ただし、オーナーは必ず1人以上必要）
                - 管理者は自分以外のメンバの権限を変更可能（ただし、オーナーの権限は変更不可）

    - 編集ボタン
        - 編集処理（`api/group/update_group.php`）
            - レスポンス: `success`->true/false, `message`->エラーメッセージ
            - 成功：`chat.php?group_id=xxx` へ遷移
            - 失敗：エラーメッセージ表示

    - グループ削除ボタン（オーナー権限）
        - 削除処理（`api/group/delete_group.php`）
            - レスポンス: `success`->true/false, `message`->エラーメッセージ
            - 成功：`dashboard.php` へ遷移
            - 失敗：エラーメッセージ表示
- メンバー
    - レスポンス（`api/group/get_group_info.php`）
        - グループ名表示   : `group_name`
        - アイコン表示     : `group_icon`
        - 公開/非公開表示  : `is_public`
        - メンバ一覧表示   : `members[]`
            - アイコン表示      : `icon_url`
            - ユーザID          : `user_id`
            - 表示名            : `display_name` **表示名をクリックすると `profile.php?user_id=xxx` に遷移**
            - 権限 [owner/manager/member] : `role`

    - 退会ボタン
        - 退会処理（`api/group/leave_group.php`）
            - レスポンス: `success`->true/false, `message`->エラーメッセージ
            - 成功：`dashboard.php` へ遷移
            - 失敗：エラーメッセージ表示

#### オープンチャット一覧ページ（`open_chats.php`）
- 公開グループ一覧(`api/group/get_public_groups.php`)
    - グループID                : `group_id`
    - グループ名表示            : `group_name`
        - グループチャットページへのリンク
            - 押した時点でグループに参加する処理(`api/group/join_group.php`)
                - レスポンス:`success`->true/false, `message`->エラーメッセージ
                - 成功：グループチャットページへ遷移(`chat.php?group_id=xxx`)
                - 失敗：エラーメッセージ表示
    - アイコン表示              : `group_icon`
    - 最新のチャット内容表示    : `latest_message`

- グループ検索機能
    - フォーム
        - グループ名入力 : `group_name_search`

    - 検索ボタン
        - 検索処理（`api/group/search_groups.php`）
            - レスポンス: `success`->true/false, `message`->エラーメッセージ, `groups[]`
            - 成功：検索結果を表示
            - 失敗：エラーメッセージ表示

### ブログ（Qiita 風）ページ

#### ブログ一覧ページ（`blogs.php`）
- レスポンス(`api/blog/get_blogs.php`)
    - 公開記事一覧表示 : `public_blogs[]`
        - ブログID: `blog_id`
        - タイトル表示 : `title` **タイトルをクリックでページ遷移(`blog_detail.php?blog_id=xxx`)**
        - タグ表示   : `tags` **カンマ区切りの文字列**
    - 非公開記事一覧表示 : `private_blogs[]`
        - ブログID: `blog_id`
        - タイトル表示 : `title` **タイトルをクリックでページ遷移(`blog_detail.php?blog_id=xxx`)**
        - タグ表示   : `tags` **カンマ区切りの文字列**

        - 自分の投稿一覧表示 : `my_blogs[]`
            - ブログID: `blog_id`
            - タイトル表示 : `title` **クリックで `blog_detail.php?blog_id=xxx` に遷移**
            - タグ表示   : `tags` **カンマ区切りの文字列（半角カンマ）**
            - 公開設定表示 : `visibility` (public / private / group)

- フォーム
    - タグ入力     : `tag_search`
    - グループ指定 : `group_filter`

- 検索ボタン
    - 検索処理(`api/blog/search_blogs.php`)
        - レスポンス : `success`->true/false, `message`->エラーメッセージ, `blogs[]`
        - 成功：検索結果を表示
        - 失敗：エラーメッセージ表示
    
#### ブログ詳細ページ（`blog_detail.php`）
- レスポンス(`api/blog/get_blog_detail.php`)
    - タイトル表示  : `title`
    - タグ表示      : `tags`　**カンマ区切りの文字列**
    - 本文表示      : `content`
        - 投稿者表示    : `author`
            - ユーザID          : `user_id`
            - 表示名            : `display_name` **表示名をクリックで `profile.php?user_id=xxx` に遷移**
            - アイコン表示      : `icon_url`

    - いいね表示    : `likes`
        - コメント表示  : `comments[]`
            - コメントID        : `comment_id`
            - コメント内容表示  : `content`
            - コメント投稿者表示: `author`
                - ユーザID          : `user_id`
                - 表示名            : `display_name` **表示名をクリックで `profile.php?user_id=xxx` に遷移**
                - アイコン表示      : `icon_url`

    - 関連記事表示  : `related_blogs[]`
        - ブログID      : `blog_id`
        - タイトル表示  : `title` **タイトルをクリックでページ遷移(`blog_detail.php?blog_id=xxx`)**
        - タグ表示      : `tags` **カンマ区切りの文字列**

#### ブログ作成ページ（`create_blog.php`）
- フォーム(`api/blog/create_blog.php`)
    - タイトル  : `title`
    - 本文      : `content`
    - 公開設定（public / private / group）: `visibility`
        - 公開：全員に公開
        - 非公開：自分にのみ公開
        - グループ：グループに所属しているユーザーに公開（グループ選択も必要）
            - グループ選択 : `group_id` **グループに所属しているユーザーのみ表示されるようにすること。グループ名を表示して選択できるようにすること。**

    - タグ設定  : `tags` **カンマ区切りの文字列（半角カンマ）**

- 投稿ボタン
    - 投稿処理(`api/blog/create_blog.php`)
        - レスポンス:`success`->true/false, `message`->エラーメッセージ
        - 成功：ブログ詳細ページへ遷移(`blog_detail.php?blog_id=xxx`)
        - 失敗：エラーメッセージ表示

#### ブログ編集ページ（`edit_blog.php`）
- GETリクエストで`blog_id`を受け取ることを前提とすること。
    - `edit_blog.php?blog_id=xxx`
- ブログの投稿者以外がアクセスした場合は、ブログ詳細ページへリダイレクトすることを前提とすること。
     - `blog_detail.php?blog_id=xxx`
- レスポンス(`api/blog/get_blog_detail.php`)
    - タイトル  : `title`
    - タグ      : `tags` **カンマ区切りの文字列**
    - 本文      : `content`
    - 公開設定  : `visibility`

- フォーム
    - タイトル編集  : `title`
    - タグ編集      : `tags` **カンマ区切りの文字列**
    - 本文編集      : `content`
    - 公開設定変更（public / private / group）: `visibility`
    
    - 削除ボタン
        - 削除処理（`api/blog/delete_blog.php`）
            - レスポンス: `success`->true/false, `message`->エラーメッセージ
            - 成功：ブログ一覧ページへ遷移
            - 失敗：エラーメッセージ表示
    
    - 編集ボタン
        - 編集処理（`api/blog/update_blog.php`）
            - レスポンス: `success`->true/false, `message`->エラーメッセージ
            - 成功：編集後のブログ詳細ページへ遷移(`blog_detail.php?blog_id=xxx`)
            - 失敗：エラーメッセージ表示

### 追加: サインアウト・パスワード再設定（不足していた認証フロー）

#### サインアウトページ（`signout.php`）
- POST リクエストでサインアウト処理を行うことを想定します。
    - サインアウト処理（`api/auth/signout.php`）
        - レスポンス: `success`->true/false, `message`->エラーメッセージ
        - 成功：`signin.php` へリダイレクト
        - 実装上の注意: セッション破棄、Cookie の削除、CSRF 検証を行うこと。

#### パスワード再設定（2 段階フロー）

1) パスワード再発行申請ページ（`forgot_password.php`）
- フォーム: メールアドレス `mail_address`
- 送信処理: `api/auth/request_password_reset.php`（トークンを生成してメール送信）**5分間有効なトークンを生成し、メールで送信すること。**
- レスポンス: `success`->true/false, `message`->エラーメッセージ

2) パスワードリセットページ（`reset_password.php`）
- フォーム: `mail_address`, `token`, `new_password`, `new_password_confirm`
- リセット処理: `api/auth/reset_password.php`
    - レスポンス: `success`->true/false, `message`->エラーメッセージ
    - 成功：`signin.php` へ遷移
    - 実装上の注意: トークンの有効期限を設定し、再利用を防ぐこと。パスワード要件はサインアップと同様に検証すること。

---

（文書全般の表記ルール）
- 表示名は一貫して `表示名` を使用しました。
- 配列表記は `[]` とし、要素の型や説明を併記してください（例: `blogs[]` は `blog_id` の配列）。
- カンマ区切りは「半角カンマ」を前提として表記しています。
