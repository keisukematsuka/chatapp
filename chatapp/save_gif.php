<?php
session_start();

require_once 'sql_connection.php';  // データベース接続情報を含むファイル

// セッションから user_id を取得する際のエラーチェック
if (!isset($_SESSION["id"])) {
    die('セッションが設定されていません。ログインが必要です。');
}
$userId = $_SESSION["id"];
$gifUrl = $_GET['url'] ?? '';  // URLパラメータからGIFのURLを取得


if (!empty($gifUrl)) {
    try {
        $pdo = getDB();  // データベース接続を取得
        $sql = "INSERT INTO messages (users_id, gif) VALUES (:userId, :gifUrl)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['userId' => $userId, 'gifUrl' => $gifUrl]);
    } catch (PDOException $e) {
        die("データベースへの保存中にエラーが発生しました: " . $e->getMessage());
    }
}

// index.phpにリダイレクト
header('Location: index.php');
exit;
