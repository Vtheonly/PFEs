<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>
    <link rel="stylesheet" href="anouncement.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">Announcements</div>
            <nav class="sidebar-nav">
                <ul>
                    <?php if ($_SESSION['role'] === 'teacher'): ?>
                        <li><a href="../7 - teacher-dashboard/">Back to Dashboard</a></li>
                        <li><a href="../10 - Qr Genration/qr.html">QR Code</a></li>
                    <?php else: ?>
                        <li><a href="../8 - student-dashboard/">Back to Dashboard</a></li>
                    <?php endif; ?>
                    <li><button class="theme-toggle" onclick="toggleTheme()">
                        <span id="theme-icon">🌞</span>
                        <span id="theme-text">Dark Mode</span>
                    </button></li>
                    <li><a href="../0 - landing/index.html">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <div class="announcements-container">
                <?php if (!empty($announcements)): ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement-card">
                            <div class="announcement-header">
                                <h3><?php echo htmlspecialchars($announcement['teacher_name']); ?></h3>
                                <span class="date"><?php echo $announcement['created_at']; ?></span>
                            </div>
                            <div class="announcement-content">
                                <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-announcements">
                        <p>No announcements available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Theme toggle functionality
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update button text and icon
            document.getElementById('theme-icon').textContent = newTheme === 'light' ? '🌞' : '🌙';
            document.getElementById('theme-text').textContent = newTheme === 'light' ? 'Dark Mode' : 'Light Mode';
        }

        // Initialize theme from localStorage
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.getElementById('theme-icon').textContent = savedTheme === 'light' ? '🌞' : '🌙';
            document.getElementById('theme-text').textContent = savedTheme === 'light' ? 'Dark Mode' : 'Light Mode';
        });
    </script>
</body>
</html>