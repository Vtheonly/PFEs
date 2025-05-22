<?php

header("Access-Control-Allow-Origin: *" );
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


require_once dirname(__DIR__, 2) . '/config.php';
require_once dirname(__DIR__) . '/Bibliothecaire.php';


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Gestion des actions POST
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['action'])) {
            $action = $data['action'];

            if ($action === 'login') {
                // Connexion du bibliothécaire
                $bibliothecaire = new Bibliothecaire();
                $bibliothecaire->Email_b = $data['Email_b'];
                $bibliothecaire->Mot_de_passe_b = $data['Mot_de_passe_b'];

                if ($bibliothecaire->login()) {
                    echo json_encode([
                        'message' => 'Connexion réussie.',
                        'bibliothecaire' => [
                            'ID_Bibliothecaire' => $bibliothecaire->ID_Bibliothecaire,
                            'Nom_b' => $bibliothecaire->Nom_b,
                            'Prenom_b' => $bibliothecaire->Prenom_b,
                            'Email_b' => $bibliothecaire->Email_b
                        ]
                    ]);
                } else {
                    echo json_encode(['error' => 'Email ou mot de passe incorrect.']);
                }
            } elseif ($action === 'add_book') {
                // Ajouter un livre
                $bibliothecaire = new Bibliothecaire();
                try {
                    $bibliothecaire->addBook(
                        $data['Titre'],
                        $data['Auteur'],
                        $data['Categorie'],
                        $data['Statut_Livre'],
                        $data['Image'] ?? null
                    );
                    echo json_encode(['message' => 'Livre ajouté avec succès.']);
                } catch (Exception $e) {
                    echo json_encode(['error' => $e->getMessage()]);
                }
            }
        } else {
            echo json_encode(['error' => 'Action non spécifiée.']);
        }
        break;

    case 'GET':
        // Récupérer les informations du bibliothécaire connecté
        if (isset($_GET['action']) && $_GET['action'] === 'info') {
            session_start();
            if (isset($_SESSION['ID_Bibliothecaire'])) {
                echo json_encode([
                    'ID_Bibliothecaire' => $_SESSION['ID_Bibliothecaire'],
                    'Nom_b' => $_SESSION['Nom_b'],
                    'Prenom_b' => $_SESSION['Prenom_b'],
                    'Email_b' => $_SESSION['Email_b']
                ]);
            } else {
                echo json_encode(['error' => 'Aucun bibliothécaire connecté.']);
            }
        } else {
            echo json_encode(['error' => 'Action non spécifiée ou invalide.']);
        }
        break;

    case 'PUT':
        // Modifier un livre
        $data = json_decode(file_get_contents("php://input"), true);

        if (isset($data['action']) && $data['action'] === 'update_book') {
            $bibliothecaire = new Bibliothecaire();
            try {
                $bibliothecaire->updateBook(
                    $data['ID_Livre'],
                    $data['Titre'],
                    $data['Auteur'],
                    $data['Categorie'],
                    $data['Statut_Livre'],
                    $data['Image'] ?? null
                );
                echo json_encode(['message' => 'Livre modifié avec succès.']);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'Action non spécifiée ou invalide.']);
        }
        break;

    case 'DELETE':
        // Supprimer un livre
        if (isset($_GET['ID_Livre'])) {
            $bibliothecaire = new Bibliothecaire();
            try {
                $bibliothecaire->deleteBook($_GET['ID_Livre']);
                echo json_encode(['message' => 'Livre supprimé avec succès.']);
            } catch (Exception $e) {
                echo json_encode(['error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['error' => 'ID du livre manquant.']);
        }
        break;

    default:
        // Méthode non autorisée
        http_response_code(405);
        echo json_encode(['error' => 'Méthode non autorisée.']);
        break;
}
?>