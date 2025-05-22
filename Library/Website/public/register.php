<?php
require_once('../config.php');
require_once('../classes/User.php');

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = User::handleRegistration($_POST);
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
        <h2 data-i18n="create_account">Créer un compte</h2>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-row">
                <input data-i18n="first_name" type="text" id="Prenom_u" name="Prenom_u" placeholder="Prénom" required>
                <input data-i18n="name" type="text" id="Nom_u" name="Nom_u" placeholder="Nom" required>
            </div>

            <input data-i18n="university_email" type="email" id="Email_u" name="Email_u" placeholder="Adresse email" required>
            <input data-i18n="matricule" type="text" id="Matricule" name="Matricule" placeholder="Matricule" required>
            <div class="role-selection">
                <div class="role-option">
                    <input type="radio" id="student" name="Role" value="Etudiant" checked>
                    <label for="student" data-i18n="student">Étudiant</label>
                </div>
                <div class="role-option">
                    <input type="radio" id="teacher" name="Role" value="Enseignant">
                    <label for="teacher" data-i18n="teacher">Enseignant</label>
                </div>
            </div>
            <input data-i18n="password" type="password" id="Mot_de_passe_u" name="Mot_de_passe_u" placeholder="Mot de passe" required>
            <input data-i18n="create_account" type="password" id="Confirm_Mot_de_passe_u" name="Confirm_Mot_de_passe_u" placeholder="Confirmer le mot de passe" required>
            <button type="submit" class="animate__animated animate__pulse animate__infinite animate__slower">
                <i class="fas fa-user-plus"></i> <span data-i18n="register">S'inscrire</span>
            </button>
        </form>

        <div class="form-options">
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> <span data-i18n="already_account">Déjà un compte? Se connecter</span></a>
        </div>
    </div>

    <div class="image-section">
        <div class="image-content">
            <img src="../assets/img/clavier.jpg" alt="Inscription universitaire" id="portal-image">
        </div>
    </div>
</div>
<button id="dark-mode-toggle"> 🌙
    <i class="fas fa-moon"></i>
</button>

<script src="../assets/js/script.js"></script>