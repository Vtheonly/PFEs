<?php
// public/historique_reservations.php
session_start();

require_once('../config.php');
require_once('../classes/Reservation.php');

// Assurez-vous que l'utilisateur est connecté
if (!isset($_SESSION["ID_Utilisateur"])) {
    header("Location: login.php");
    exit;
}

$ID_Utilisateur = $_SESSION["ID_Utilisateur"];

$reservation = new Reservation();
$reservations = $reservation->getReservationStatus($ID_Utilisateur);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des réservations</title>
    <head>
    <link rel="stylesheet" href="../assets/css/cs2.css">
</head>
<body class="historique-reservations-page">
</head>
<body>
    <div class="reservations-container">
        <h2>Historique de vos réservations</h2>
        <?php if (!empty($reservations)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Titre du livre</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['Titre']); ?></td>
                            <td><span class="status <?php echo htmlspecialchars(str_replace(' ', '_', $reservation['Statut'])); ?>">
                                <?php echo htmlspecialchars($reservation['Statut']); ?>
                            </span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-reservations">Vous n'avez aucune réservation enregistrée.</p>
        <?php endif; ?>
    </div>
</body>
</html>
