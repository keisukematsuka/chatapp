<?php
session_start();

// データベースに接続する関数
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

// 検索キーワードを取得
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    $conn = getDB();
    // message,image_pass,nameを選択
    $sql = "SELECT messages.message, messages.image_path, users.name FROM messages INNER JOIN users ON messages.users_id = users.id";

    // 検索キーワードがあれば条件を追加
    if (!empty($search)) {
        // messageかnameが一致すれば$sqlに加える
        $sql .= " WHERE messages.message LIKE :search OR users.name LIKE :search";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    } else {
        $stmt = $conn->prepare($sql);
    }

    $stmt->execute();

    // 検索結果を配列で取得
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "エラー: " . $e->getMessage();
} finally {
    $conn = null;
}

// 検索結果をセッションに保存
$_SESSION['search_results'] = $search_results;

// 検索結果を表示
if (empty($search_results)) {
    echo "検索結果はありません。";
} else {
    foreach ($search_results as $result) {
        echo "<p><strong>" . $result['name'] . ":</strong> " . $result['message'] . "</p>";
        if (!empty($result['image_path'])) {
            echo '<img src="' . $result['image_path'] . '" alt="Chat Image">';
        }
    }
}
