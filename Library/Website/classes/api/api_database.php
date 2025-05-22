<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");


require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/Database.php';



try {
    // Créer une instance de la classe Database
    $database = new Database();
    $conn = $database->getConnection();

    // Vérifier si la connexion est établie
    if ($conn) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Connexion à la base de données réussie.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Impossible de se connecter à la base de données.'
        ]);
    }
} catch (Exception $e) {
    // Gérer les erreurs de connexion
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur : ' . $e->getMessage()
    ]);
}
?>