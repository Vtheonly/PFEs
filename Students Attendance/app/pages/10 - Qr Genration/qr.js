class QRCodeManager {
    constructor() {
        this.qrcode = null;
        this.regenerateBtn = document.getElementById('regenerate-btn');
        this.sessionInterval = null;
        this.initializeQRCode();
        this.setupEventListeners();
    }

    initializeQRCode() {
        const qrcodeDiv = document.getElementById('qrcode');
        qrcodeDiv.innerHTML = '';
        
        this.qrcode = new QRCode(qrcodeDiv, {
            width: 256,
            height: 256,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        
        // Check active session before generating code
        this.checkAndGenerateCode();
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
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error checking session:', error);
            this.showError('Failed to check active session status');
            return { success: false, error: error.message };
        }
    }

    async checkAndGenerateCode() {
        const sessionStatus = await this.checkActiveSession();
        const sessionInfo = document.getElementById('session-info');
        
        if (sessionStatus.success) {
            sessionInfo.className = 'active';
            sessionInfo.textContent = `Active Session: ${sessionStatus.session.date} ${sessionStatus.session.start_time} - ${sessionStatus.session.end_time}`;
            this.regenerateBtn.disabled = false;
            await this.generateNewCode();
        } else {
            sessionInfo.className = 'inactive';
            sessionInfo.textContent = 'No active session found. Please start a session from the dashboard.';
            this.regenerateBtn.disabled = true;
            this.qrcode.clear();
            document.getElementById('code-display').textContent = '';
        }
    }

    async generateNewCode() {
        try {
            const response = await fetch('qr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=generate'
            });
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            if (data.success && data.code) {
                this.qrcode.clear();
                this.qrcode.makeCode(data.code);
                document.getElementById('code-display').textContent = data.code;
                this.showError(''); // Clear any error messages
            } else {
                this.showError(data.error || 'Failed to generate code');
                if (data.error && data.error.includes('No active session')) {
                    await this.checkAndGenerateCode(); // Recheck session status
                }
            }
        } catch (error) {
            console.error('Error generating QR code:', error);
            this.showError('Failed to generate QR code. Please check your connection and try again.');
        }
    }

    showError(message) {
        const errorDiv = document.getElementById('error-message');
        if (message) {
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        } else {
            errorDiv.style.display = 'none';
        }
    }

    setupEventListeners() {
        this.regenerateBtn.addEventListener('click', () => {
            this.generateNewCode();
        });

        // Start periodic session check
        this.sessionInterval = setInterval(() => {
            this.checkAndGenerateCode();
        }, 5 * 60 * 1000); // Check every 5 minutes
    }
}

// Initialize when the page loads
document.addEventListener('DOMContentLoaded', () => {
    new QRCodeManager();
});