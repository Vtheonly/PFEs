<?php
require_once('../config.php');
require_once('../classes/Bibliothecaire.php');
require_once('../classes/Reservation.php');

$reservation = new Reservation();
$reservations = [];
$message = "";

// Vérifier et mettre à jour les retards
$reservation->verifierRetards(); // Met à jour les statuts des réservations en retard

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = $reservation->handleReservationForm($_POST);
}

// Récupération des réservations
try {
    $reservations = $reservation->getAllReservationsWithMessage(); // Récupère les réservations mises à jour
} catch (Exception $e) {
    $message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gérer les réservations</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../assets/css/cs.css">
</head>
<body>
<div class="container">
    <h2>Gérer les réservations</h2>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <table>
    <thead>
    <tr>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Titre du Livre</th>
        <th>Date Réservation</th>
        <th>Date Retour Prévue</th>
        <th>Statut</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($reservations)): ?>
        <?php foreach ($reservations as $reservation): ?>
            <tr>
                <td><?php echo htmlspecialchars($reservation['Nom_u']); ?></td>
                <td><?php echo htmlspecialchars($reservation['Prenom_u']); ?></td>
                <td><?php echo htmlspecialchars($reservation['Titre']); ?></td>
                <td><?php echo htmlspecialchars($reservation['Date_Reservation']); ?></td>
                <td><?php echo htmlspecialchars($reservation['Date_Retour_Prevue']); ?></td>
                <td class="<?php echo ($reservation['Statut'] == 'Retard') ? 'retard' : ''; ?>">
                    <?php echo htmlspecialchars($reservation['Statut']); ?>
                </td>
                <td>
                    <form method="post" class="actions">
                        <input type="hidden" name="ID_Reservation" value="<?php echo htmlspecialchars($reservation['ID_Reservation']); ?>">
                        <select name="Statut">
                            <option value="En attente" <?php echo ($reservation['Statut'] == 'En attente') ? 'selected' : ''; ?>>En attente</option>
                            <option value="Confirmée" <?php echo ($reservation['Statut'] == 'Confirmée') ? 'selected' : ''; ?>>Confirmée</option>
                            <option value="Empruntée" <?php echo ($reservation['Statut'] == 'Empruntée') ? 'selected' : ''; ?>>Empruntée</option>
                            <option value="Retournée" <?php echo ($reservation['Statut'] == 'Retournée') ? 'selected' : ''; ?>>Retournée</option>
                            <option value="Annulée" <?php echo ($reservation['Statut'] == 'Annulée') ? 'selected' : ''; ?>>Annulée</option>
                            <option value="Retard" <?php echo ($reservation['Statut'] == 'Retard') ? 'selected' : ''; ?>>Retard</option>
                        </select>
                        <button type="submit" name="modifier">Modifier</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" style="text-align: center;">Aucune réservation trouvée.</td>
        </tr>
    <?php endif; ?>
    </tbody>
    </table>
</div>
</body>
</html>