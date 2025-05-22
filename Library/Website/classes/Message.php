<?php


require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Notification.php';


class Message {
    private $conn;
    private $table_name = "message";

    public $ID_Message;
    public $Expediteur_ID;
    public $Destinataire_ID;
    public $Contenu;
    public $Date_Envoi;
    public $Statut_Message;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        if (empty($this->Contenu)) {
            throw new Exception("Veuillez saisir un message");
        }
        $query = "INSERT INTO " . $this->table_name . " (Expediteur_ID, Destinataire_ID, Contenu, Date_Envoi, Statut_Message) VALUES (?, ?, ?, ?, 'Non lu')";
        $stmt = $this->conn->prepare($query);
        if (!$stmt->execute([$this->Expediteur_ID, $this->Destinataire_ID, $this->Contenu, $this->Date_Envoi])) {
            throw new Exception("Erreur lors de l'envoi du message");
        }
        // Notification (optionnel)
        $notification = new Notification();
        $subject = "Nouveau message";
        $body = "Nouveau message reçu !";
        $notification->sendNotification($this->getEmailBibliothecaire(), $subject, $body);
        return true;
    }

    public function getMessages($ID_Utilisateur) {
        $query = "SELECT Contenu FROM " . $this->table_name . " WHERE ID_Utilisateur = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$ID_Utilisateur]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getEmailBibliothecaire() {
        $query = "SELECT Email_b FROM bibliothecaire LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $bibliothecaire = $stmt->fetch(PDO::FETCH_ASSOC);
        return $bibliothecaire['Email_b'] ?? null;
    }

    // Conversation complète avec noms/prénoms pour affichage Messenger
   
    public static function getConversation($user_id, $biblio_id) {
        $database = new Database();
        $conn = $database->getConnection();
        $query = "SELECT m.*
                  FROM message m
                  WHERE (m.Expediteur_ID = :user AND m.Destinataire_ID = :biblio)
                     OR (m.Expediteur_ID = :biblio AND m.Destinataire_ID = :user)
                  ORDER BY m.Date_Envoi ASC";
        $stmt = $conn->prepare($query);
        $stmt->execute([
            'user' => $user_id,
            'biblio' => $biblio_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAllNonLus() {
        $db = new Database();
        $conn = $db->getConnection();
        $sql = "SELECT m.*, u.Nom_u AS Expediteur_Nom
                FROM message m
                JOIN utilisateur u ON m.Expediteur_ID = u.ID_Utilisateur
                WHERE m.Statut_Message = 'Non lu'
                ORDER BY m.Date_Envoi DESC";
        $stmt = $conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function getAllMessagesFromBibliothecaires($user_id) {
        $database = new Database();
        $conn = $database->getConnection();
    
        $query = "
            SELECT m.*
            FROM message m
            WHERE (m.Expediteur_ID IN (SELECT ID_Bibliothecaire FROM bibliothecaire) AND m.Destinataire_ID = :user_id)
               OR (m.Expediteur_ID = :user_id AND m.Destinataire_ID IN (SELECT ID_Bibliothecaire FROM bibliothecaire))
            ORDER BY m.Date_Envoi ASC
        ";
    
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function sendToAllBibliothecaires($user_id, $contenu) {
        if (empty($contenu)) {
            throw new Exception("Veuillez saisir un message !");
        }
    
        $database = new Database();
        $conn = $database->getConnection();
    
        // Récupérer tous les bibliothécaires
        $query = "SELECT ID_Bibliothecaire FROM bibliothecaire";
        $stmt = $conn->query($query);
        $bibliothecaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        if (empty($bibliothecaires)) {
            throw new Exception("Aucun bibliothécaire trouvé.");
        }
    
        foreach ($bibliothecaires as $biblio) {
            $msg = new self();
            $msg->Expediteur_ID = $user_id;
            $msg->Destinataire_ID = $biblio['ID_Bibliothecaire'];
            $msg->Contenu = $contenu;
            $msg->Date_Envoi = date("Y-m-d H:i:s");
            $msg->create();
        }
    
        return "Message envoyé avec succès!";
    }
   
public static function getUsersWithMessages($biblio_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $sql = "SELECT DISTINCT u.ID_Utilisateur, u.Nom_u, u.Prenom_u
            FROM message m
            JOIN utilisateur u ON (u.ID_Utilisateur = m.Expediteur_ID OR u.ID_Utilisateur = m.Destinataire_ID)
            WHERE (m.Expediteur_ID = :biblio OR m.Destinataire_ID = :biblio)
              AND u.ID_Utilisateur != :biblio
            ORDER BY u.Nom_u, u.Prenom_u";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['biblio' => $biblio_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public static function getConversations($user_id, $biblio_id) {
    $db = new Database();
    $conn = $db->getConnection();
    $sql = "SELECT m.*, 
                   uExp.Nom_u AS NomExp, uExp.Prenom_u AS PrenomExp
            FROM message m
            LEFT JOIN utilisateur uExp ON m.Expediteur_ID = uExp.ID_Utilisateur
            WHERE ((m.Expediteur_ID = :biblio AND m.Destinataire_ID = :user)
                OR (m.Expediteur_ID = :user AND m.Destinataire_ID = :biblio))
            ORDER BY m.Date_Envoi ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['biblio' => $biblio_id, 'user' => $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public static function sendMessage($biblio_id, $user_id, $contenu) {
    if (empty($contenu)) {
        throw new Exception("Veuillez saisir un message !");
    }

    $msg = new self();
    $msg->Expediteur_ID = $biblio_id;
    $msg->Destinataire_ID = $user_id;
    $msg->Contenu = $contenu;
    $msg->Date_Envoi = date("Y-m-d H:i:s");

    if (!$msg->create()) {
        throw new Exception("Erreur lors de l'envoi du message.");
    }

    return "Message envoyé avec succès!";
}
}
?>