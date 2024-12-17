// WordPress Chat Window with RAG Integration

(function() {
    const chatWindow = document.createElement('div');
    chatWindow.id = 'chat-window';
    chatWindow.style.position = 'fixed';
    chatWindow.style.bottom = '20px';
    chatWindow.style.right = '20px';
    chatWindow.style.width = '300px';
    chatWindow.style.height = '400px';
    chatWindow.style.border = '1px solid #ccc';
    chatWindow.style.borderRadius = '10px';
    chatWindow.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
    chatWindow.style.backgroundColor = '#fff';
    chatWindow.style.overflow = 'hidden';
    chatWindow.style.zIndex = '9999';
    chatWindow.style.display = 'block'; // Initially visible
    chatWindow.style.paddingBottom = '50px'; // Adjust for input field

    const chatHeader = document.createElement('div');
    chatHeader.style.backgroundColor = '#0073aa';
    chatHeader.style.color = '#fff';
    chatHeader.style.padding = '10px';
    chatHeader.style.fontSize = '16px';
    chatHeader.style.textAlign = 'center';
    chatHeader.style.cursor = 'pointer';
    chatHeader.textContent = window.location.href.includes('?p=') ? 'ブログの記事について質問してね' : 'なんでも聞いてね！';
    chatWindow.appendChild(chatHeader);

    const chatBody = document.createElement('div');
    chatBody.id = 'chat-body';
    chatBody.style.padding = '10px';
    chatBody.style.height = 'calc(100% - 60px)'; // Adjust height
    chatBody.style.overflowY = 'auto'; // Enable scrolling
    chatBody.style.fontSize = '14px';
    chatBody.style.boxSizing = 'border-box';
    chatWindow.appendChild(chatBody);

    const chatInput = document.createElement('input');
    chatInput.type = 'text';
    chatInput.id = 'chat-input';
    chatInput.placeholder = 'ここに質問を書いてね！';
    chatInput.style.width = 'calc(100% - 20px)';
    chatInput.style.margin = '10px';
    chatInput.style.padding = '8px';
    chatInput.style.fontSize = '14px';
    chatInput.style.border = '1px solid #ccc';
    chatInput.style.borderRadius = '4px';
    chatInput.style.boxSizing = 'border-box';
    chatInput.style.position = 'absolute';
    chatInput.style.bottom = '10px';
    chatWindow.appendChild(chatInput);

    document.body.appendChild(chatWindow);
    
    
    // Pugzo image container
    const pugzoContainer = document.createElement('div');
    pugzoContainer.style.position = 'fixed';
    pugzoContainer.style.bottom = '20px';
    pugzoContainer.style.right = '80px';
    pugzoContainer.style.width = '70px';
    pugzoContainer.style.height = '70px';
    pugzoContainer.style.zIndex = '10000';

    const pugzoImage = document.createElement('img');
    pugzoImage.src = 'https://chatterboxvr.com/wordpress/wp-content/uploads/2024/11/unnamed.png';
    pugzoImage.alt = 'パグ蔵';
    pugzoImage.style.width = '100%';
    pugzoImage.style.borderRadius = '50%';
    pugzoImage.style.cursor = 'pointer';

    const tooltipText = document.createElement('div');
    tooltipText.textContent = '🐾 パグ蔵だよ！';
    tooltipText.style.visibility = 'hidden';
    tooltipText.style.width = '150px';
    tooltipText.style.backgroundColor = '#ffde59'; // 明るい黄色
    tooltipText.style.color = '#333';
    tooltipText.style.textAlign = 'center';
    tooltipText.style.padding = '10px';
    tooltipText.style.borderRadius = '10px';
    tooltipText.style.fontSize = '14px';
    tooltipText.style.fontFamily = "'Comic Sans MS', cursive"; 
    tooltipText.style.boxShadow = '0px 4px 8px rgba(0, 0, 0, 0.2)';
    tooltipText.style.position = 'absolute';
    tooltipText.style.bottom = '110%'; // 画像の上に表示
    tooltipText.style.left = '50%';
    tooltipText.style.transform = 'translateX(-50%)';
    tooltipText.style.zIndex = '1';
    tooltipText.style.opacity = '0';
    tooltipText.style.transition = 'opacity 0.5s ease, transform 0.3s ease';

    // 吹き出しの三角形
    tooltipText.style.content = '';
    tooltipText.style.display = 'inline-block';
    tooltipText.setAttribute('data-tooltip', 'パグ蔵は考え中…');

    pugzoContainer.appendChild(pugzoImage);
    pugzoContainer.appendChild(tooltipText);
    document.body.appendChild(pugzoContainer);

    pugzoContainer.addEventListener('mouseover', () => {
        tooltipText.style.visibility = 'visible';
        tooltipText.style.opacity = '1';
        tooltipText.textContent = getRandomPugzoMessage();
    });
    pugzoContainer.addEventListener('mouseout', () => {
        tooltipText.style.visibility = 'hidden';
        tooltipText.style.opacity = '0';
    });

    function getRandomPugzoMessage() {
        const messages = [
            '🐾 今日はいい天気だね！',
            '🐾 おっと、何か質問かな？',
            '🐾 ご主人様、どうしましたか？',
            '🐾 モフモフしていきます？'
        ];
        return messages[Math.floor(Math.random() * messages.length)];
    }

    
    const toggleButton = document.createElement('div');
    toggleButton.id = 'chat-toggle';
    toggleButton.style.position = 'fixed';
    toggleButton.style.bottom = '20px';
    toggleButton.style.right = '20px';
    toggleButton.style.width = '50px';
    toggleButton.style.height = '50px';
    toggleButton.style.borderRadius = '50%';
    toggleButton.style.backgroundColor = '#0073aa';
    toggleButton.style.color = '#fff';
    toggleButton.style.textAlign = 'center';
    toggleButton.style.lineHeight = '50px';
    toggleButton.style.cursor = 'pointer';
    toggleButton.style.zIndex = '10000';
    toggleButton.textContent = '-'; // Initially open
    document.body.appendChild(toggleButton);

    // ページ読み込み時に状態を復元
    const chatState = localStorage.getItem('chatWindowState');
    if (chatState === 'open') {
        chatWindow.style.display = 'block';
        toggleButton.textContent = '-';
    }else if(chatState === 'closed'){
        chatWindow.style.display = 'none';
        toggleButton.textContent = '+';
    }
    
    toggleButton.addEventListener('click', function() {
        if (chatWindow.style.display === 'none') {
            chatWindow.style.display = 'block';
            toggleButton.textContent = '-';
            localStorage.setItem('chatWindowState', 'open'); // 状態を保存
        } else {
            chatWindow.style.display = 'none';
            toggleButton.textContent = '+';
            localStorage.setItem('chatWindowState', 'closed'); // 状態を保存
        }
    });

    function scrollToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }
    
    // URLから投稿IDを抽出する関数
    function getPostIdFromUrl() {
        const url = window.location.href; // 現在のURLを取得
        const match = url.match(/p=(\d+)/); // 正規表現で投稿IDを抽出
        return match ? match[1] : null; // 見つかった場合はIDを返し、なければnull
    }
    
    
    // Loading indicator
    const loadingIndicator = document.createElement('div');
    loadingIndicator.id = 'loading-indicator';
    loadingIndicator.style.display = 'none'; // Initially hidden
    loadingIndicator.style.textAlign = 'left';
    loadingIndicator.style.position = 'absolute';
    loadingIndicator.style.bottom = '60px'; // Position above the input field
    loadingIndicator.style.width = '100%';

    const loadingGif = document.createElement('img');
    loadingGif.src = 'https://chatterboxvr.com/wordpress/pug.gif';
    loadingGif.alt = 'Loading...';
    loadingGif.style.width = '100px';
    loadingIndicator.appendChild(loadingGif);
    chatBody.appendChild(loadingIndicator);
    
    const loadingText = document.createElement('div');
    loadingText.textContent = '考え中...';
    loadingText.style.marginTop = '0px';
    loadingText.style.marginLeft = '20px';
    loadingText.style.color = '#333';
    loadingText.style.fontSize = '12px';
    loadingIndicator.appendChild(loadingText);

    chatWindow.appendChild(loadingIndicator);
    
chatInput.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        let currentPostId = getPostIdFromUrl();
        const postId = currentPostId || null; // 現在の投稿IDを保持している変数
        const userMessage = chatInput.value.trim();
        if (userMessage) {
            const userMessageDiv = document.createElement('div');
            userMessageDiv.textContent = userMessage;
            userMessageDiv.style.margin = '10px 0';
            userMessageDiv.style.textAlign = 'right';
            userMessageDiv.style.color = '#0073aa';
            chatBody.appendChild(userMessageDiv);

            chatInput.value = '';
            scrollToBottom();

            // Show loading indicator
            loadingIndicator.style.display = 'block';
            fetch('https://chatterboxvr.com/wp_rag/rag_endpoint.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query: userMessage, post_id: postId })
            })
            .then(response => response.json())
            .then(data => {
                loadingIndicator.style.display = 'none'; // Hide loading indicator
                if (data.status === 'success') {
                    let botMessage = data.response.choices[0].message.content || 'No response from ChatGPT';
                    // ChatGPTの応答を表示
                    
                    // Extract URL from response
                    const botMessageDiv = document.createElement('div');
                    const urlPattern = /\[URL_START\](.*?)\[URL_END\]/;
                    const urlMatch = botMessage.match(urlPattern);
                    let url = null;

                    if (urlMatch && urlMatch[1]) {
                        url = urlMatch[1];
                        botMessage = botMessage.replace(urlPattern, '');
                    }
                    
                    botMessageDiv.textContent = data.response.choices[0].message.content || 'No response from ChatGPT';
                    botMessageDiv.textContent = botMessage;
                    botMessageDiv.style.margin = '10px 0';
                    botMessageDiv.style.textAlign = 'left';
                    botMessageDiv.style.color = '#333';
                    
                    
                    
                    
                    
                    // pubzo start
                    botMessageDiv.style.padding = '10px';
                    botMessageDiv.style.border = '1px solid #ccc';
                    botMessageDiv.style.borderRadius = '10px';
                    botMessageDiv.style.backgroundColor = '#f9f9f9';
                    // pugzo end
                    
                    
                    
                    chatBody.appendChild(botMessageDiv);
/*
                    // 関連投稿があればリンクを表示
                    if (data.response.related_post) {
                        const relatedPostDiv = document.createElement('div');
                        relatedPostDiv.style.margin = '10px 0';

                        const postLink = document.createElement('a');
                        postLink.href = data.response.related_post.url; // サーバーから返ってくる関連投稿のURL
                        postLink.textContent = `Related Post: ${data.response.related_post.title}`;
                        postLink.style.color = '#0073aa';
                        postLink.style.display = 'block';
                        postLink.target = '_blank'; // Open link in new tab
                        relatedPostDiv.appendChild(postLink);

                        chatBody.appendChild(relatedPostDiv);
                    }
*/
                    if (url) {
                        const urlDiv = document.createElement('div');
                        const urlLink = document.createElement('a');
                        urlLink.href = url;
                        urlLink.textContent = `[関連しているかもしれないURL] ${url}`;
                        urlLink.style.color = '#0073aa';
                        urlLink.target = '_blank';
                        urlDiv.appendChild(urlLink);
                        chatBody.appendChild(urlDiv);
                    }

                    scrollToBottom();
                } else {
                    console.error('Error in response:', data);
                    const errorDiv = document.createElement('div');
                    errorDiv.textContent = 'Something went wrong. Please try again.';
                    errorDiv.style.margin = '10px 0';
                    errorDiv.style.textAlign = 'left';
                    errorDiv.style.color = 'red';
                    chatBody.appendChild(errorDiv);

                    scrollToBottom();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorDiv = document.createElement('div');
                errorDiv.textContent = 'An error occurred while communicating with the server.';
                errorDiv.style.margin = '10px 0';
                errorDiv.style.textAlign = 'left';
                errorDiv.style.color = 'red';
                chatBody.appendChild(errorDiv);

                scrollToBottom();
            });
        }
    }
});

    function getPostIdFromUrl() {
        const url = window.location.href;
        const match = url.match(/p=(\d+)/);
        return match ? match[1] : null;
    }

    let currentPostId = getPostIdFromUrl();
})();
