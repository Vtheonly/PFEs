<?php

require_once('../config.php');
require_once('../classes/Bibliothecaire.php');
require_once('../classes/Notification.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = Bibliothecaire::handleLoginRequest($_POST);
}

include('../templates/header.php');
?>
<link rel="stylesheet" href="../assets/css/page2.css">
<div class="bubble bubble-1"></div>
<div class="bubble bubble-2"></div>
<div class="bubble bubble-3"></div>
<div class="bubble bubble-4"></div>
<div class="login-container">
    <div class="form-section">
        <h2 data-i18n="librarian_portal">Portail Bibliothécaires</h2>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'e-mail') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="email" id="Email_b" name="Email_b" class="form-input" data-i18n-placeholder="librarian_email" placeholder="Adresse email" required>
            <input type="password" id="Mot_de_passe_b" name="Mot_de_passe_b" class="form-input" data-i18n-placeholder="password" placeholder="Mot de passe" required>

            <button type="submit" class="login-btn animate__animated animate__pulse animate__infinite animate__slower">
                <i class="fas fa-sign-in-alt"></i> <span class="btn-text" data-i18n="login">Se connecter</span>
            </button>
        </form>

        <div class="form-options">
            <a href="#" id="forgot-password-link"><i class="fas fa-key"></i> <span data-i18n="forgot_password">Mot de passe oublié</span></a>
        </div>
    </div>

    <div class="image-section">
        <div class="image-content">
            <img src="../assets/img/clavier.jpg" alt="Connexion Bibliothécaire">
        </div>
    </div>
</div>

<!-- Formulaire de récupération de mot de passe -->
<div class="forgot-password-container" id="forgot-password-container" style="display: none;">
    <h2>Réinitialiser le mot de passe</h2>
    <form method="post">
        <input type="email" id="forgot_email" name="forgot_email" class="form-input" data-i18n-placeholder="forgot_email" placeholder="Entrez votre adresse email" required>
        <button type="submit" class="btn-submit">Envoyer</button>
    </form>
</div>

<button id="dark-mode-toggle"> 🌙
    <i class="fas fa-moon"></i>
</button>



<script src="../assets/js/scripts.js"></script>