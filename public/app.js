const { createApp, reactive, ref, nextTick, onBeforeUnmount } = Vue;

createApp({
    setup() {
        // 상태 관리
        const serverUrl = ref('ws://localhost:8888/chat');
        const clientCount = ref(2);
        const clients = reactive([]);
        let clientIdCounter = 0;
        const messagesRefs = {};

        /**
         * 메시지 컨테이너 참조 설정
         */
        const setMessagesRef = (el, clientId) => {
            if (el) {
                messagesRefs[clientId] = el;
            }
        };

        /**
         * 메시지 목록 하단으로 스크롤
         */
        const scrollToBottom = (clientId) => {
            nextTick(() => {
                const el = messagesRefs[clientId];
                if (el) {
                    el.scrollTop = el.scrollHeight;
                }
            });
        };

        /**
         * 메시지 추가
         */
        const addMessage = (client, content, type = 'system') => {
            const time = new Date().toLocaleTimeString('ko-KR');
            client.messages.push({ content, type, time });
            scrollToBottom(client.id);
        };

        /**
         * 새 클라이언트 생성
         */
        const createClient = () => {
            const id = ++clientIdCounter;
            const client = reactive({
                id,
                connected: false,
                sentCount: 0,
                receivedCount: 0,
                messages: [],
                inputMessage: '',
                ws: null
            });

            addMessage(client, `클라이언트 #${id}가 생성되었습니다. 연결 버튼을 클릭하세요.`);
            return client;
        };

        /**
         * WebSocket 연결
         */
        const connect = (client) => {
            if (client.ws && client.ws.readyState === WebSocket.OPEN) {
                addMessage(client, '⚠️ 이미 연결되어 있습니다.');
                return;
            }

            try {
                addMessage(client, `🔌 ${serverUrl.value}에 연결 중...`);
                client.ws = new WebSocket(serverUrl.value);

                client.ws.onopen = () => {
                    addMessage(client, '✅ 서버에 연결되었습니다!');
                    client.connected = true;
                };

                client.ws.onmessage = (event) => {
                    client.receivedCount++;
                    addMessage(client, event.data, 'received');
                };

                client.ws.onerror = (error) => {
                    addMessage(client, '❌ 오류 발생: ' + (error.message || '연결 실패'));
                    console.error(`Client #${client.id} WebSocket error:`, error);
                };

                client.ws.onclose = (event) => {
                    addMessage(client, `🔌 연결이 종료되었습니다. (코드: ${event.code})`);
                    client.connected = false;
                    client.ws = null;
                };

            } catch (error) {
                addMessage(client, '❌ 연결 실패: ' + error.message);
                console.error(`Client #${client.id} connection error:`, error);
            }
        };

        /**
         * WebSocket 연결 해제
         */
        const disconnect = (client) => {
            if (client.ws && client.ws.readyState === WebSocket.OPEN) {
                client.ws.close();
                addMessage(client, '👋 연결을 종료합니다...');
            }
        };

        /**
         * 메시지 전송
         */
        const sendMessage = (client) => {
            const message = client.inputMessage.trim();

            if (!message) return;

            if (!client.ws || client.ws.readyState !== WebSocket.OPEN) {
                addMessage(client, '❌ 서버에 연결되어 있지 않습니다.');
                return;
            }

            try {
                client.ws.send(message);
                client.sentCount++;
                addMessage(client, message, 'sent');
                client.inputMessage = '';
            } catch (error) {
                addMessage(client, '❌ 메시지 전송 실패: ' + error.message);
                console.error(`Client #${client.id} send error:`, error);
            }
        };

        /**
         * 여러 클라이언트 생성
         */
        const createClients = () => {
            if (!serverUrl.value) {
                alert('서버 URL을 입력해주세요.');
                return;
            }

            if (clientCount.value < 1 || clientCount.value > 10) {
                alert('클라이언트 수는 1~10 사이여야 합니다.');
                return;
            }

            for (let i = 0; i < clientCount.value; i++) {
                clients.push(createClient());
            }
        };

        /**
         * 특정 클라이언트 제거
         */
        const removeClient = (id) => {
            const index = clients.findIndex(c => c.id === id);
            if (index !== -1) {
                const client = clients[index];
                if (client.ws) {
                    client.ws.close();
                }
                delete messagesRefs[id];
                clients.splice(index, 1);
            }
        };
        /**
         * 모든 클라이언트 연결
         */
        const connectAll = () => {
            clients.forEach(client => connect(client));
        };
        /**
         * 모든 클라이언트 연결 해제
         */
        const disconnectAll = () => {
            clients.forEach(client => disconnect(client));
        };

        /**
         * 모든 클라이언트 제거
         */
        const removeAllClients = () => {
            clients.forEach(client => {
                if (client.ws) {
                    client.ws.close();
                }
            });
            clients.splice(0, clients.length);
            clientIdCounter = 0;
        };

        // 생명주기: 컴포넌트 언마운트 전 정리
        onBeforeUnmount(() => {
            disconnectAll();
        });

        // 초기 클라이언트 생성
        nextTick(() => {
            createClients();
        });

        // 템플릿에 노출할 API
        return {
            serverUrl,
            clientCount,
            clients,
            setMessagesRef,
            connect,
            disconnect,
            sendMessage,
            createClients,
            removeClient,
            connectAll,
            disconnectAll,
            removeAllClients
        };
    }
}).mount('#app');