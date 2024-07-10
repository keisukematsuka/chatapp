<?php
session_start();

// データベースに接続する関数
function getDB(): PDO
{
    $dsn = 'mysql:dbname=webapp; host=127.0.0.1; charset=utf8';
    $usr = 'root';
    $password = '';
    $db = new PDO($dsn, $usr, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
}

// メッセージのテキスト情報をデータベースに挿入
$message = $_POST["message"];
$token = $_POST["token"];
// 送られてきたtokenが一致しない場合
$users_id = $_SESSION["id"];

// データベースへの登録が成功した場合
try {
    $conn = getDB();

    // 画像の処理
    $image_path = null;
    // フォームを通じてアップロードされた画像がエラーなくサーバーに送信されたかどうかを確認
    if ($_FILES["image"]["error"] === UPLOAD_ERR_OK) {
        // アップロードされた画像の一時ファイルパス
        $tmp_file = $_FILES["image"]["tmp_name"];

        // 画像のリサイズ
        // getimagesize() 関数は画像ファイルから幅と高さを取得
        list($original_width, $original_height) = getimagesize($tmp_file);
        // 新しい縦×横のサイズを指定
        $new_width = 300;
        $new_height = 300;
        // imagecreatetruecolor() 関数は新しい真色の画像を作成
        $resized_image = imagecreatetruecolor($new_width, $new_height);

        // 元画像を読み込む
        // mime_content_type()ファイルのタイプ調べる為の関数
        $mime_type = mime_content_type($tmp_file);
        if ($mime_type == 'image/jpeg') {
            $original_image = imagecreatefromjpeg($tmp_file);
        } elseif ($mime_type == 'image/png') {
            $original_image = imagecreatefrompng($tmp_file);
        } elseif ($mime_type == 'image/gif') {
            $original_image = imagecreatefromgif($tmp_file);
        } else {
            // 未対応の形式の場合はエラーを返すか、適切な処理を行う
            die("Unsupported image format");
        }

        // 元画像からリサイズした画像を生成
        imagecopyresampled($resized_image, $original_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

        // リサイズした画像を保存
        // 保存場所
        $uploadDir = 'uploads/';
        // 保存場所のパスを作成
        $uploadFile = $uploadDir . basename($_FILES['image']['name']);
        imagejpeg($resized_image, $uploadFile);

        // メモリの解放
        imagedestroy($original_image);
        imagedestroy($resized_image);

        // 保存した画像のパスを取得
        $image_path = $uploadFile;
    }

    // メッセージをデータベースに挿入
    $stmt = $conn->prepare("INSERT INTO messages (message, image_path, users_id) VALUES (?, ?, ?)");
    $stmt->execute([$message, $image_path, $users_id]);

    header("Location: index.php");
    exit;
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
}
