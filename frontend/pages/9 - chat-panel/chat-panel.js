document.addEventListener('DOMContentLoaded', () => {
    const userListUl = document.getElementById('user-list');
    const conversationHeader = document.getElementById('conversation-header');
    const messageDisplay = document.getElementById('message-display');
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');

    // Dummy User Data (replace with backend data in real app)
    const users = [
        { id: 1, name: 'Alice', lastMessage: 'Hello!' },
        { id: 2, name: 'Bob', lastMessage: 'How are you?' },
        { id: 3, name: 'Charlie', lastMessage: 'Good morning' },
        { id: 4, name: 'David', lastMessage: 'See you later' }
    ];

    // Dummy Conversation Data (replace with backend data in real app)
    const conversations = {
        1: [ // User ID 1's conversation
            { sender: 'me', text: 'Hi Alice!', timestamp: '10:00 AM' },
            { sender: 'them', text: 'Hello!', timestamp: '10:01 AM' }
        ],
        2: [ // User ID 2's conversation
            { sender: 'me', text: 'Hey Bob, how\'s it going?', timestamp: '11:00 AM' },
            { sender: 'them', text: 'Pretty good, thanks!', timestamp: '11:02 AM' },
            { sender: 'them', text: 'How are you?', timestamp: '11:03 AM' },
            { sender: 'me', text: 'Doing well too.', timestamp: '11:04 AM' }
        ],
        3: [], // User ID 3's conversation (empty)
        4: []  // User ID 4's conversation (empty)
    };

    let selectedUserId = null;

    // Function to display users in the user list
    function displayUsers() {
        userListUl.innerHTML = ''; // Clear existing list
        users.forEach(user => {
            const userLi = document.createElement('li');
            userLi.textContent = user.name;
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
        displayMessages(userId);

        // Highlight selected user in the list
        document.querySelectorAll('#user-list li').forEach(li => li.classList.remove('active'));
        const selectedUserLi = Array.from(userListUl.children).find(li => li.textContent === userName);
        if (selectedUserLi) {
            selectedUserLi.classList.add('active');
        }
    }

    // Function to display messages for a selected user
    function displayMessages(userId) {
        messageDisplay.innerHTML = ''; // Clear existing messages
        if (conversations[userId]) {
            conversations[userId].forEach(message => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message');
                messageDiv.classList.add(message.sender === 'me' ? 'sent' : 'received');
                messageDiv.textContent = message.text;
                messageDisplay.appendChild(messageDiv);
            });
            // Scroll to the bottom to show latest messages
            messageDisplay.scrollTop = messageDisplay.scrollHeight;
        } else {
            messageDisplay.innerHTML = '<p>No messages yet.</p>';
        }
    }

    // Event listener for sending messages
    sendButton.addEventListener('click', () => {
        const messageText = messageInput.value.trim();
        if (messageText && selectedUserId) {
            const newMessage = { sender: 'me', text: messageText, timestamp: new Date().toLocaleTimeString() };
            if (!conversations[selectedUserId]) {
                conversations[selectedUserId] = []; // Initialize conversation if it doesn't exist
            }
            conversations[selectedUserId].push(newMessage);
            displayMessages(selectedUserId);
            messageInput.value = ''; // Clear input field
            // In a real app, you would send the message to the backend here
        }
    });

    // Initial display of users
    displayUsers();
});