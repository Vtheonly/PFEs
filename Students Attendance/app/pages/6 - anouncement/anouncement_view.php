<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>
    <link rel="stylesheet" href="anouncement.css">
</head>
<body>
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
</body>
</html>