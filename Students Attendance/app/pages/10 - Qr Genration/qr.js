class QRCodeManager {
    constructor() {
        this.lastCode = null;
        this.sessionInterval = null;
        this.regenerateBtn = document.getElementById('regenerate-btn');
        this.qrCodeDiv = document.getElementById('qr-code');
        this.codeDisplay = document.getElementById('code-display');
        this.sessionInfo = document.getElementById('session-info');
        this.errorMessage = document.getElementById('error-message');
        
        this.setupEventListeners();
        this.checkAndGenerateCode();
        this.startPeriodicChecks();
    }

    setupEventListeners() {
        this.regenerateBtn.addEventListener('click', () => {
            this.generateNewCode();
            // Add button animation
            this.regenerateBtn.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.regenerateBtn.style.transform = '';
            }, 200);
        });
    }

    async checkActiveSession() {
        try {
            const response = await fetch('../7 - teacher-dashboard/attendance_management.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=get_active'
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error checking session:', error);
            this.showError('Failed to check session status. Please refresh the page.');
            return { success: false, error: error.message };
        }
    }

    async checkAndGenerateCode() {
        const sessionStatus = await this.checkActiveSession();
        
        if (sessionStatus.success) {
            this.sessionInfo.className = 'active';
            this.sessionInfo.innerHTML = `
                <strong>Active Session</strong><br>
                Date: ${sessionStatus.session.date}<br>
                Time: ${sessionStatus.session.start_time} - ${sessionStatus.session.end_time}
            `;
            this.regenerateBtn.disabled = false;
            await this.generateNewCode();
        } else {
            this.sessionInfo.className = 'inactive';
            this.sessionInfo.textContent = 'No active session found. Please start a session from the dashboard.';
            this.regenerateBtn.disabled = true;
            this.clearQRCode();
        }
    }

    clearQRCode() {
        this.qrCodeDiv.innerHTML = '';
        this.codeDisplay.textContent = '';
    }

    async generateNewCode() {
        this.regenerateBtn.disabled = true;
        
        try {
            const response = await fetch('qr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=generate'
            });
            
            if (!response.ok) throw new Error('Network response was not ok');
            
            const data = await response.json();
            
            if (data.success && data.code) {
                // Only update if the code is different from the last one
                if (this.lastCode !== data.code) {
                    this.lastCode = data.code;
                    this.showNewCode(data.code);
                }
                this.showError(''); // Clear any error messages
            } else {
                throw new Error(data.error || 'Failed to generate code');
            }
        } catch (error) {
            console.error('Error generating QR code:', error);
            this.showError(error.message);
            if (error.message.includes('No active session')) {
                await this.checkAndGenerateCode();
            }
        } finally {
            this.regenerateBtn.disabled = false;
        }
    }

    showNewCode(code) {
        // Generate QR code
        const qr = qrcode(0, 'L');
        qr.addData(code);
        qr.make();
        
        // Add fade-out effect to old content
        this.qrCodeDiv.style.opacity = '0';
        this.codeDisplay.style.opacity = '0';
        
        setTimeout(() => {
            // Update content
            this.qrCodeDiv.innerHTML = qr.createImgTag(8);
            this.codeDisplay.textContent = code;
            
            // Add fade-in effect
            this.qrCodeDiv.style.opacity = '1';
            this.codeDisplay.style.opacity = '1';
        }, 300);
    }

    showError(message) {
        if (message) {
            this.errorMessage.textContent = message;
            this.errorMessage.style.display = 'block';
            
            // Auto-hide error after 5 seconds
            setTimeout(() => {
                this.errorMessage.style.opacity = '0';
                setTimeout(() => {
                    if (this.errorMessage.textContent === message) {
                        this.errorMessage.style.display = 'none';
                        this.errorMessage.style.opacity = '1';
                    }
                }, 300);
            }, 5000);
        } else {
            this.errorMessage.style.display = 'none';
        }
    }

    startPeriodicChecks() {
        // Check session status every 30 seconds
        this.sessionInterval = setInterval(() => {
            this.checkAndGenerateCode();
        }, 30000);

        // Clean up interval when page is closed
        window.addEventListener('beforeunload', () => {
            if (this.sessionInterval) {
                clearInterval(this.sessionInterval);
            }
        });
    }
}

// Initialize when the page loads
document.addEventListener('DOMContentLoaded', () => {
    // Add CSS transitions
    const style = document.createElement('style');
    style.textContent = `
        #qr-code, #code-display {
            transition: opacity 0.3s ease;
        }
        #regenerate-btn {
            transition: transform 0.2s ease, background-color 0.3s ease, box-shadow 0.3s ease;
        }
        #error-message {
            transition: opacity 0.3s ease;
        }
    `;
    document.head.appendChild(style);
    
    new QRCodeManager();
});