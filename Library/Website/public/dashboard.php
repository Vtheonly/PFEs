<?php
session_start();

// Check if the user is logged in and has a valid role
if (!isset($_SESSION["ID_Utilisateur"]) || !in_array($_SESSION["Role"], ['Etudiant', 'Enseignant'])) {
    header("Location: login.php");
    exit();
}

// Determine user role
$userRole = $_SESSION["Role"];

// Define an array to hold user information.
$userInfo = [
    'Nom' => $_SESSION["Nom_u"],
    'Prénom' => $_SESSION["Prenom_u"],
    'Email' => $_SESSION["Email_u"] ,
    "Matricule" =>$_SESSION["Matricule"] 

];

include('../templates/header.php');
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars(ucfirst($userRole)); ?></title>
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
                    <a href="#reservation" class="sidebar-link" data-section="reservation">
                        <i class="fas fa-calendar-alt"></i>Réservations
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#suggestion" class="sidebar-link" data-section="suggestion">
                        <i class="fas fa-lightbulb"></i>Suggestions
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#message" class="sidebar-link" data-section="message">
                        <i class="fas fa-envelope"></i>Messages
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#catalogue" class="sidebar-link" data-section="catalogue">
                        <i class="fas fa-book"></i>Catalogue
                    </a>
                </li>
            </ul>
        </aside>

        <main class="main-content" id="mainContent">
            <!-- Profile Section -->
            <section id="profile" class="content-section active">
                <div class="profile-container">
                    <!-- Message de bienvenue -->
                    <div id="welcomeMessage" class="welcome-message"></div>

                    <!-- Informations utilisateur -->
                    <div class="profile-card">
                        <h2>Informations <?php echo htmlspecialchars(ucfirst($userRole)); ?></h2>
                        <div class="profile-info">
                            <div><strong>Nom:</strong> <?php echo htmlspecialchars($userInfo['Nom']); ?></div>
                            <div><strong>Prénom:</strong> <?php echo htmlspecialchars($userInfo['Prénom']); ?></div>
                            <div><strong>Matricule:</strong> <?php echo htmlspecialchars($userInfo['Matricule']); ?></div>
                            <div><strong>Email:</strong> <?php echo htmlspecialchars($userInfo['Email']); ?></div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Reservation Section -->
            <section id="reservation" class="content-section">
                <iframe src="historique_reservations.php" title="Faire une réservation"></iframe>
            </section>

            <!-- Suggestion Section -->
            <section id="suggestion" class="content-section">
                <iframe src="suggestion.php" title="Suggérer un livre"></iframe>
            </section>

            <!-- Message Section -->
            <section id="message" class="content-section">
                <iframe src="message.php" title="Envoyer un message"></iframe>
            </section>

            <!-- Catalogue Section -->
            <section id="catalogue" class="content-section">
                <iframe src="catalogue.php" title="Consulter le catalogue"></iframe>
            </section>
        </main>
    </div>

    
    <button id="dark-mode-toggle">
    <i class="fas fa-moon"></i>
</button>
<script src="../assets/js/scripts.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>