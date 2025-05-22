<?php
require_once('../classes/Bibliothecaire.php');

// Récupérer les informations du bibliothécaire via la classe
$biblioInfo = Bibliothecaire::getBibliothecaireInfoFromSession();

include('../templates/header.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bibliothécaire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <button class="toggle-btn" id="toggleSidebar">
        <i class="fas fa-bars"></i>
    </button>
    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-menu">
                <li class="sidebar-item">
                    <a href="#profile" class="sidebar-link active" data-section="profile">
                        <i class="fas fa-user"></i>Mon Profil
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#ouvrages" class="sidebar-link" data-section="ouvrages">
                        <i class="fas fa-book"></i>Gérer les ouvrages
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#reservations" class="sidebar-link" data-section="reservations">
                        <i class="fas fa-calendar-alt"></i>Gérer les réservations
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#comptes" class="sidebar-link" data-section="comptes">
                        <i class="fas fa-users"></i>Gérer les comptes
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#suggestions" class="sidebar-link" data-section="suggestions">
                        <i class="fas fa-lightbulb"></i>Gérer les suggestions
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#messages" class="sidebar-link" data-section="messages">
                        <i class="fas fa-envelope"></i>Répondre aux messages
                    </a>
                </li>
            </ul>
        </aside>
        <main class="main-content" id="mainContent">
            <!-- Profil -->
            <section id="profile" class="content-section active">
                <div class="profile-container">
                    <!-- Message de bienvenue -->
                    <div id="welcomeMessage" class="welcome-message"></div>

                    <!-- Informations du bibliothécaire -->
                    <div class="profile-card">
                        <h2>Informations Bibliothécaire</h2>
                        <div class="profile-info">
                            <div><strong>Nom:</strong> <?php echo htmlspecialchars($biblioInfo['Nom']); ?></div>
                            <div><strong>Prénom:</strong> <?php echo htmlspecialchars($biblioInfo['Prénom']); ?></div>
                            <div><strong>Email:</strong> <?php echo htmlspecialchars($biblioInfo['Email']); ?></div>
                        </div>
                    </div>
                </div>
            </section>
            
            <!-- Gérer les ouvrages -->
            <section id="ouvrages" class="content-section">
                <iframe src="gerer_ouvrages.php" title="Gérer les ouvrages"></iframe>
            </section>
            <!-- Gérer les réservations -->
            <section id="reservations" class="content-section">
                <iframe src="gerer_reservations.php" title="Gérer les réservations"></iframe>
            </section>
            <!-- Gérer les comptes utilisateurs -->
            <section id="comptes" class="content-section">
                <iframe src="gerer_comptes.php" title="Gérer les comptes"></iframe>
            </section>
            <!-- Gérer les suggestions -->
            <section id="suggestions" class="content-section">
                <iframe src="gerer_suggestions.php" title="Gérer les suggestions"></iframe>
            </section>
            <!-- Répondre aux messages -->
            <section id="messages" class="content-section">
                <iframe src="repondre_messages.php" title="Répondre aux messages"></iframe>
            </section>
        </main>
    </div>
    
    <button id="dark-mode-toggle"> 
    <i class="fas fa-moon"></i>
</button>
<script src="../assets/js/scripts.js"></script>
</body>
</html>