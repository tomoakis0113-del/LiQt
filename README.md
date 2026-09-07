# LiQt

LiQtは、学生・プログラマー同士の交流や情報共有を目的として開発したWebアプリケーションです。

LINEのようなグループチャット機能と、Qiitaのような記事投稿機能を組み合わせ、
ユーザー同士がコミュニケーションを取りながら技術情報を共有できるサービスを目指しました。

## 開発概要

- 開発形態：チーム開発
- 開発班：3名
- 担当：バックエンド開発
- 使用言語：PHP / JavaScript / HTML / CSS
- データベース：MariaDB
- 開発環境：Docker
- バージョン管理：Git / GitHub

## 主な機能

- ユーザー登録・サインイン
- メール認証
- プロフィール管理
- グループ作成・参加
- グループチャット
- 記事の投稿・閲覧
- ユーザー検索
- 通知機能
- Markdown対応

## 自分の担当

主にPHPを使用したバックエンド開発を担当しました。

- APIの設計・実装
- データベースとの連携
- ユーザー認証・セッション管理
- メール認証処理
- プロフィール関連処理
- グループ・チャット関連処理
- ブログ関連処理
- 入力値のバリデーション
- エラー処理
- CSRF対策などのセキュリティ対策

チーム開発では、フロントエンド担当とAPIの仕様を共有しながら実装を進めました。

## 使用技術

| 分類 | 技術 |
|---|---|
| Backend | PHP |
| Frontend | HTML / CSS / JavaScript / Bootstrap |
| Database | MariaDB |
| Environment | Docker |
| Package Management | Composer |
| Mail | PHPMailer |
| Environment Variables | Dotenv |
| Version Control | Git / GitHub |

## セキュリティ対策

- `password_hash()` を利用したパスワードのハッシュ化
- CSRFトークンによるリクエスト検証
- Prepared StatementによるSQLインジェクション対策
- 入力値のバリデーション
- セッションを利用した認証・アクセス制御
- 環境変数による認証情報の管理

## 詳細資料

- [LiQt 説明資料（PowerPoint）](src/docs/説明書.pptx)
- [開発説明書（PDF）](src/docs/開発説明書.pdf)
- [機能仕様](src/docs/Items.md)
- [データベース設計](src/docs/scheme.yml)
- [ER図](src/docs/er図.svg)
- [ページ遷移図](src/docs/ページ遷移図.pdf)

## 開発について

本リポジトリは、チームで開発したLiQtを就職活動用に整理したものです。

開発時のブランチ・コミット履歴を残しており、
チームでの開発過程も確認できるようにしています。

元プロジェクト：
https://github.com/SanaeProject/newLiQt

## License

Apache License 2.0
