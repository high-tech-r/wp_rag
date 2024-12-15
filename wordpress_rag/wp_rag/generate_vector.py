import sys
import json
from sentence_transformers import SentenceTransformer

# モデルのロード
model = SentenceTransformer('all-MiniLM-L6-v2')

# テキストを引数として受け取る
if len(sys.argv) < 2:
    print(json.dumps({"error": "No input text provided"}))
    sys.exit(1)

text = sys.argv[1]

# ベクトル化処理
try:
    embedding = model.encode(text).tolist()
    print(json.dumps({"vector": embedding}))
except Exception as e:
    print(json.dumps({"error": str(e)}))