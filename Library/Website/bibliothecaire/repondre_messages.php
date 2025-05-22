<?php
require_once('../config.php');
require_once('../classes/Message.php');
session_start();

if (!isset($_SESSION['ID_Bibliothecaire'])) {
    header('Location: login_bibliothecaire.php');
    exit();
}

$biblio_id = $_SESSION['ID_Bibliothecaire'];

// Récupérer les utilisateurs ayant échangé avec le bibliothécaire
$conversations = Message::getUsersWithMessages($biblio_id);

// Récupérer la conversation avec un utilisateur sélectionné
$user_id = isset($_GET['user']) ? intval($_GET['user']) : 0;
$messages = $user_id ? Message::getConversations($user_id, $biblio_id) : [];
$message = "";

// Envoi d'un message
if ($_SERVER["REQUEST_METHOD"] == "POST" && $user_id && isset($_POST['Contenu'])) {
    $contenu = trim($_POST['Contenu']);
    try {
        $message = Message::sendMessage($biblio_id, $user_id, $contenu);
        $messages = Message::getConversations($user_id, $biblio_id); // Rafraîchir la conversation
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Messagerie - Bibliothécaire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link rel="stylesheet" href="../assets/css/cs.css">
</head>
<body>
<div class="messenger-layout">
    <aside class="sidebar">
        <h3>Conversations</h3>
        <ul class="user-list">
            <?php foreach ($conversations as $user): ?>
                <li>
                    <a class="user-link<?= $user_id == $user['ID_Utilisateur'] ? ' active' : '' ?>"
                       href="?user=<?= $user['ID_Utilisateur'] ?>">
                        <?= htmlspecialchars($user['Prenom_u'] . ' ' . $user['Nom_u']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <?php if (empty($conversations)): ?>
                <li><span style="color:#888;display:block;padding:16px 20px;">Aucune conversation</span></li>
            <?php endif; ?>
        </ul>
    </aside>
    <main class="main-content">
        <div class="message-container">
            <?php if (!$user_id): ?>
                <div class="alert alert-error">Sélectionnez un utilisateur pour discuter.</div>
            <?php else: ?>
                <h2>Conversation avec 
                    <?php
                    foreach ($conversations as $u) {
                        if ($u['ID_Utilisateur'] == $user_id) {
                            echo htmlspecialchars($u['Prenom_u'] . ' ' . $u['Nom_u']);
                            break;
                        }
                    }
                    ?>
                </h2>
                <div class="messenger-box" id="messenger-box">
                    <?php if ($messages): ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-row <?php echo $msg['Expediteur_ID'] == $biblio_id ? 'sent' : 'received'; ?>">
                                <div class="message-content">
                                    <?php
                                    if ($msg['Expediteur_ID'] == $biblio_id) {
                                        echo '<strong>Moi:</strong><br>';
                                    } elseif (!empty($msg['PrenomExp']) || !empty($msg['NomExp'])) {
                                        echo '<strong>' . htmlspecialchars($msg['PrenomExp'] . ' ' . $msg['NomExp']) . ':</strong><br>';
                                    }
                                    ?>
                                    <?php echo htmlspecialchars($msg['Contenu']); ?>
                                    <span class="message-date"><?php echo date('d/m/Y H:i', strtotime($msg['Date_Envoi'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-message">Aucun message pour le moment.</div>
                    <?php endif; ?>
                </div>
                <?php if ($message): ?>
                    <div class="alert <?php echo strpos($message, 'succès') !== false ? 'alert-success' : 'alert-error'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form method="post" class="message-form" autocomplete="off">
                    <textarea id="Contenu" name="Contenu" placeholder="Écrivez votre message ici..." required></textarea>
                    <button type="submit" class="btn-submit">Envoyer</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
</div>
<script src="../assets/js/scripts.js"></script>
</body>
</html>