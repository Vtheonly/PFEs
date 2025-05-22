<?php
require_once('../config.php');
require_once('../classes/Message.php');
session_start();

if (!isset($_SESSION['ID_Utilisateur'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['ID_Utilisateur'];
$message = "";

// Envoi du message à tous les bibliothécaires
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contenu = trim($_POST["Contenu"]);
    try {
        $message = Message::sendToAllBibliothecaires($user_id, $contenu);
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
}

// Récupérer tous les messages des bibliothécaires
$messages = Message::getAllMessagesFromBibliothecaires($user_id);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Messagerie</title>
    <link rel="stylesheet" href="../assets/css/cs2.css">
</head>
<body class="message-page">
    <div class="message-container">
        <h2>Messagerie</h2>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'succès') !== false ? 'alert-success' : 'alert-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="messenger-box" id="messenger-box">
            <?php if ($messages): ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-row <?php echo $msg['Expediteur_ID'] == $user_id ? 'sent' : 'received'; ?>">
                        <div class="message-content">
                            <?php if ($msg['Expediteur_ID'] != $user_id): ?>
                                <strong>Bibliothécaire:</strong><br>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($msg['Contenu']); ?>
                            <div class="message-date"><?php echo date('d/m/Y H:i', strtotime($msg['Date_Envoi'])); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-message">Aucun message pour le moment.</div>
            <?php endif; ?>
        </div>

        <form method="post" class="message-form">
            <textarea id="Contenu" name="Contenu" placeholder="Écrivez votre message ici..." required></textarea>
            <button type="submit" class="btn-submit">Envoyer</button>
        </form>
    </div>
</body>
</html>