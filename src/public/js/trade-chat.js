(function() {
    'use strict';

    /**
     * テキストエリアの高さを自動調整します
     * @param {HTMLTextAreaElement} textarea - テキストエリア要素
     */
    function adjustTextareaHeight(textarea) {
        textarea.style.height = 'auto';
        const scrollHeight = textarea.scrollHeight;
        const lineHeight = parseFloat(getComputedStyle(textarea).lineHeight) || parseFloat(getComputedStyle(textarea).fontSize) * 1.5;
        const paddingTop = parseFloat(getComputedStyle(textarea).paddingTop);
        const paddingBottom = parseFloat(getComputedStyle(textarea).paddingBottom);
        const oneLineHeight = lineHeight;
        const maxHeight = oneLineHeight * 5 + paddingTop + paddingBottom;
        textarea.style.height = Math.min(scrollHeight, maxHeight) + 'px';
    }

    /**
     * メッセージ入力をlocalStorageに保持する機能を初期化します
     */
    function initMessageInputPersistence() {
        const messageInput = document.getElementById('message-input');
        if (!messageInput) return;

        const itemId = messageInput.dataset.itemId;
        const userId = messageInput.dataset.userId;
        const storageKey = 'trade_message_input_' + userId + '_' + itemId;

        // 成功メッセージがある場合はlocalStorageとテキストエリアをクリア（メッセージ送信成功時）
        // リダイレクト後のページ読み込み時のみ実行する
        const successMessage = document.querySelector('.trade-chat-page__alert--success');
        if (successMessage) {
            localStorage.removeItem(storageKey);
            // メッセージ送信成功時はテキストエリアもクリア
            messageInput.value = '';
            adjustTextareaHeight(messageInput);
        } else {
            // 成功メッセージがない場合のみ、localStorageから復元を試みる
            const savedMessage = localStorage.getItem(storageKey);
            if (savedMessage && !messageInput.value.trim()) {
                messageInput.value = savedMessage;
                adjustTextareaHeight(messageInput);
            }
        }

        // 入力時にlocalStorageに保存
        messageInput.addEventListener('input', function() {
            localStorage.setItem(storageKey, this.value);
            adjustTextareaHeight(this);
        });

        // フォーム送信時にlocalStorageをクリア（テキストエリアはサーバー側で処理後にクリア）
        // 送信前にテキストエリアをクリアしない（バリデーションエラーを避けるため）
        const messageForm = messageInput.closest('form');
        if (messageForm) {
            messageForm.addEventListener('submit', function(e) {
                // localStorageのみクリア
                localStorage.removeItem(storageKey);
            });
        }

        // 初期表示時の高さ調整
        adjustTextareaHeight(messageInput);
    }

    /**
     * 画像プレビュー機能を初期化します
     */
    function initImagePreview() {
        const imageInput = document.getElementById('image-input');
        if (!imageInput) return;

        const previewContainer = document.getElementById('image-preview-container');
        const previewImg = document.getElementById('image-preview');
        const previewFilename = document.getElementById('image-preview-filename');
        const removeButton = document.getElementById('image-remove-button');

        // 画像選択時の処理
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // ファイルサイズチェック（10MB以下）
                if (file.size > 10 * 1024 * 1024) {
                    alert('画像ファイルは10MB以下にしてください');
                    this.value = '';
                    return;
                }

                // プレビュー画像を表示
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    previewFilename.textContent = file.name;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });

        // 画像削除ボタン
        if (removeButton) {
            removeButton.addEventListener('click', function() {
                imageInput.value = '';
                previewContainer.style.display = 'none';
                previewImg.src = '';
                previewFilename.textContent = '';
            });
        }

        // フォーム送信時にプレビューをクリア
        const messageForm = imageInput.closest('form');
        if (messageForm) {
            messageForm.addEventListener('submit', function() {
                setTimeout(function() {
                    previewContainer.style.display = 'none';
                }, 100);
            });
        }
    }

    /**
     * 画像拡大表示モーダルを開きます
     * @param {string} imageSrc - 画像のURL
     */
    function openImageModal(imageSrc) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('imageModalImg');
        if (modal && modalImg) {
            modalImg.src = imageSrc;
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    /**
     * 画像拡大表示モーダルを閉じます
     */
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    /**
     * 画像クリック時に拡大表示モーダルを開きます（イベント委譲）
     * @param {Event} e - イベントオブジェクト
     */
    document.addEventListener('click', function(e) {
        const imageElement = e.target.closest('.trade-chat-page__message-image-img');
        if (imageElement && imageElement.dataset.imageUrl) {
            openImageModal(imageElement.dataset.imageUrl);
        }
    });

    /**
     * ESCキーが押されたときにモーダルを閉じます
     * @param {Event} e - イベントオブジェクト
     */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });

    // グローバルに公開（onclick属性で使用するため）
    window.openImageModal = openImageModal;
    window.closeImageModal = closeImageModal;

    /**
     * 評価モーダル表示機能を初期化します
     */
    function initEvaluationModal() {
        const evaluationModal = document.getElementById('evaluationModal');
        const tradeChatPage = document.querySelector('.trade-chat-page');
        const needsEvaluationData = tradeChatPage && tradeChatPage.dataset.needsEvaluation === 'true';

        if (evaluationModal && needsEvaluationData) {
            evaluationModal.classList.add('show');
        }
    }

    /**
     * メッセージ編集機能を初期化します
     */
    function initMessageEdit() {
        const editButtons = document.querySelectorAll('.trade-chat-page__message-action--edit');
        const tradeChatPage = document.querySelector('.trade-chat-page');

        if (!tradeChatPage) return;

        const updateRouteTemplate = tradeChatPage.dataset.updateRouteTemplate;
        const csrfToken = tradeChatPage.dataset.csrfToken;

        if (!updateRouteTemplate || !csrfToken) {
            console.warn('必要なデータ属性が見つかりません。メッセージ編集に失敗しました。');
            return;
        }

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const messageId = this.getAttribute('data-message-id');
                const messageText = this.getAttribute('data-message-text');
                const messageElement = this.closest('.trade-chat-page__message');
                const messageTextElement = messageElement.querySelector('.trade-chat-page__message-text');
                const actionsElement = messageElement.querySelector('.trade-chat-page__message-actions');

                // 編集フォームを作成
                const editForm = document.createElement('form');
                editForm.action = updateRouteTemplate.replace(':messageId', messageId);
                editForm.method = 'POST';
                editForm.className = 'trade-chat-page__edit-form';
                editForm.innerHTML = `
                    <input type="hidden" name="_token" value="${escapeHtml(csrfToken)}">
                    <input type="hidden" name="_method" value="PUT">
                    <textarea name='message' class='trade-chat-page__edit-input' required>${escapeHtml(messageText)}</textarea>
                    <div class='trade-chat-page__edit-actions'>
                        <button type='submit' class='trade-chat-page__edit-submit'>更新</button>
                        <button type='button' class='trade-chat-page__edit-cancel'>キャンセル</button>
                    </div>
                `;

                // 元のメッセージを非表示にして編集フォームを表示
                messageTextElement.style.display = 'none';
                actionsElement.style.display = 'none';
                messageTextElement.after(editForm);

                // キャンセルボタンの処理
                editForm.querySelector('.trade-chat-page__edit-cancel').addEventListener('click', function() {
                    editForm.remove();
                    messageTextElement.style.display = '';
                    actionsElement.style.display = '';
                });
            });
        });
    }

    /**
     * HTMLエスケープを行います
     * @param {string} text - エスケープするテキスト
     * @returns {string} エスケープされたテキスト
     */
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * 初期化します
     */
    document.addEventListener('DOMContentLoaded', function() {
        initMessageInputPersistence();
        initImagePreview();
        initEvaluationModal();
        initMessageEdit();
    });
})();

