document.addEventListener('DOMContentLoaded', () => {
    const userListUl = document.getElementById('user-list');
    const conversationHeader = document.getElementById('conversation-header');
    const messageDisplay = document.getElementById('message-display');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');

    let selectedUserId = null;
    let currentUsers = [];

    // Function to load users and their conversations
    async function loadUsers() {
        try {
            const response = await fetch('load_chat.php');
            const data = await response.json();
            
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }

            currentUsers = data.users;
            displayUsers();
        } catch (error) {
            console.error('Failed to load users:', error);
        }
    }

    // Function to display users in the user list
    function displayUsers() {
        userListUl.innerHTML = ''; // Clear existing list
        currentUsers.forEach(user => {
            const userLi = document.createElement('li');
            userLi.textContent = `${user.name} (${user.role})`;
            userLi.addEventListener('click', () => {
                selectUser(user.id, user.name);
            });
            userListUl.appendChild(userLi);
        });
    }

    // Function to select a user and display their conversation
    function selectUser(userId, userName) {
        selectedUserId = userId;
        conversationHeader.textContent = userName;
        loadMessages(userId);

        // Highlight selected user in the list
        document.querySelectorAll('#user-list li').forEach(li => li.classList.remove('active'));
        const selectedUserLi = Array.from(userListUl.children).find(li => li.textContent.includes(userName));
        if (selectedUserLi) {
            selectedUserLi.classList.add('active');
        }
    }

    // Function to load messages for a selected user
    async function loadMessages(userId) {
        try {
            const response = await fetch(`load_chat.php?user_id=${userId}`);
            const data = await response.json();
            
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }

            displayMessages(data.messages || []);
        } catch (error) {
            console.error('Failed to load messages:', error);
        }
    }

    // Function to display messages
    function displayMessages(messages) {
        messageDisplay.innerHTML = ''; // Clear existing messages
        if (messages.length > 0) {
            messages.forEach(message => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message');
                messageDiv.classList.add(message.sender === 'me' ? 'sent' : 'received');
                
                const timeSpan = document.createElement('span');
                timeSpan.classList.add('timestamp');
                timeSpan.textContent = message.timestamp;
                
                const textDiv = document.createElement('div');
                textDiv.textContent = message.text;
                
                messageDiv.appendChild(textDiv);
                messageDiv.appendChild(timeSpan);
                messageDisplay.appendChild(messageDiv);
            });
        } else {
            messageDisplay.innerHTML = '<p>No messages yet.</p>';
        }
        // Scroll to the bottom to show latest messages
        messageDisplay.scrollTop = messageDisplay.scrollHeight;
    }

    // Function to send a message
    async function sendMessage(text) {
        if (!selectedUserId || !text.trim()) return;

        try {
            const response = await fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    receiver_id: selectedUserId,
                    message: text.trim()
                })
            });

            const data = await response.json();
            
            if (data.success) {
                messageInput.value = ''; // Clear input field
                loadMessages(selectedUserId); // Reload messages to show the new one
            } else {
                console.error('Failed to send message:', data.error);
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    // Event listener for sending messages
    sendButton.addEventListener('click', () => {
        const messageText = messageInput.value.trim();
        if (messageText && selectedUserId) {
            sendMessage(messageText);
        }
    });

    // Event listener for pressing Enter to send message
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const messageText = messageInput.value.trim();
            if (messageText && selectedUserId) {
                sendMessage(messageText);
            }
        }
    });

    // Auto-refresh messages every 5 seconds when a conversation is selected
    setInterval(() => {
        if (selectedUserId) {
            loadMessages(selectedUserId);
        }
    }, 5000);

    // Initial load of users
    loadUsers();
});