<?php
session_start();

// データベースに接続する関数

require_once "sql_connection.php";

// セッションに "token" キーを設定する
if (!isset($_SESSION["token"])) {
    $_SESSION["token"] = uniqid(); // 一意のトークンを生成して設定する
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>チャットアプリ</title>
    <link rel="stylesheet" type="text/css" href="./css/style.css">
    <meta charset="utf-8">
</head>

<body>
    <div class="container">
        <!-- ユーザー情報 -->
        <div class="left">
            <h2>ユーザー情報</h2>
            <!-- 特殊文字の入力を防ぐ -->
            <p>ユーザー名：<?= isset($_SESSION["name"]) ? htmlspecialchars($_SESSION["name"], ENT_QUOTES, 'UTF-8') : '未設定'; ?></p>
            <p>メールアドレス：<?= isset($_SESSION["email"]) ? $_SESSION["email"] : '未設定'; ?></p>
            <a href="update_profile.php" class="user-button">ユーザー情報を変更する</a>
            <a href="login.php" class="user-button login">ログイン</a>
            <a href="logout.php" class="user-button logout">ログアウト</a>
        </div>

        <div class="right">
            <h2>チャット</h2>

            <!-- 検索フォーム -->
            <form action="search.php" method="get" class="search-form" target="_blank">
                <label for="search">検索:</label>
                <input type="text" id="search" name="search" placeholder="キーワードを入力">
                <button type="submit">検索</button>
            </form>

            <!-- チャットのテキストエリア -->
            <div class="chat-box">
                <?php
                try {
                    $conn = getDB();

                    // メッセージと画像を取得
                    $sql = "SELECT messages.message, messages.image_path, messages.gif, messages.date, users.name FROM messages INNER JOIN users ON messages.users_id = users.id ORDER BY messages.id";
                    $result = $conn->query($sql);


                    // メッセージの投稿がある場合
                    if ($result->rowCount() > 0) {
                        // DBにあるすべての投稿を表示する
                        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                            $formattedDate = date('Y-m-d H:i:s', strtotime($row['date']));  // 日時をフォーマット

                            // ユーザー名とメッセージの表示
                            echo "<p><strong>" . htmlspecialchars($row['name']) . ":</strong> " . htmlspecialchars($row['message']) . "</p>";

                            // 日時を別の行で表示
                            echo "<div style='color: gray; font-size: 0.8em; margin-top: -10px;'> " . $formattedDate . "</div>";

                            // 画像がある場合は表示
                            if (!empty($row['image_path'])) {
                                echo '<img src="' . htmlspecialchars($row['image_path']) . '" alt="Chat Image" style="max-width: 100%; height: auto; display: block; margin-top: 10px;">';
                            }

                            // GIFがある場合は表示
                            if (!empty($row['gif'])) {
                                echo '<img src="' . htmlspecialchars($row['gif']) . '" alt="chat giphy" style="max-width: 100%; height: auto; display: block; margin-top: 10px;">';
                            }
                        }

                        // DBに投稿がない場合
                    } else {
                        echo "<p>メッセージがありません</p>";
                    }
                } catch (PDOException $e) {
                    echo "エラー: " . $e->getMessage();
                } finally {
                    $conn = null;
                }
                ?>
            </div>

            <!-- メッセージの入力フォーム -->
            <form action="send_message.php" method="post" enctype="multipart/form-data" class="chat-form">
                <input type="text" id="message" name="message" placeholder="メッセージを入力">
                <label for="image">ファイルを選択</label>
                <input type="file" id="image" name="image" accept="image/*">
                <button type="submit">送信</button>
            </form>



            <!-- giphy.phpへのリンクボタン -->
            <a href="giphy.php" class="link-button">Giphy</a>
        </div>
    </div>
    <script>
        // チャットボックスが常に下にスクロールされるようにする関数
        window.onload = () => {
            let chatBox = document.querySelector(".chat-box");
            chatBox.scrollTop = chatBox.scrollHeight;
        };
    </script>

</body>

</html>
