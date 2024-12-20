<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

// .env ファイルのロード
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
use GuzzleHttp\Client;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;

header('Content-Type: application/json');
    
// リクエストメソッドの確認
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// オーディオファイルの確認
if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Audio file is missing or invalid']);
    exit;
}

// 環境変数からAPIキーを取得
$openaiApiKey = $_ENV['OPENAI_API_KEY'];
$googleCredentialsPath = '/var/www/html/wp_rag/dotted-nature-00000.json'; // GCPで作成した設定ファイルのパス


    
// 音声ファイル保存ディレクトリを設定
$audioFilePath = $_FILES['audio']['tmp_name'];
$saveDir = __DIR__ . '/generated_audio/';
if (!file_exists($saveDir)) {
    mkdir($saveDir, 0777, true);
}
$savePath = $saveDir . uniqid('audio_', true) . '.mp3';


try {
    // Whisper APIで文字起こし
    $client = new Client();
    $response = $client->request('POST', 'https://api.openai.com/v1/audio/transcriptions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $openaiApiKey,
        ],
        'multipart' => [
            [
                'name' => 'file',
                'contents' => fopen($audioFilePath, 'r'),
                'filename' => 'audio.webm'
            ],
            [
                'name' => 'model',
                'contents' => 'whisper-1'
            ]
        ]
    ]);


    $transcription = json_decode($response->getBody(), true)['text'];


    // POSTの内容取得
    $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : null;

    $postContent = '';

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
    }

    // ChatGPT APIで会話生成
    $chatResponse = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $openaiApiKey,
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'あなたはブログに設置してある、有能で親切なアシスタントのチャットボット、パグ蔵だワン！語尾に「わん」とつけてね。回答は200文字以内で、一人称は「ぼく」フレンドリーでかわいい感じにして。ちょっと難しい質問でも、一緒に楽しみながら解決してね！'],
//                ['role' => 'user', 'content' => $transcription]
                ['role' => 'user', 'content' => "User Query:\n{$transcription}\n\nBlog Post Content:\n{$postContent}"]
            ],
        ],
    ]);

    $chatText = json_decode($chatResponse->getBody(), true)['choices'][0]['message']['content'];

    // Google Cloud TTS APIで音声を生成
    if (!file_exists($googleCredentialsPath)) {
        throw new Exception("Google Cloud credentials file not found at $googleCredentialsPath");
    }
    putenv("GOOGLE_APPLICATION_CREDENTIALS=$googleCredentialsPath");

    $ttsClient = new TextToSpeechClient();
    
    $inputText = (new SynthesisInput())
        ->setText($chatText);

    $voice = (new VoiceSelectionParams())
        ->setLanguageCode('ja-JP')
        ->setSsmlGender(SsmlVoiceGender::NEUTRAL);

    $audioConfig = (new AudioConfig())
        ->setAudioEncoding(AudioEncoding::MP3);

    $ttsResponse = $ttsClient->synthesizeSpeech($inputText, $voice, $audioConfig);
    file_put_contents($savePath, $ttsResponse->getAudioContent());

    $ttsClient->close();

    echo json_encode(['audio_url' => '/wp_rag/generated_audio/' . basename($savePath)]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
