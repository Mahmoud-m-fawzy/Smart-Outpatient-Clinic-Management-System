class VoiceInput {
    constructor() {
        this.soundEnabled = false;
        this.initializeElements();
        this.initializeEvents();
    }

    initializeElements() {
        this.voiceToggle = document.getElementById('voiceToggle');
        this.instructionBox = document.createElement('div');
        this.instructionBox.className = 'voice-instruction-box';
        this.instructionBox.style.display = 'none';
        
        // Create instruction content
        this.instructionBox.innerHTML = `
            <div class="voice-instruction-header">
                <i class="fas fa-microphone"></i>
                <h3>التعليمات</h3>
                <button class="close-instruction">&times;</button>
            </div>
            <div class="voice-instruction-content">
                <ol>
                    <li>انقر على أي حقل إدخال</li>
                    <li>انتظر حتى يظهر "جاهز للتحدث"</li>
                    <li>ابدأ بالتحدث بوضوح</li>
                </ol>
                <div class="voice-note">
                    <strong>نصائح للاستخدام الأمثل:</strong>
                    <ul>
                        <li>تحدث بوضوح وبطء معتدل</li>
                        <li>اختر مكاناً هادئاً</li>
                    </ul>
                </div>
            </div>
        `;
        
        // Add to body
        document.body.appendChild(this.instructionBox);
        
        // Get all input fields
        this.inputFields = document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="password"], input[type="number"]');
    }

    // Function to speak text in Arabic
    speak(text) {
        if (!this.soundEnabled) return;
        
        // Cancel any ongoing speech
        window.speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'ar-SA'; // Arabic (Saudi Arabia)
        window.speechSynthesis.speak(utterance);
    }

    initializeEvents() {
        // Toggle voice functionality
        this.voiceToggle.addEventListener('change', () => {
            this.soundEnabled = this.voiceToggle.checked;
            if (this.soundEnabled) {
                this.showInstructions();
            }
        });

        // Close instruction box
        this.instructionBox.querySelector('.close-instruction')?.addEventListener('click', () => {
            this.hideInstructions();
        });

        // Map of field IDs to Arabic labels
        this.fieldLabels = {
            'FN': 'الاسم الأول',
            'LN': 'الاسم الأخير',
            'email': 'البريد الإلكتروني',
            'phone': 'رقم الهاتف',
            'password': 'كلمة المرور',
            'confirmPassword': 'تأكيد كلمة المرور',
            'age': 'العمر',
            'address': 'العنوان',
            'job': 'الوظيفة'
        };

        // Add focus and click events to input fields
        this.inputFields.forEach(field => {
            // Speak field name on focus
            field.addEventListener('focus', () => {
                if (this.soundEnabled) {
                    const fieldId = field.id;
                    const fieldName = this.fieldLabels[fieldId] || field.placeholder || field.name || 'حقل إدخال';
                    this.speak(fieldName);
                }
            });
            
            // Start dictation on click if enabled
            field.addEventListener('click', (e) => {
                if (this.soundEnabled) {
                    this.startDictation(field);
                }
            });
        });
    }

    showInstructions() {
        this.instructionBox.style.display = 'block';
        // Auto-hide after 10 seconds
        setTimeout(() => {
            this.hideInstructions();
        }, 10000);
    }

    hideInstructions() {
        this.instructionBox.style.display = 'none';
    }

    startDictation(field) {
        if (!('webkitSpeechRecognition' in window)) {
            alert('Your browser does not support speech recognition. Please use Google Chrome or Microsoft Edge.');
            return;
        }
        
        const originalBorder = field.style.border;
        const originalPlaceholder = field.placeholder;
        
        // Show waiting state
        field.style.border = '2px solid #ffc107';
        field.placeholder = 'جاري التحضير...';
        
        // Show instruction near the field
        const instruction = document.createElement('div');
        instruction.className = 'field-instruction';
        field.parentNode.insertBefore(instruction, field.nextSibling);
        
        // Position instruction
        const rect = field.getBoundingClientRect();
        instruction.style.top = `${rect.bottom + 5}px`;
        instruction.style.left = `${rect.left}px`;
        
        // Countdown
        let countdown = 2;
        const countdownInterval = setInterval(() => {
            if (countdown > 0) {
                field.placeholder = `جاري البدء... ${countdown}`;
                countdown--;
            } else {
                clearInterval(countdownInterval);
                field.placeholder = `تحدث الآن`;
                field.style.border = '2px solid #dc3545';
                
                // Start recognition
                const recognition = new webkitSpeechRecognition();
                recognition.lang = 'ar-EG';
                recognition.interimResults = false;
                
                recognition.start();
                
                recognition.onresult = (event) => {
                    const transcript = event.results[0][0].transcript;
                    field.value = transcript;
                    const eventObj = new Event('input', { bubbles: true });
                    field.dispatchEvent(eventObj);
                };
                
                recognition.onerror = (event) => {
                    console.error('Speech recognition error', event.error);
                    if (event.error === 'not-allowed') {
                        alert('Please allow microphone access to use voice input.');
                    }
                };
                
                recognition.onend = () => {
                    field.style.border = originalBorder;
                    field.placeholder = originalPlaceholder || '';
                    instruction.remove();
                };
            }
        }, 1000);
        
        // Cleanup if user clicks away
        const cleanup = () => {
            clearInterval(countdownInterval);
            field.style.border = originalBorder;
            field.placeholder = originalPlaceholder || '';
            instruction.remove();
            document.removeEventListener('click', outsideClick);
        };
        
        const outsideClick = (e) => {
            if (e.target !== field) {
                cleanup();
            }
        };
        
        document.addEventListener('click', outsideClick);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.voiceInput = new VoiceInput();
});
