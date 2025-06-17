# coachtech勤怠管理アプリ


## 環境構築
### Docker ビルド
1. **リポジトリをクローン**
   ```bash
   git clone git@github.com:misato-kataoka/coachtechAttendance.git

2. cd coachtechAttendance

3. docker compose up -d --build

### Laravelの環境構築
1. docker compose exec php bash

2. composer install

3. .env.exmpleファイルから.envファイルを作成し、環境変数を以下の通りに変更
```
  DB_CONNECTION=mysql
  DB_HOST=mysql
  DB_PORT=3306
  DB_DATABASE=laravel_db
  DB_USERNAME=laravel_user
  DB_PASSWORD=laravel_pass

STRIPE_KEY=pk_test_51RRVovH1ap6ABKzgQAn4S9wxQIN0slhHLU6AmiciLYL1NG0iR0vRSuk3iSDOxMJtMUYC7jzahEJXpQgCHrsGKZia00awOr9IHV
STRIPE_SECRET=sk_test_51RRVovH1ap6ABKzg5R9B8ykI65tfvTttnAGKPKddAWvG606XMgkbXmRNJOHWkOzt0zkOs5RAUIOyzPPN0Zf6c9aN00YKUK6lCC
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

## ER図


## テストユーザー
* **Email:** 'testuser@example.com'
* **password:** 'password123'

## 使用技術

-php 7.4.9

-Laravel (v8.6.12)

-MySQL 8.0.26

-Docker

## URL

-開発環境 http://localhost/

-phpMyAdmin http://localhost:8080
