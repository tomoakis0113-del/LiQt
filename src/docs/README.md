# LiQt — 各ページの要約

このドキュメントは `docs/Items.md` を要約した簡潔版です。各ページで必要な項目と主要な API 要件を短くまとめています。

## 目的と用語（簡潔）
- サインイン / サインアップ / サインアウト: 認証フロー
- プロフィール: ユーザー情報表示・編集
- グループ / チャット: コミュニティとメッセージ機能
- ブログ: 記事投稿・閲覧（公開/非公開/グループ）

## 主要ページ（概要）
- サインイン（`signin.php`）: メール・パスワード入力、`api/signin.php` で認証
- サインアップ（`signup.php`）: ユーザ登録、メール認証フロー
- プロフィール（`profile.php`）: 表示・編集（`api/get_profile.php`, `api/update_profile.php`）
- ダッシュボード（`dashboard.php`）: 参加グループ一覧、グループ作成への導線
- グループ（`create_group.php`, `chat.php`, `edit_group.php`）: 作成・チャット・メンバー管理
- ブログ（`blogs.php`, `blog_detail.php`, `create_blog.php`, `edit_blog.php`）: 投稿一覧・詳細・作成・編集
- パスワード再発行（`forgot_password.php`, `reset_password.php`）とサインアウト（`signout.php`）

## API の基本方針（必須事項）
- Composer ライブラリは各 API で `vendor/autoload.php` を `require_once` すること。
- レスポンスは JSON 形式で統一: {"success": boolean, "message": string, "data": object}
- セキュリティ: プレースホルダ付き SQL（プリペアドステートメント）、CSRF トークン検証、出力エスケープ、入力バリデーションを必須とする。
- API メソッドと期待するフォーム項目・レスポンス項目はファイル冒頭にコメントで明記すること。

## 実装上の注意（短く）
- 各フォームに適切なバリデーションとユーザ向けエラーメッセージを実装する。
- 公開/非公開の権限やグループのロール（owner/manager/member）を明確に扱う。
- ブログ・タグは半角カンマ区切りで扱うなど、表記ルールを統一する。

必要に応じて詳細は `docs/Items.md` を参照してください。