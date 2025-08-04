# COACHTECH フリマアプリ

これは、COACHTECHの模擬案件で作成したフリマアプリケーションです。

## ✨ 機能一覧

- 会員登録(メール認証)、ログイン・ログアウト機能
- プロフィール編集機能（アバター画像、ユーザー名、住所など）
- 商品一覧表示、検索機能
- 商品出品機能
- 商品詳細表示機能
- 商品購入機能
- コメント投稿機能
- いいね機能

## 🚀 技術スタック

- **バックエンド**: Laravel, PHP
- **フロントエンド**: Blade, CSS, JavaScript
- **データベース**: MySQL
- **開発環境**: Docker, Docker Compose

---

## 💻 環境構築手順

このアプリケーションをローカル環境で起動するための手順です。

### 必須要件

- Docker
- Docker Compose

### セットアップ

1. **リポジトリのクローン**

   ```bash
   git clone https://github.com/hgleam/coachtech-flea-market.git
   cd coachtech-flea-market
   ```
2. **.env ファイルの準備**
   `src` ディレクトリにある `.env.example` ファイルをコピーして `.env` ファイルを作成します。

   ```bash
   cp src/.env.example src/.env
   # .env ファイルを編集して、docker-compose.ymlのmysqlコンテナ設定(DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)を確認・設定
   # mysqlのサービス名 → .envのDB_HOST
   # MYSQL_DATABASE → .envのDB_DATABASE
   # MYSQL_USER → .envのDB_USERNAME
   # MYSQL_PASSWORD → .envのDB_PASSWORD
   # MAIL_HOST → mailhogのサービス名を設定
   # MAIL_FROM_ADDRESS → メールアドレスを設定(例：test@example.com)
   # MAIL_FROM_NAME → メールアドレスの名前を設定(例：Coachtech Flea Market)
   ```
3. **Dockerコンテナのビルドと起動**

   ```bash
   docker compose build
   docker compose up -d
   ```
4. **PHP依存パッケージのインストール**

   ```bash
   docker compose exec php composer install
   ```
5. **アプリケーションキーの生成**

   ```bash
   docker compose exec php php artisan key:generate
   ```
6. **データベースのマイグレーションと初期データ投入**
   以下のコマンドで、データベースのテーブル作成と初期データの投入を同時に行います。
   Seederには、動作確認用のユーザー、カテゴリ、商品データが含まれています。

   ```bash
   docker compose exec php php artisan migrate --seed
   ```
7. **ストレージへのシンボリックリンク作成**
   ユーザーがアップロードした画像などを公開するために、以下のコマンドを実行してシンボリックリンクを作成します。

   ```bash
   docker compose exec php php artisan storage:link
   ```
8. **アプリケーションへのアクセス**
   上記の手順が完了したら、ブラウザで以下のURLにアクセスしてください。

   - アプリケーション: [http://localhost:81](http://localhost:81)
   - phpMyAdmin: [http://localhost:8080](http://localhost:8080)
   - MailHog (メール確認用): [http://localhost:8025](http://localhost:8025)

   MailHogは開発用のメールサーバーです。会員登録時の認証メールなどは、実際のメールボックスではなくMailHogのWeb UIに届きますので、こちらでご確認ください。

### テスト用アカウント

`--seed` オプションでデータベースを初期化すると、以下のテスト用アカウントが作成されます。動作確認に利用してください。

- **出品者アカウント**

  - メールアドレス: `test1@example.com`
  - パスワード: `password!!`
  - 備考: このアカウントは商品データ一覧シートに記載のある全ての商品を出品している状態です。
- **購入者アカウント**

  - メールアドレス: `test2@example.com`
  - パスワード: `password!!`
  - 備考: このアカウントは会員登録しただけで、まだ商品を出品・購入していない状態です。

---

## ER図

![ER図](er.png)
