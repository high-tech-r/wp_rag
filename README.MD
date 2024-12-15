# WordPress Chat Window Plugin with RAG Integration

## 概要
このリポジトリは以下の2つのコンポーネントで構成されています：

1. **chat-window-plugin**
   - チャットボットのユーザーインターフェース（UI）を提供するWordPressプラグインです。
   - フロントエンドのデザインとインタラクションを担当しますが、サーバーサイドの通信は処理しません。

2. **wp_rag**
   - OpenAIのChatGPT APIとの通信を処理するサーバーサイドプログラムです。
   - MySQLデータベースを使用してベクトル類似度計算を行い、関連するブログ投稿を取得します。
   - vectorize_posts.phpはcronで定期実行させると投稿をベクトル化してDBに保存します。

## 機能
- WordPressにシームレスに統合されたチャットウィンドウUI。
- ユーザーの問い合わせを処理し、Retrieval-Augmented Generation (RAG) を利用して関連するブログ投稿を取得。
- ベクトル計算やOpenAI APIとの通信を行うサーバーサイドプログラム。

## 前提条件
### WordPress設定
- WordPressのインストールと管理者アクセス。
- ブログデータを保存するMySQLデータベース。

### サーバー要件
- PHP (>=7.4)
- Python (>=3.6)と必要なライブラリがインストールされていること。
- ベクトル計算を行うためのMySQLデータベースとストアドプロシージャ。

## インストール

### 1. チャットウィンドウプラグインのインストール
1. `chat-window-plugin`ディレクトリをWordPressの`wp-content/plugins/`ディレクトリにコピー。
2. WordPress管理パネルからプラグインを有効化。

### 2. wp_ragサーバープログラムのセットアップ
1. `wp_rag`ディレクトリをサーバーに配置。
2. 以下の依存関係が満たされていることを確認：
   - Pythonの必要なライブラリ（例：`numpy`、`scipy`）をインストール。
   - MySQLデータベースを設定し、付属のストアドプロシージャSQLスクリプトを実行。
3. `.env`ファイルを設定：
   ```
   DB_HOST=your_database_host
   DB_NAME=your_database_name
   DB_USER=your_database_user
   DB_PASSWORD=your_database_password
   OPENAI_API_KEY=your_openai_api_key
   ```
4. サーバーがPythonスクリプトを実行できることを確認。
5. post_vectors_create_table.sqlを実行してMySQLの任意のDBへテーブルを作成。

## 使用方法
1. プラグインを有効化すると、WordPressサイトのフロントエンドにチャットボットウィンドウが表示される。
2. ユーザーがクエリを送信すると、プラグインはそのクエリと現在のブログ投稿ID（オプション）を`wp_rag`サーバープログラムに送信。
3. サーバープログラムは次を行う：
   - ベクトル類似度を使用して最も関連性の高いブログ投稿を特定。
   - ユーザーのクエリと関連するブログコンテンツをChatGPTに渡し、回答を生成。
4. チャットボットウィンドウに回答を表示し、関連ブログ投稿へのリンクを提供（該当する場合）。
5. チャットボットウィンドウに回答を表示し、関連ブログ投稿へのリンクを提供（該当する場合）。

## 注意事項
- `chat-window-plugin`はUIのみを提供します。`wp_rag`サーバーサイドプログラムがなければチャットボットは機能しません。
- Pythonベースのベクトル計算を処理するために、サーバーに十分なリソースが必要です。特に大規模データセットの場合は注意してください。

## コントリビューション
改良やバグ修正について、プルリクエストや課題を歓迎します。

## ライセンス
このプロジェクトはMITライセンスの下でライセンスされています。詳細は`LICENSE`ファイルを参照してください。