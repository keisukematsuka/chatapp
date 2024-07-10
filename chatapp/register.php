<?php

session_start();

/**
 * データベースに接続する関数
 *
 * @return PDO
 */

require_once "sql_connection.php";

?>

<!DOCTYPE html>
<html>

<head>
    <title>登録ページ</title>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="./css/register.css">
</head>

<body>
    <!-- 新規登録画面 -->
    <!-- 現在のリクエストがGETメソッドである時 -->
    <?php if ($_SERVER['REQUEST_METHOD'] == 'GET') { ?>
        <div class="register-container">
            <h2>新規登録</h2>
            <!-- ユーザーが入力したデータを同じページのサーバーサイドスクリプトにPOSTで送る -->
            <form action=<?php echo $_SERVER['PHP_SELF'] ?> method="post">
                <label for="name">名前:</label>
                <input type="text" id="name" name="name" required>

                <label for="email">メールアドレス:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">パスワード:</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">パスワード確認:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit">登録</button>
                <?php if (isset($_SESSION["err_register_message"])) {
                    echo "<p style='color: red;'>" . $_SESSION["err_register_message"] . "</p>";
                } ?>
            </form>
            <p>既にアカウントをお持ちの方は<a href="login.php">ログインページ</a>へ</p>
        </div>
    <?php } ?>

    <!-- 現在のリクエストがPOSTメソッドである時 -->
    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST') { ?>
        <?php
        // フォームからの入力を取得
        $name = $_POST["name"];
        $email = $_POST["email"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        // データベースへの接続
        try {
            $conn = getDB();
            // emailが入力されたDBにあるすべてのユーザーを指定　?はプレースホルダで後に置き換えが行われる
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            // 置き換え処理
            $stmt->execute([$email]);
            // 連想配列に格納
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // 変数$resultの中身は、id,name,email,passwordが連想配列で格納されている

            if (!$result) {
                // パスワードと確認用のパスワードが一致している場合
                if ($password === $confirm_password) {
                    // 入力されたパスワードをハッシュ化してDBに登録する
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    // name,email,passwordをDBに新規登録　?はプレースホルダで後に置き換えが行われる
                    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                    // 置き換え処理
                    $stmt->execute([$name, $email, $hashed_password]);

                    // 登録後すぐにログインさせる処理
                    // emailがが一致するユーザーをDBから指定　?はプレースホルダで後に置き換えが行われる
                    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
                    // 置き換え処理
                    $stmt->execute([$email]);
                    // 連想配列に格納
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    // 変数$usersの中身は、id,name,email,passwordが連想配列で格納されている

                    // DBに登録された情報をセッションに保存
                    $_SESSION["id"] = $user["id"];
                    $_SESSION["name"] = $user["name"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["err_register_message"] = null;
                    // ログイン成功時にindex.phpにリダイレクト
                    header("Location: index.php");
                    exit;
                    // パスワードと確認用パスワードが違う時
                } else {
                    $_SESSION["err_register_message"] = "確認と違うパスワードが入力されました。";
                    header("Location: register.php");
                    exit;
                }
            } else {
                // ログイン失敗
                $_SESSION["err_register_message"] = "そのemailは登録されています。";
                header("Location: register.php");
                exit;
            }
            // データべーすへの接続失敗
        } catch (PDOException $e) {
            echo "エラー: " . $e->getMessage();
            exit;
        }
        ?>
    <?php } ?>
</body>

</html>
