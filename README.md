# coachtech 勤怠管理アプリ

## 環境構築

### Docker ビルド

1. **リポジトリをクローン**

   ```bash
   git clone git@github.com:misato-kataoka/coachtechAttendance.git

   ```

2. cd coachtechAttendance

3. docker compose up -d --build

### Laravel の環境構築

1. docker compose exec php bash

2. composer install

3. .env.exmple ファイルから.env ファイルを作成し、環境変数を以下の通りに変更

```
  DB_CONNECTION=mysql
  DB_HOST=mysql
  DB_PORT=3306
  DB_DATABASE=laravel_db
  DB_USERNAME=laravel_user
  DB_PASSWORD=laravel_pass

```

4. docker compose exec php bash

5. アプリケーションキーの作成

```
　php artisan key:generate
```

6. マイグレーションの実行

```
  php artisan migrate
```

7. シーディングを実行する

```
  php artisan db:seed
```

## メール認証

mailtrap を使用しています。
以下のリンクから会員登録をしてください。
https://mailtrap.io/

Sandbox 内の Integration から「laravel 7.x and 8.x」を選択し、
.env ファイルの MAIL_MAILER から MAIL_ENCRYPTION までの項目をコピー＆ペーストしてください。
MAIL_FROM_ADDRESS は任意のメールアドレスを入力してください。

## ER 図

<img width="1026" height="858" alt="Image" src="https://github.com/user-attachments/assets/fa6e886e-77d1-4c50-92a4-8f51a4b0b1f2" />

## テストアカウント

- 一般ユーザー 1
- **名前:** '西　玲奈'
- **Email:** 'reina.n@coachtech.com'
- **password:** 'password123'

- 一般ユーザー 2
- **名前:** '山田　太郎'
- **Email:** 'tarou.y@coachtech.com'
- **password:** 'password456'

- 管理者ユーザー
- **名前:** '管理者'
- **Email:** 'admin@coachtech.com'
- **password:** 'adminpass'
- **管理者ログインページ URL:** 'http://localhost/admin/login'

## 使用技術

-php 7.4.9

-Laravel (v8.6.12)

-MySQL 8.0.26

-Docker

## URL

-開発環境 http://localhost/

-phpMyAdmin http://localhost:8080
