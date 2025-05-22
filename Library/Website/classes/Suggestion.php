<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Notification.php';


class Suggestion {
    private $conn;
    private $table_name = "suggestion";

    public $ID_Suggestion;
    public $ID_Utilisateur;
    public $Titre;
    public $Auteur;
    public $Categorie;
    public $Date_Suggestion;
    public $Statut_Suggestion;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Vérifie si un livre existe déjà dans les suggestions
    public function bookExists($titre, $auteur) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE Titre = ? AND Auteur = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$titre, $auteur]);
        return $stmt->fetchColumn() > 0; // Retourne true si le livre existe
    }

    // Crée une nouvelle suggestion
    public function create() {
        // Vérifie si le livre existe déjà
        if ($this->bookExists($this->Titre, $this->Auteur)) {
            throw new Exception("Le livre existe déjà dans les suggestions.");
        }

        $this->Statut_Suggestion = $this->Statut_Suggestion ?? 'En attente';
        $query = "INSERT INTO suggestion (ID_Utilisateur, Titre, Auteur, Categorie, Date_Suggestion, Statut_Suggestion) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $this->ID_Utilisateur,
            $this->Titre,
            $this->Auteur,
            $this->Categorie,
            $this->Date_Suggestion,
            $this->Statut_Suggestion
        ]);
    }

    // Récupère les suggestions d'un utilisateur spécifique
    public function getSuggestions($ID_Utilisateur) {
        $query = "SELECT Titre, Auteur, Categorie, Date_Suggestion, Statut_Suggestion 
                  FROM " . $this->table_name . " 
                  WHERE ID_Utilisateur = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$ID_Utilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère l'email d'un utilisateur
    private function getEmailUtilisateur($ID_Utilisateur) {
        $query = "SELECT Email_u FROM utilisateur WHERE ID_Utilisateur = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$ID_Utilisateur]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user['Email_u'] ?? null;
    }

    // Récupère toutes les suggestions avec le nom de l'utilisateur
    public static function getAll() {
        $db = new Database();
        $conn = $db->getConnection();
        $sql = "SELECT s.*, u.Nom_u, u.Prenom_u
                FROM suggestion s
                JOIN utilisateur u ON s.ID_Utilisateur = u.ID_Utilisateur
                ORDER BY s.Date_Suggestion DESC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupère toutes les suggestions avec nom et prénom (pour affichage complet)
    public static function getAllWithUser() {
        $db = new Database();
        $conn = $db->getConnection();
        $sql = "SELECT s.*, u.Nom_u, u.Prenom_u
                FROM suggestion s
                JOIN utilisateur u ON s.ID_Utilisateur = u.ID_Utilisateur
                ORDER BY s.Date_Suggestion DESC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Met à jour le statut d'une suggestion
    public static function updateStatut($id, $statut) {
        $db = new Database();
        $conn = $db->getConnection();
        if ($statut == 'Traitée' || $statut == 'En attente') {
            $stmt = $conn->prepare("UPDATE suggestion SET Statut_Suggestion = ? WHERE ID_Suggestion = ?");
            $stmt->execute([$statut, $id]);
        }
    }
    public static function handleSuggestionForm($postData) {
    $statuts_valides = ['En attente', 'Traitée'];
    $message = "";

    if (isset($postData['id_suggestion'], $postData['nouveau_statut'])) {
        $id = intval($postData['id_suggestion']);
        $nouveau_statut = trim($postData['nouveau_statut']);

        if (in_array($nouveau_statut, $statuts_valides)) {
            if (self::updateStatut($id, $nouveau_statut)) {
                $message = "Statut de la suggestion mis à jour avec succès.";
            } else {
                $message = "Erreur lors de la mise à jour du statut.";
            }
        } else {
            $message = "Statut invalide.";
        }
    }

    return $message;
}

public static function getAllSuggestions() {
    try {
        return self::getAll();
    } catch (Exception $e) {
        throw new Exception("Erreur lors de la récupération des suggestions : " . $e->getMessage());
    }
}
public static function handleStatusUpdate($postData, $statuts_valides) {
    $message = "";

    if (isset($postData['id_suggestion'], $postData['nouveau_statut'])) {
        $id = intval($postData['id_suggestion']);
        $nouveau_statut = trim($postData['nouveau_statut']);

        if (in_array($nouveau_statut, $statuts_valides)) {
            if (self::updateStatut($id, $nouveau_statut)) {
                $message = "Statut de la suggestion mis à jour avec succès.";
            } else {
       
            }
        } else {
            $message = "Statut invalide.";
        }
    }

    return $message;
}
public static function handleSuggestionSubmission($postData, $userId) {
    $message = "";
    $messageType = "";

    $titre = trim($postData["Titre"]);
    $auteur = trim($postData["Auteur"]);
    $categorie = trim($postData["Categorie"]);

    try {
        if (empty($titre) || empty($auteur) || empty($categorie)) {
            throw new Exception("Veuillez remplir tous les champs !");
        }

        $suggestion = new self();
        $suggestion->ID_Utilisateur = $userId;
        $suggestion->Titre = $titre;
        $suggestion->Auteur = $auteur;
        $suggestion->Categorie = $categorie;
        $suggestion->Date_Suggestion = date("Y-m-d");

        if ($suggestion->create()) {
            $message = "Suggestion enregistrée avec succès !";
            $messageType = "success";
        } else {
            throw new Exception("Erreur lors de l'enregistrement de la suggestion.");
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "error";
    }

    return ["message" => $message, "messageType" => $messageType];
}

public static function getUserSuggestions($userId) {
    try {
        $suggestionModel = new self();
        return $suggestionModel->getSuggestions($userId);
    } catch (Exception $e) {
        throw new Exception("Erreur lors de la récupération des suggestions : " . $e->getMessage());
    }
}
public static function handleSuggestionPage($userId, $postData = null) {
    $message = "";
    $messageType = "";
    $titre = $auteur = $categorie = "";
    $suggestions = [];

    try {
        // Récupérer les suggestions de l'utilisateur connecté
        $suggestions = self::getUserSuggestions($userId);

        // Si des données POST sont soumises, gérer la soumission
        if ($postData) {
            $result = self::handleSuggestionSubmission($postData, $userId);
            $message = $result["message"];
            $messageType = $result["messageType"];

            // Si la suggestion est enregistrée avec succès, réinitialiser les champs
            if ($messageType === "success") {
                $titre = $auteur = $categorie = "";
                $suggestions = self::getUserSuggestions($userId);
            }
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = "error";
    }

    return [
        "message" => $message,
        "messageType" => $messageType,
        "titre" => $titre,
        "auteur" => $auteur,
        "categorie" => $categorie,
        "suggestions" => $suggestions,
    ];
}
}