<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;

// .env ファイルのロード
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// 環境変数の読み込み
$apiKey = $_ENV['OPENAI_API_KEY'];
$apiUrl = 'https://api.openai.com/v1/chat/completions';

// POSTリクエストでのみ処理
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// リクエストの内容を取得
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['query'])) {
    echo json_encode(['status' => 'error', 'message' => 'Query is missing or empty']);
    exit;
}

$postId = isset($input['post_id']) ? (int) $input['post_id'] : null;
$userQuery = $input['query'];
$postContent = '';

try {
    // データベース接続
    $dbHost = $_ENV['DB_HOST'];
    $dbName = $_ENV['DB_NAME'];
    $dbUser = $_ENV['DB_USER'];
    $dbPassword = $_ENV['DB_PASSWORD'];

    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($postId) {
        // Post ID が指定されている場合のポスト内容を取得
        $stmt = $pdo->prepare("SELECT post_title, post_content FROM wp_posts WHERE ID = :id");
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            $postContent = "Post Title: {$post['post_title']}\nPost Content: {$post['post_content']}";
        }
    } else {
        // Post ID が指定されていない場合の相関性の高いポスト内容を取得
        $command = escapeshellcmd("python3 " . __DIR__ . "/generate_vector.py " . escapeshellarg($userQuery));
        $output = shell_exec($command);

        if ($output === null) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to execute Python script']);
            exit;
        }

        $result = json_decode($output, true);

        if (isset($result['error'])) {
            echo json_encode(['status' => 'error', 'message' => $result['error']]);
            exit;
        }

        if (empty($result['vector'])) {
            echo json_encode(['status' => 'error', 'message' => 'No vector returned from Python script']);
            exit;
        }

        $queryVector = json_encode($result['vector']);

        $stmt = $pdo->prepare("CALL GetMostSimilarPost(:input_vector, @result_id)");
        $stmt->bindParam(':input_vector', $queryVector, PDO::PARAM_STR);
        $stmt->execute();

        $result = $pdo->query("SELECT @result_id AS result_id")->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['result_id']) {
            $stmt = $pdo->prepare("SELECT post_title, post_content FROM wp_posts WHERE ID = :id");
            $stmt->bindParam(':id', $result['result_id'], PDO::PARAM_INT);
            $stmt->execute();
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($post) {
                $postContent = "Post Title: {$post['post_title']}\nPost Content: {$post['post_content']}";
            }
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}

// ChatGPT API へのリクエスト
$data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'system', 'content' => 'あなたはブログに設置してある、有能で親切なアシスタントのチャットボット、パグ蔵だワン！語尾に「わん」とつけてね。回答は200文字以内で、一人称は「ぼく」フレンドリーでかわいい感じにして。ちょっと難しい質問でも、一緒に楽しみながら解決してね！'],
        ['role' => 'user', 'content' => "User Query:\n{$userQuery}\n\nBlog Post Content:\n{$postContent}"]
    ],
    'temperature' => 0.7,
    'max_tokens' => 500,
];

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n" .
                    "Authorization: Bearer {$apiKey}\r\n",
        'method' => 'POST',
        'content' => json_encode($data),
    ]
];

$context = stream_context_create($options);
$response = @file_get_contents($apiUrl, false, $context);

if ($response === false) {
    $error = error_get_last();
    echo json_encode(['status' => 'error', 'message' => 'Failed to connect to OpenAI API', 'details' => $error['message']]);
    exit;
}

$responseData = json_decode($response, true);

if (isset($responseData['error'])) {
    echo json_encode(['status' => 'error', 'message' => 'ChatGPT API returned an error', 'details' => $responseData['error']]);
    exit;
}

echo json_encode(['status' => 'success', 'response' => $responseData]);
