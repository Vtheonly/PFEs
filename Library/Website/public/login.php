<?php



require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/classes/User.php'; 
require_once dirname(__DIR__) . '/classes/Bibliothecaire.php'; 
require_once dirname(__DIR__) . '/classes/Notification.php'; 




$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['Email']) && isset($_POST['Mot_de_passe'])) {
        $email = $_POST["Email"];
        $password = $_POST["Mot_de_passe"];

        // Vérifier si c'est un utilisateur
        $user = new User();
        $user->Email_u = $email;
        $user->Mot_de_passe_u = $password;

        try {
            if ($user->login()) {
                session_start();
                $_SESSION["ID_Utilisateur"] = $user->ID_Utilisateur;
                $_SESSION["Nom_u"] = $user->Nom_u;
                $_SESSION["Prenom_u"] = $user->Prenom_u;
                $_SESSION["Role"] = $user->Role;
                $_SESSION["Email_u"] = $user->Email_u; 
                $_SESSION["Matricule"] = $user->Matricule ?? null; 
                // Redirection en fonction du rôle
                header("Location: dashboard.php");
                exit();
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        // Vérifier si c'est un bibliothécaire
        $bibliothecaire = new Bibliothecaire();
        $bibliothecaire->Email_b = $email;
        $bibliothecaire->Mot_de_passe_b = $password;

        try {
            if ($bibliothecaire->login()) {
                session_start();
                $_SESSION["ID_Bibliothecaire"] = $bibliothecaire->ID_Bibliothecaire;
                $_SESSION["Nom_b"] = $bibliothecaire->Nom_b;
                $_SESSION["Prenom_b"] = $bibliothecaire->Prenom_b;
                $_SESSION["Email_b"] = $bibliothecaire->Email_b;

                // Redirection vers le tableau de bord des bibliothécaires
                header("Location: ../bibliothecaire/dashboard_bibliothecaire.php");
                exit();
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        // Si aucune connexion n'a réussi
        $message = "Email ou mot de passe incorrect.";
    }
}

require_once dirname(__DIR__) . '/templates/header.php';

?>
<link rel="stylesheet" href="../assets/css/page2.css">
<div class="bubble bubble-1"></div>
<div class="bubble bubble-2"></div>
<div class="bubble bubble-3"></div>
<div class="bubble bubble-4"></div>
<div class="login-container">
    <div class="form-section">
        <h2 data-i18n="portal">Portail de Connexion</h2>

        <?php if ($message): ?>
            <div class="alert alert-error">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="email" id="Email" name="Email" class="form-input" data-i18n-placeholder="email" placeholder="Adresse email" required>
            <input type="password" id="Mot_de_passe" name="Mot_de_passe" class="form-input" data-i18n-placeholder="password" placeholder="Mot de passe" required>

            <button type="submit" class="login-btn animate__animated animate__pulse animate__infinite animate__slower">
                <i class="fas fa-sign-in-alt"></i> <span class="btn-text" data-i18n="login">Se connecter</span>
            </button>
        </form>

        <div class="form-options">
            <a href="../public/register.php"><i class="fas fa-user-plus"></i> <span data-i18n="first_connection">Première connexion</span></a>
            <a href="#" id="forgot-password-link"><i class="fas fa-key"></i> <span data-i18n="forgot_password">Mot de passe oublié</span></a>
        </div>
    </div>

    <div class="image-section">
        <div class="image-content">
            <img src="../assets/img/clavier.jpg" alt="Campus universitaire">
        </div>
    </div>
</div>

<div class="loader" id="loader">
    <div class="loader-spinner"></div>
    <div class="loader-text" data-i18n="loading">Chargement...</div>
</div>

<button id="dark-mode-toggle"> 🌙
    <i class="fas fa-moon"></i>
</button>

<script src="../assets/js/scripts2.js"></script>