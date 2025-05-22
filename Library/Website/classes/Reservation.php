<?php
// filepath: c:\xampp\htdocs\bibliotheque\classes\Reservation.php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Notification.php';
require_once __DIR__ . '/Livre.php';


class Reservation {
    private $conn;
    private $table_name = "reservation"; // or your actual table name

    // Object properties
    public $ID_Reservation;
    public $User_ID;
    public $Livre_ID;
    public $Date_Reservation;
    public $Date_Retour_Prevu;
    public $Date_Retour_Effectif;
    public $Statut_Reservation; // e.g., 'En attente', 'Confirmée', 'Annulée', 'Retourné'

    public function __construct($db = null) {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database(); // This will use constants from config.php
            $this->conn = $database->getConnection();
        }
        if (!$this->conn) {
            // Handle error: database connection failed
            // You might throw an exception or log an error
            error_log("Reservation Class: Failed to get database connection.");
            // Depending on your error strategy, you might want to stop execution or set a flag
        }
    }




    /**
     * Fetches the reservation history for a specific user.
     * Joins with the livre table to get book details.
     *
     * @param int $user_id The ID of the user.
     * @return array|false An array of reservation history items or false on failure.
     */
    public function getHistoryByUser($user_id) {
        if (!$this->conn) {
            error_log("getHistoryByUser: No database connection.");
            return false;
        }

        // Your livre table has Auteur and Categorie as VARCHAR, and Image as BLOB
        // It has Statut_Livre for the book's current general status
        // Reservation table has Statut_Reservation for the specific reservation status
       $query = "SELECT
                    r.ID_Reservation,
                    r.ID_Livre,          -- From reservation table
                    l.Titre AS Titre_Livre,
                    l.Auteur AS Auteur_Livre,
                    l.Categorie AS Categorie_Livre,
                    l.Image AS Image_Livre,      -- This will be the BLOB data
                    r.Date_Reservation,
                    r.Date_Retour_Prevue,    -- From reservation table
                    r.Statut                 -- From reservation table (e.g., 'Retourné', 'Annulée', 'Emprunté')
                  FROM
                    " . $this->table_name . " r
                  JOIN
                    livre l ON r.ID_Livre = l.ID_Livre  -- Correct column names for join
                  WHERE
                    r.ID_Utilisateur = :user_id       -- Correct column name for user ID
                  ORDER BY
                    r.Date_Reservation DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            $reservations = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Handle the BLOB image data: convert to Base64 for JSON transfer
                if (isset($row['Image_Livre']) && $row['Image_Livre'] !== null) {
                    $row['Image_Livre_Base64'] = base64_encode($row['Image_Livre']);
                } else {
                    $row['Image_Livre_Base64'] = null;
                }
                unset($row['Image_Livre']); // Remove raw BLOB from final array if sending Base64

                $reservations[] = $row;
            }
            return $reservations;

        } catch (PDOException $e) {
            error_log("Database error in getHistoryByUser: " . $e->getMessage());
            return false;
        }
    }



    // Example: Create a new reservation
    public function create() {
        if (!$this->conn) {
            return false; // No database connection
        }

        $query = "INSERT INTO " . $this->table_name . "
                    SET
                        User_ID = :User_ID,
                        ID_Livre = :ID_Livre,
                        Date_Reservation = :Date_Reservation,
                        Date_Retour_Prevue = :Date_Retour_Prevue,
                        Statut = :Statut";
                        // Date_Retour_Effectif is usually null on creation

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->User_ID = htmlspecialchars(strip_tags($this->User_ID));
        $this->Livre_ID = htmlspecialchars(strip_tags($this->Livre_ID));
        $this->Date_Reservation = htmlspecialchars(strip_tags($this->Date_Reservation));
        $this->Date_Retour_Prevu = htmlspecialchars(strip_tags($this->Date_Retour_Prevu));
        $this->Statut_Reservation = htmlspecialchars(strip_tags($this->Statut_Reservation));

        // Bind data
        $stmt->bindParam(":User_ID", $this->User_ID);
        $stmt->bindParam(":Livre_ID", $this->Livre_ID);
        $stmt->bindParam(":Date_Reservation", $this->Date_Reservation);
        $stmt->bindParam(":Date_Retour_Prevu", $this->Date_Retour_Prevu);
        $stmt->bindParam(":Statut_Reservation", $this->Statut_Reservation);

        if ($stmt->execute()) {
            $this->ID_Reservation = $this->conn->lastInsertId(); // Get ID of the new reservation
            
            // After successful reservation, update the book's status to 'Réservé'
            // This assumes you have a Livre class and method to update status
            require_once __DIR__ . '/Livre.php'; // Make sure Livre.php is available
            $livre = new Livre($this->conn);
            if (!$livre->updateStatus($this->Livre_ID, 'Réservé')) {
                // Log error or handle: failed to update book status
                error_log("Failed to update status for Livre_ID: " . $this->Livre_ID . " after reservation.");
                // Depending on policy, you might even consider rolling back the reservation
                // or just flagging it for admin attention.
            }
            return true;
        }
        error_log("Reservation create failed: " . implode(", ", $stmt->errorInfo()));
        return false;
    }

   
    public function canBorrow($ID_Utilisateur) {
        // Vérifier le nombre de livres empruntés
        $query = "SELECT COUNT(*) FROM reservation WHERE ID_Utilisateur = ? AND Statut = 'En attente' or Statut = 'Empruntée' or Statut = 'Confirmée' or Statut = 'Retard' ";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$ID_Utilisateur]);
        $borrowedBooksCount = $stmt->fetchColumn();
    
        if ($borrowedBooksCount >= 2) { // Limite de livres empruntés
            return false;
        }
    
        // Vérifier les retards en appelant la fonction verifierRetards
        $this->verifierRetards();
    
        // Vérifier si l'utilisateur a encore des réservations en retard
        $queryRetards = "SELECT COUNT(*) FROM reservation WHERE ID_Utilisateur = ? AND Statut = 'Retard'";
        $stmtRetards = $this->conn->prepare($queryRetards);
        $stmtRetards->execute([$ID_Utilisateur]);
        $nombreRetards = $stmtRetards->fetchColumn();
    
        if ($nombreRetards > 0) {
            return false;
        }
    
        return true;
    }

    public function getAllReservations() {
        $query = "SELECT r.ID_Reservation, r.Date_Reservation, r.Date_Retour_Prevue, r.Statut, 
                         u.Nom_u, u.Prenom_u, l.Titre 
                  FROM reservation r
                  INNER JOIN utilisateur u ON r.ID_Utilisateur = u.ID_Utilisateur
                  INNER JOIN livre l ON r.ID_Livre = l.ID_Livre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateReservationStatus($ID_Reservation, $Statut) {
        $query = "UPDATE " . $this->table_name . " SET Statut = ? WHERE ID_Reservation = ?";
        $stmt = $this->conn->prepare($query);

        if (!$stmt->execute([$Statut, $ID_Reservation])) {
            throw new Exception("Erreur lors de la mise à jour du statut de la réservation.");
        }

        return true;
    }

    private function getEmailUtilisateur($ID_Utilisateur) {
        $query = "SELECT Email_u FROM utilisateur WHERE ID_Utilisateur = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$ID_Utilisateur]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user['Email_u'] ?? null;
    }
public function getReservationStatus($ID_Utilisateur) {
    $query = "SELECT r.ID_Reservation, r.Statut, l.Titre 
              FROM reservation r
              INNER JOIN livre l ON r.ID_Livre = l.ID_Livre
              WHERE r.ID_Utilisateur = ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$ID_Utilisateur]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


public function verifierRetards() {
    // Rechercher les réservations en retard
    $query = "SELECT ID_Reservation, ID_Utilisateur, ID_Livre, Date_Retour_Prevue 
              FROM " . $this->table_name . " 
              WHERE Statut = 'Empruntée' AND Date_Retour_Prevue < ?";
    $stmt = $this->conn->prepare($query);
    $stmt->execute([date('Y-m-d')]);
    $reservationsEnRetard = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($reservationsEnRetard as $reservation) {
        // Mettre à jour le statut de la réservation en "Retard"
        $this->updateReservationStatus($reservation['ID_Reservation'], 'Retard');

        // Envoyer une notification à l'utilisateur
        $notification = new Notification();
        $email = $this->getEmailUtilisateur($reservation['ID_Utilisateur']);
        if ($email) {
            $notification-> sendNotification(
                $email,
                "Retour en retard",
                "Votre réservation pour le livre ID " . $reservation['ID_Livre'] . " est en retard. Veuillez le retourner dès que possible."
            );
        }
    }
}
public function handleReservationForm($postData) {
    $message = "";

    if (isset($postData['ID_Reservation']) && isset($postData['Statut'])) {
        $ID_Reservation = $postData['ID_Reservation'];
        $nouveauStatut = $postData['Statut'];

        try {
            $this->updateReservationStatus($ID_Reservation, $nouveauStatut);
            $message = "Réservation mise à jour avec succès.";
        } catch (Exception $e) {
            $message = "Erreur lors de la mise à jour de la réservation : " . $e->getMessage();
        }
    }

    return $message;
}

public function getAllReservationsWithMessage() {
    try {
        // Vérifier et mettre à jour les retards avant de récupérer les réservations
        $this->verifierRetards();

        // Récupérer toutes les réservations
        return $this->getAllReservations();
    } catch (Exception $e) {
        throw new Exception("Erreur lors de la récupération des réservations: " . $e->getMessage());
    }
}

public static function handleBorrowRequest($postData, $sessionData) {
    if (!isset($sessionData["ID_Utilisateur"])) {
        return "Vous devez être connecté pour emprunter un livre.";
    }

    $message = "";
    $ID_Utilisateur = $sessionData["ID_Utilisateur"];

    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($postData["ID_Livre"])) {
        $ID_Livre = intval($postData["ID_Livre"]);

        $reservation = new self();

        try {
            if ($reservation->reserverLivre($ID_Utilisateur, $ID_Livre)) {
                $message = "Livre emprunté avec succès !";
            } else {
                $message = "Erreur lors de l'emprunt du livre.";
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
    } else {
        $message = "Aucune donnée valide reçue pour emprunter un livre.";
    }

    return $message;
}


public function reserverLivre($ID_Utilisateur, $ID_Livre) {
    // Vérifier si l'utilisateur peut réserver un livre
    if (!$this->canBorrow($ID_Utilisateur)) {
        throw new Exception("Vous ne pouvez pas réserver ce livre. Vérifiez vos emprunts ou retards.");
    }

    $dateRetourPrevue = date('Y-m-d', strtotime('+7 days'));
    $this->conn->beginTransaction();
    try {
        $queryUpdateLivre = "UPDATE livre SET Statut_Livre = 'Réservé' WHERE ID_Livre = ?";
        $stmtUpdateLivre = $this->conn->prepare($queryUpdateLivre);
        if (!$stmtUpdateLivre->execute([$ID_Livre])) {
            throw new Exception("Erreur lors de la mise à jour du statut du livre.");
        }
        $queryInsertReservation = "INSERT INTO reservation (ID_Utilisateur, ID_Livre, Date_Reservation, Date_Retour_Prevue, Statut) 
                                   VALUES (?, ?, ?, ?, 'En attente')";
        $stmtInsertReservation = $this->conn->prepare($queryInsertReservation);
        if (!$stmtInsertReservation->execute([$ID_Utilisateur, $ID_Livre, date('Y-m-d'), $dateRetourPrevue])) {
            throw new Exception("Erreur lors de la création de la réservation.");
        }
        $this->conn->commit();
        return true;
    } catch (Exception $e) {
        $this->conn->rollBack();
        throw new Exception("Erreur : " . $e->getMessage());
    }
}


}
?>