<?php
// Giphy APIのキー
$apiKey = 'JM0rDDf5k5AXOF3BQSYKtGLqU1e55QJP';

// 検索キーワード
$searchQuery = isset($_GET['query']) ? $_GET['query'] : 'funny cats'; // ユーザーの入力があればそれを使い、なければ初期値を使用

// cURLを使用してGiphy APIを呼び出す
function fetchGifs($apiKey, $query)
{
    $url = "https://api.giphy.com/v1/gifs/search?api_key={$apiKey}&q=" . urlencode($query) . "&limit=30&offset=";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

// GIFデータを取得
$gifs = fetchGifs($apiKey, $searchQuery);

// HTMLとして表示
echo '<div style="display:flex; flex-wrap:wrap;">';
foreach ($gifs['data'] as $gif) {
    echo '<div style="margin:10px;">';
    echo '<a href="save_gif.php?url=' . urlencode($gif['images']['fixed_height']['url']) . '">';
    echo '<img src="' . $gif['images']['fixed_height']['url'] . '" alt="GIF" style="width:150px; height:150px;">';
    echo '</a>';
    echo '</div>';
}
echo '</div>';
?>

<!-- 検索フォーム -->
<form method="get">
    <input type="text" name="query" placeholder="Search for GIFs">
    <button type="submit">Search</button>
</form>
