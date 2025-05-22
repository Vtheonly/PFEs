<?php
// templates/header.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliothèque</title>
    <link rel="stylesheet" href="../assets/css/page1.css">
    <link rel="icon" href="../assets/img/icon.png" type="image/x-icon">
</head>
<body>
    <header class="top-header">
        <div class="umb-logo">
            <img src="../assets/img/logo.png" alt="Logo UMB">
          <div class="umb-title">
            <span data-i18n="university_name">Université M'hamed Bougara-Boumerdès</span>
            <span data-i18n="library_name">Faculté des Sciences-Departement d'Informatique</span>
          </div>
        </div>
        <nav class="main-nav">
            <a href="../public/index.php" data-i18n="home">Accueil</a>
            <?php if(isset($_SESSION['ID_Utilisateur']) || isset($_SESSION['ID_Bibliothecaire'])): ?>
                
                <a href="../public/logout.php" data-i18n="logout">Déconnexion</a>
            <?php else: ?>
                <div class="dropdown">
                    <a href="../public/login.php"" data-i18n="connect">Se connecter </a>
                    
                </div>
                <a href="../public/register.php" data-i18n="register">Inscription</a>
            <?php endif; ?>
            <div class="language-switcher">
                <button class="language-btn" id="language-toggle">
                    <img src="https://flagcdn.com/w20/fr.png" alt="FR" id="current-flag">
                    <span id="current-language">FR</span>
                </button>
                <div class="language-dropdown">
                    <div class="language-option active" data-lang="fr">
                        <img src="https://flagcdn.com/w20/fr.png" alt="FR">
                        <span>FR</span>
                    </div>
                    <div class="language-option" data-lang="ar">
                        <img src="https://flagcdn.com/w20/dz.png" alt="AR">
                        <span>AR</span>
                    </div>
                    <div class="language-option" data-lang="en">
                        <img src="https://flagcdn.com/w20/gb.png" alt="EN">
                        <span>EN</span>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <script src="../assets/js/script.js"></script>