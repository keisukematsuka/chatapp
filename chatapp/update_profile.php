<?php session_start(); ?>
<!DOCTYPE html>
<html>

<head>
    <title>ユーザー情報更新</title>
    <link rel="stylesheet" type="text/css" href="login.css">
    <meta charset="utf-8">
    <link rel="stylesheet" href="./css/update.css">
</head>

<body>
    <!-- 現在のリクエストがGETメソッドである時 -->
    <?php if ($_SERVER['REQUEST_METHOD'] == 'GET') { ?>
        <!-- ユーザー更新フォーム -->
        <div class="update-user-container">
            <h2>ユーザー情報更新</h2>
            <form action=<?php echo $_SERVER['PHP_SELF'] ?> method="post">
                <label for="name">名前:</label>
                <input type="text" id="name" name="name" value=<?php echo $_SESSION["name"] ?> required>

                <label for="password">新しいパスワード:</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">パスワード確認:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button class="form-button" type="submit">更新</button>
                <?php if (isset($_SESSION["err_update_message"])) {
                    echo "<p style='color: red;'>" . $_SESSION["err_update_message"] . "</p>";
                } ?>
            </form>
        </div>
    <?php } ?>

    <!-- 現在のリクエストがPOSTメソッドである時 -->
    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST') { ?>
        <?php

        require_once "sql_connection.php";

        // フォームからの入力を取得
        $name = $_POST["name"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];

        // データべーすへの接続が成功した場合
        try {
            $conn = getDB();
            // name,passwordをDBから更新する
            $stmt = $conn->prepare("UPDATE users SET name=?, password=? WHERE id=?");
            // パスワードのハッシュ化
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->execute([$name, $hashed_password, $_SESSION["id"]]);

            // 更新後のユーザー情報を取得してセッションに格納
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION["id"]]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION["name"] = $user["name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["err_update_message"] = null;
            // 更新成功時にindex.phpにリダイレクト
            header("Location: index.php");
            exit;
            // DBへの接続が失敗した場合
        } catch (PDOException $e) {
            $_SESSION["err_update_message"] = "データベースエラー: " . $e->getMessage();
            header("Location: update_profile.php");
            exit;
        }
        ?>
    <?php } ?>
</body>

</html>
