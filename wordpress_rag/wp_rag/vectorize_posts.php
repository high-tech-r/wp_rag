<?php

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST']; // MySQLホスト
$db = $_ENV['DB_NAME'];   // データベース名
$user = $_ENV['DB_USER'];       // MySQLユーザー名
$password = $_ENV['DB_PASSWORD']; // MySQLパスワード
echo shell_exec("which python3");
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ベクトル化されていない投稿を取得
    $stmt = $pdo->query("
        SELECT ID, post_content 
        FROM wp_posts 
        WHERE post_status = 'publish' 
        AND ID NOT IN (SELECT post_id FROM post_vectors)
    ");

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $postId = $row['ID'];
        $content = $row['post_content'];

        // 投稿内容をサニタイズして文字数を制限
        $trimmedContent = mb_substr(strip_tags($content), 0, 1000);

        if (empty($trimmedContent)) {
            echo "Post ID $postId skipped: empty content.\n";
            continue;
        }

        // Pythonスクリプトを呼び出す
        $command = escapeshellcmd("python3 generate_vector.py " . escapeshellarg($trimmedContent));
        $output = shell_exec($command);

        // Pythonスクリプトの出力をデコード
        $result = json_decode($output, true);

        if (isset($result['error'])) {
            echo "Post ID $postId skipped: " . $result['error'] . "\n";
            continue;
        }

        if (!isset($result['vector'])) {
            echo "Post ID $postId skipped: no vector returned.\n";
            continue;
        }

        // ベクトルをJSON形式で取得
        $vector = json_encode($result['vector']);

        // ベクトルをpost_vectorsテーブルに保存
        $insertStmt = $pdo->prepare("
            INSERT INTO post_vectors (post_id, vector) 
            VALUES (:post_id, :vector)
        ");
        $insertStmt->bindParam(':post_id', $postId, PDO::PARAM_INT);
        $insertStmt->bindParam(':vector', $vector, PDO::PARAM_STR);
        $insertStmt->execute();

        echo "Post ID $postId vectorized and saved.\n";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    echo "General error: " . $e->getMessage();
}