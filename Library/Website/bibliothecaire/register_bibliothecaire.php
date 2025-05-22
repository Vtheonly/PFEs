<!-- filepath: c:\xamppp\htdocs\pfe\bibliotheque\public\register_bibliothecaire.php -->
<?php
// public/register_bibliothecaire.php
require_once('../config.php');
require_once('../classes/Bibliothecaire.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = Bibliothecaire::handleRegistration($_POST);
}

include('../templates/header.php');
?>
<link rel="stylesheet" href="../assets/css/page3.css">
<div class="bubble bubble-1"></div>
<div class="bubble bubble-2"></div>
<div class="bubble bubble-3"></div>
<div class="bubble bubble-4"></div>

<div class="register-container">
    <div class="form-section">
        <h2 data-i18n="create_account">Créer un compte Bibliothécaire</h2>

        <?php if ($message): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-row">
                <input type="text" id="Prenom_b" name="Prenom_b" data-i18n-placeholder="first_name" placeholder="Prénom" required>
                <input type="text" id="Nom_b" name="Nom_b" data-i18n-placeholder="last_name" placeholder="Nom" required>
            </div>

            <input type="email" id="Email_b" name="Email_b" data-i18n-placeholder="email" placeholder="Adresse email" required>

            <input type="password" id="Mot_de_passe_b" name="Mot_de_passe_b" data-i18n-placeholder="password" placeholder="Mot de passe" required>
            <input type="password" id="Confirm_Mot_de_passe_b" name="Confirm_Mot_de_passe_b" data-i18n-placeholder="confirm_password" placeholder="Confirmer le mot de passe" required>

            <button type="submit" class="animate__animated animate__pulse animate__infinite animate__slower">
                <i class="fas fa-user-plus"></i> <span data-i18n="register">S'inscrire</span>
            </button>
        </form>

        <div class="form-options">
            <a href="login_bibliothecaire.php"><i class="fas fa-sign-in-alt"></i> <span data-i18n="already_account">Déjà un compte? Se connecter</span></a>
        </div>
    </div>

    <div class="image-section">
        <div class="image-content">
            <img src="../assets/images/clavier.jpg" alt="Inscription Bibliothécaire" id="portal-image">
        </div>
    </div>
</div>
<button id="dark-mode-toggle">
    <i class="fas fa-moon"></i>

</button>
<script src="../assets/js/script.js"></script>