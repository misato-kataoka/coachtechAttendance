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
mailtrapを使用しています。
以下のリンクから会員登録をしてください。　
https://mailtrap.io/

Sandbox内のIntegrationから「laravel 7.x and 8.x」を選択し、
.envファイルのMAIL_MAILERからMAIL_ENCRYPTIONまでの項目をコピー＆ペーストしてください。　
MAIL_FROM_ADDRESSは任意のメールアドレスを入力してください。

## ER図


## テストアカウント
* **名前:** '西　玲奈'
* **Email:** 'reina.n@coachtech.com'
* **password:** 'password123'

* **名前:** '山田　太郎'
* **Email:** 'tarou.y@coachtech.com'
* **password:** 'password456'

* **名前:** '管理者'
* **Email:** 'admin@coachtech.com'
* **password:** 'adminpass'

## 使用技術

-php 7.4.9

-Laravel (v8.6.12)

-MySQL 8.0.26

-Docker

## URL

-開発環境 http://localhost/

-phpMyAdmin http://localhost:8080
