class ChatManager {
    constructor() {
        this.appointmentId = document.currentScript.getAttribute('data-appointment-id');
        this.lastMessageId = 0;
        this.pollingInterval = null;
        this.isTyping = false;
        this.typingTimeout = null;
        this.lastMessageDate = null;
        
        // Initialize elements
        this.chatPanel = document.getElementById('chatPanel');
        this.chatTrigger = document.getElementById('doctorChatTrigger');
        this.closeChat = document.getElementById('closeChat');
        this.chatMessages = document.getElementById('chatMessages');
        this.messageInput = document.getElementById('messageInput');
        this.sendButton = document.getElementById('sendMessage');
        this.chatHeader = document.getElementById('chatHeader');
        this.typingIndicator = document.getElementById('typingIndicator');
        
        // Initialize chat
        this.setupEventListeners();
        this.loadChatHistory();
        this.startPolling();
    }
    
    setupEventListeners() {
        // Toggle chat panel
        this.chatTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            this.chatPanel.classList.toggle('chat-open');
            if (this.chatPanel.classList.contains('chat-open')) {
                setTimeout(() => {
                    this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
                }, 100);
            }
        });

        // Close chat
        this.closeChat.addEventListener('click', () => {
            this.chatPanel.classList.remove('chat-open');
        });

        // Make chat draggable
        this.chatHeader.addEventListener('mousedown', (e) => this.startDragging(e));
        document.addEventListener('mousemove', (e) => this.drag(e));
        document.addEventListener('mouseup', () => this.stopDragging());
        document.addEventListener('mouseleave', () => this.stopDragging());

        // Handle sending messages
        this.sendButton.addEventListener('click', () => this.sendMessage());
        this.messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        // Auto-resize textarea
        this.messageInput.addEventListener('input', () => {
            this.messageInput.style.height = 'auto';
            this.messageInput.style.height = (this.messageInput.scrollHeight) + 'px';
        });
    }
    
    // Draggable functionality
    startDragging(e) {
        if (e.target === this.chatHeader || e.target.closest('.chat-header')) {
            this.isDragging = true;
            const rect = this.chatPanel.getBoundingClientRect();
            this.offsetX = e.clientX - rect.left;
            this.offsetY = e.clientY - rect.top;
            this.chatPanel.style.transition = 'none';
            this.chatPanel.style.cursor = 'grabbing';
        }
    }

    drag(e) {
        if (!this.isDragging) return;
        e.preventDefault();
        this.chatPanel.style.left = (e.clientX - this.offsetX) + 'px';
        this.chatPanel.style.top = (e.clientY - this.offsetY) + 'px';
        this.chatPanel.style.right = 'auto';
        this.chatPanel.style.bottom = 'auto';
        this.chatPanel.style.transform = 'none';
    }

    stopDragging() {
        if (this.isDragging) {
            this.isDragging = false;
            this.chatPanel.style.transition = 'transform 0.3s ease, opacity 0.3s ease';
            this.chatPanel.style.cursor = 'grab';
        }
    }
    
    // Chat functionality
    async fetchData(action, data = {}) {
        const formData = new FormData();
        formData.append('action', action);
        
        for (const key in data) {
            formData.append(key, data[key]);
        }
        
        try {
            const response = await fetch('../Controller/ChatController.php', {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            throw error;
        }
    }
    
    escapeHtml(unsafe) {
        const div = document.createElement('div');
        div.textContent = unsafe;
        return div.innerHTML;
    }
    
    async loadChatHistory() {
        try {
            const response = await this.fetchData('get_messages', {
                appointment_id: this.appointmentId
            });
            
            if (response.success) {
                this.renderMessages(response.messages);
                if (response.messages.length > 0) {
                    this.lastMessageId = Math.max(...response.messages.map(m => m.id));
                }
                this.scrollToBottom();
            }
        } catch (error) {
            console.error('Error loading chat history:', error);
        }
    }
    
    renderMessages(messages) {
        this.chatMessages.innerHTML = ''; // Clear existing messages
        this.lastMessageDate = null;
        
        messages.forEach(msg => {
            this.addMessageToUI(msg);
        });
    }
    
    addMessageToUI(message, isOutgoing = false) {
        const messageDate = new Date(message.created_at);
        const dateString = this.formatDateForDisplay(messageDate);
        
        // Add date divider if needed
        if (this.lastMessageDate !== dateString) {
            this.addDateDivider(dateString);
            this.lastMessageDate = dateString;
        }
        
        const messageElement = document.createElement('div');
        const isPatient = message.sender_type === 'patient';
        
        messageElement.className = `message ${isPatient ? 'sent' : 'received'}`;
        messageElement.dataset.messageId = message.id;
        if (message.id.toString().startsWith('temp-')) {
            messageElement.dataset.tempId = message.id;
        }
        
        const time = messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        messageElement.innerHTML = `
            <div class="message-content">
                <div class="message-text">${this.escapeHtml(message.message)}</div>
                <div class="message-time">${time} <i class="fas fa-check-double"></i></div>
            </div>
        `;
        
        this.chatMessages.appendChild(messageElement);
        this.scrollToBottom();
    }
    
    formatDateForDisplay(date) {
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);
        
        if (date.toDateString() === today.toDateString()) {
            return 'Today';
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Yesterday';
        } else {
            return date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    }
    
    addDateDivider(dateString) {
        const divider = document.createElement('div');
        divider.className = 'chat-date-divider';
        divider.innerHTML = `<span>${dateString}</span>`;
        this.chatMessages.appendChild(divider);
    }
    
    scrollToBottom() {
        this.chatMessages.scrollTop = this.chatMessages.scrollHeight;
    }
    
    startPolling() {
        // Check for new messages every 3 seconds
        this.pollingInterval = setInterval(() => this.checkForNewMessages(), 3000);
    }
    
    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
    }
    
    async checkForNewMessages() {
        if (!this.lastMessageId) return;
        
        try {
            const response = await this.fetchData('check_new_messages', {
                appointment_id: this.appointmentId,
                last_seen_id: this.lastMessageId
            });
            
            if (response.success && response.has_new_messages) {
                this.loadChatHistory();
            }
        } catch (error) {
            console.error('Error checking for new messages:', error);
        }
    }
    
    async sendMessage() {
        const message = this.messageInput.value.trim();
        
        if (!message) return;
        
        try {
            // Clear input
            this.messageInput.value = '';
            this.messageInput.style.height = 'auto';
            
            // Create a temporary message in the UI
            const tempMessageId = 'temp-' + Date.now();
            this.addMessageToUI({
                id: tempMessageId,
                sender_type: 'patient',
                message: message,
                created_at: new Date().toISOString()
            }, true);
            
            // Send to server
            const response = await this.fetchData('send_message', {
                appointment_id: this.appointmentId,
                message: message
            });
            
            if (response.success) {
                // Update the temporary message with the real ID
                const tempMsg = this.chatMessages.querySelector(`[data-temp-id="${tempMessageId}"]`);
                if (tempMsg) {
                    tempMsg.dataset.messageId = response.message_id;
                    delete tempMsg.dataset.tempId;
                }
                this.lastMessageId = response.message_id;
            } else {
                // Show error message if sending failed
                console.error('Failed to send message:', response.error);
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }
}

// Initialize chat when the page loads
document.addEventListener('DOMContentLoaded', () => {
    const chatManager = new ChatManager();
});
