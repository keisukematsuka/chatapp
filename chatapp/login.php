<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <title>ログイン</title>
    <link rel="stylesheet" type="text/css" href="./css/login.css">
    <meta charset="utf-8">
</head>

<body>
    <!-- 現在のリクエストがGETメソッドである時 -->
    <?php if ($_SERVER['REQUEST_METHOD'] == 'GET') { ?>
        <!-- ログイン入力フォーム -->
        <div class="login-container">
            <h2>ログイン</h2>
            <!-- ユーザーが入力したデータを同じページのサーバーサイドスクリプトにPOSTで送る -->
            <form action=<?php echo $_SERVER['PHP_SELF'] ?> method="post">
                <label for="email">メールアドレス:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">パスワード:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">ログイン</button>

                <!-- ログイン情報が間違っていた時、メールアドレスまたはパスワードが間違っています。を画面に出す -->
                <?php if (isset($_SESSION["err_login_message"])) {
                    echo "<p style='color: red;'>" . $_SESSION["err_login_message"] . "</p>";
                } ?>
            </form>
            <p>アカウントをお持ちでない方は<a href="register.php">新規登録ページ</a>へ</p>
        </div>
    <?php } ?>

    <!-- 現在のリクエストがPOSTである時 -->
    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST') { ?>
        <?php

        require_once "sql_connection.php";

        // function getDB(): PDO
        // {
        //     $dsn = 'mysql:dbname=webapp; host=127.0.0.1; charset=utf8';
        //     $usr = 'root';
        //     $password = '';
        //     $db = new PDO($dsn, $usr, $password);
        //     $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //     return $db;
        // }

        // フォームからの入力を取得
        $email = $_POST["email"];
        $password = $_POST["password"];

        // データベースに接続が成功した時
        try {
            $conn = getDB();
            // emailがが一致するユーザーをDBから指定　?はプレースホルダで後に置き換えが行われる
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            // 置き換え処理
            $stmt->execute([$email]);
            // 連想配列に格納
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // 変数$userの中身は、id,name,email,passwordが連想配列で格納されている

            // DBに$userが存在してる時、入力されたパスワードとDBに登録されたパスワードが一致した時
            if ($user && password_verify($password, $user['password'])) {
                // ログイン成功時にindex.phpにリダイレクト
                $_SESSION["id"] = $user["id"];
                $_SESSION["name"] = $user["name"];
                $_SESSION["email"] = $user["email"];
                $_SESSION["err_login_message"] = null;
                header("Location: index.php");
                exit;
                // $userが存在しないもしくはパスワードが一致しない場合
            } else {
                $_SESSION["err_login_message"] = "メールアドレスまたはパスワードが間違っています。";
                header("Location: login.php");
                exit;
            }
            // データベースに接続が失敗した時
        } catch (PDOException $e) {
            $_SESSION["err_login_message"] = "データベースエラー: " . $e->getMessage();
            header("Location: login.php");
            exit;
        }
        ?>
    <?php } ?>
</body>

</html>
