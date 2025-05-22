<?php
session_start();

require_once('../config.php');
require_once('../classes/Reservation.php');

if (!isset($_SESSION["ID_Utilisateur"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$ID_Utilisateur = $_SESSION["ID_Utilisateur"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["ID_Livre"])) {
    $ID_Livre = $_POST["ID_Livre"];
    $reservation = new Reservation();

    try {
        if ($reservation->reserverLivre($ID_Utilisateur, $ID_Livre)) {
            $message = "Livre réservé avec succès !";
        } else {
            $message = "Erreur lors de la reservation du livre.";
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
} else {
    $message = "Veuillez sélectionner un livre à reserver.";
}

header("Location: catalogue.php?message=" . urlencode($message));
exit;
?>