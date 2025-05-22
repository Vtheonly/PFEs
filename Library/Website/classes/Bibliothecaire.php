<?php


require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Notification.php';

class Bibliothecaire {
    private $conn;
    private $table_name = "bibliothecaire";

    public $ID_Bibliothecaire;
    public $Nom_b;
    public $Prenom_b;
    public $Email_b;
    public $Mot_de_passe_b;
    public $Salt;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Email_b = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->Email_b]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row && password_verify($this->Mot_de_passe_b, $row['Mot_de_passe_b'])) {
            $this->ID_Bibliothecaire = $row['ID_Bibliothecaire'];
            $this->Nom_b = $row['Nom_b'];
            $this->Prenom_b = $row['Prenom_b'];
            return true;
        }
        return false;
    }

    public function addBook($Titre, $Auteur, $Categorie, $Statut_Livre, $Image = null) {
        if (empty($Titre) || empty($Auteur) || empty($Categorie) || empty($Statut_Livre)) {
            throw new Exception("Veuillez remplir tous les champs");
        }
    
        // Vérifier si le livre existe déjà
        $queryCheck = "SELECT COUNT(*) FROM livre WHERE Titre = ? AND Auteur = ?";
        $stmtCheck = $this->conn->prepare($queryCheck);
        $stmtCheck->execute([$Titre, $Auteur]);
    
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("Ce livre existe déjà dans le catalogue");
        }
    
        $query = "INSERT INTO livre (Titre, Auteur, Categorie, Statut_Livre, Image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if (!$stmt->execute([$Titre, $Auteur, $Categorie, $Statut_Livre, $Image])) {
            throw new Exception("Erreur lors de l'ajout du livre");
        }
    
        // Notification
        $notification = new Notification();
        $subject = "Nouveau livre ajouté";
        $body = "Le livre " . htmlspecialchars($Titre) . " a été ajouté par le bibliothécaire " . htmlspecialchars($this->Nom_b) . " !";
        $notification->sendNotification($this->Email_b, $subject, $body);
    
        return true;
    }
    

    public function updateBook($ID_Livre, $Titre, $Auteur, $Categorie, $Statut_Livre, $Image = null) {
        if (empty($Titre) || empty($Auteur) || empty($Categorie) || empty($Statut_Livre)) {
            throw new Exception("Veuillez remplir tous les champs");
        }
    
        if ($Image !== null) {
            $query = "UPDATE livre SET Titre = ?, Auteur = ?, Categorie = ?, Statut_Livre = ?, Image = ? WHERE ID_Livre = ?";
            $params = [$Titre, $Auteur, $Categorie, $Statut_Livre, $Image, $ID_Livre];
        } else {
            $query = "UPDATE livre SET Titre = ?, Auteur = ?, Categorie = ?, Statut_Livre = ? WHERE ID_Livre = ?";
            $params = [$Titre, $Auteur, $Categorie, $Statut_Livre, $ID_Livre];
        }
    
        $stmt = $this->conn->prepare($query);
        if (!$stmt->execute($params)) {
            throw new Exception("Erreur lors de la modification du livre");
        }
    
        // Notification
        $notification = new Notification();
        $subject = "Livre modifié";
        $body = "Le livre " . htmlspecialchars($Titre) . " a été modifié par le bibliothécaire " . htmlspecialchars($this->Nom_b) . " !";
        $notification->sendNotification($this->Email_b, $subject, $body);
    
        return true;
    }
    

    public function deleteBook($ID_Livre) {
        $query = "DELETE FROM livre WHERE ID_Livre = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt->execute([$ID_Livre])) {
            throw new Exception("Erreur lors de la suppression du livre");
        }

        // Envoyer une notification au bibliothécaire
        $notification = new Notification();
        $subject = "Livre supprimé";
        $body = "Le livre a été supprimé par le bibliothécaire " . htmlspecialchars($this->Nom_b) . " !";
        $notification->sendNotification($this->Email_b, $subject, $body);

        return true;
    }

    public function acceptReservation($ID_Reservation) {
        $query = "UPDATE reservation SET Statut = 'Confirmée' WHERE ID_Reservation = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt->execute([$ID_Reservation])) {
            throw new Exception("Erreur lors de l'acceptation de la réservation");
        }
        // Envoyer notification à l'utilisateur
        $notification = new Notification();
        $subject = "Réservation confirmée";
        $body = "Votre réservation a été confirmée !";
        $ID_Utilisateur = $this->getUserIdFromReservation($ID_Reservation);
        $notification->sendNotification($this->getEmailUtilisateur($ID_Utilisateur), $subject, $body);
        return true;
    }

    private function getEmailUtilisateur($ID_Utilisateur) {
        $query = "SELECT Email_u FROM utilisateur WHERE ID_Utilisateur = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$ID_Utilisateur]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user['Email_u'] ?? null;
    }

    private function getUserIdFromReservation($ID_Reservation) {
        $query = "SELECT ID_Utilisateur FROM reservation WHERE ID_Reservation = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$ID_Reservation]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        return $reservation['ID_Utilisateur'] ?? null;
    }

    public function rejectReservation($ID_Reservation) {
        $query = "UPDATE reservation SET Statut = 'Annulée' WHERE ID_Reservation = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt->execute([$ID_Reservation])) {
            throw new Exception("Erreur lors du rejet de la réservation");
        }

        // Envoyer une notification à l'utilisateur
        $notification = new Notification();
        $subject = "Réservation rejetée";
        $body = "Votre réservation a été rejetée !";
        $ID_Utilisateur = $this->getUserIdFromReservation($ID_Reservation);
        $notification->sendNotification($this->getEmailUtilisateur($ID_Utilisateur), $subject, $body);

        return true;
    }

    public function updateReservationStatus($ID_Reservation, $Statut) {
        $query = "UPDATE reservation SET Statut = ? WHERE ID_Reservation = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt->execute([$Statut, $ID_Reservation])) {
            throw new Exception("Erreur lors de la mise à jour du statut de la réservation");
        }

        // Envoyer une notification à l'utilisateur
        $notification = new Notification();
        $subject = "Statut de la réservation mis à jour";
        $body = "Le statut de votre réservation a été mis à jour à : " . htmlspecialchars($Statut) . " !";
        $ID_Utilisateur = $this->getUserIdFromReservation($ID_Reservation);
        $notification->sendNotification($this->getEmailUtilisateur($ID_Utilisateur), $subject, $body);
        return true;
    }
    
   
  
    public function exists() {
        $query = "SELECT 1 FROM bibliothecaire WHERE Email_b = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->Email_b]);
        return $stmt->fetch() !== false;
    }

 

public function updatePassword($newPassword) {
    $query = "UPDATE bibliothecaire SET Mot_de_passe_b = ? WHERE Email_b = ?";
    $stmt = $this->conn->prepare($query);
    return $stmt->execute([$newPassword, $this->Email_b]);
}

    public function register() {
        $query = "INSERT INTO bibliothecaire (Nom_b, Prenom_b, Email_b, Mot_de_passe_b) VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $hashed_password = password_hash($this->Mot_de_passe_b, PASSWORD_DEFAULT);
        return $stmt->execute([$this->Nom_b, $this->Prenom_b, $this->Email_b, $hashed_password]);
    }
   
public static function getBibliothecaireInfoFromSession() {
    session_start();
    if (!isset($_SESSION["ID_Bibliothecaire"])) {
        header("Location: login_bibliothecaire.php");
        exit();
    }
    return [
        'Nom' => $_SESSION["Nom_b"],
        'Prénom' => $_SESSION["Prenom_b"],
        'Email' => $_SESSION["Email_b"]
    ];
}
public function handleBookForm($postData, $fileData) {
    $message = "";
    $formData = [
        "form_ID_Livre" => "",
        "form_Titre" => "",
        "form_Auteur" => "",
        "form_Categorie" => "",
        "form_Statut_Livre" => "",
    ];

    $Titre = $postData["Titre"] ?? null;
    $Auteur = $postData["Auteur"] ?? null;
    $Categorie = $postData["Categorie"] ?? null;
    $Statut_Livre = $postData["Statut_Livre"] ?? null;
    $ID_Livre = $postData["ID_Livre"] ?? null;

    // Gestion de l'image (optionnelle)
    $imageData = null;
    if (isset($fileData['image']) && $fileData['image']['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($fileData['image']['tmp_name']);
    }

    try {
        if (isset($postData["ajouter"])) {
            if ($this->addBook($Titre, $Auteur, $Categorie, $Statut_Livre, $imageData)) {
                $message = "Livre ajouté avec succès!";
            } else {
                $message = "Erreur lors de l'ajout du livre.";
            }
        } elseif (isset($postData["modifier"]) && $ID_Livre) {
            if ($this->updateBook($ID_Livre, $Titre, $Auteur, $Categorie, $Statut_Livre, $imageData)) {
                $message = "Livre modifié avec succès!";
            } else {
                $message = "Erreur lors de la modification du livre.";
            }
        } elseif (isset($postData["supprimer"]) && $ID_Livre) {
            if ($this->deleteBook($ID_Livre)) {
                $message = "Livre supprimé avec succès!";
            } else {
                $message = "Erreur lors de la suppression du livre.";
            }
        } elseif (isset($postData["remplir"])) {
            // Pré-remplir le formulaire avec les données du livre sélectionné
            $formData["form_ID_Livre"] = $postData["ID_Livre"];
            $formData["form_Titre"] = $postData["Titre"];
            $formData["form_Auteur"] = $postData["Auteur"];
            $formData["form_Categorie"] = $postData["Categorie"];
            $formData["form_Statut_Livre"] = $postData["Statut_Livre"];
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }

    $formData["message"] = $message;
    return $formData;
}
public static function handleLoginRequest($postData) {
    $message = "";

    if (isset($postData['Email_b']) && isset($postData['Mot_de_passe_b'])) {
        // Connexion bibliothécaire
        $bibliothecaire = new self();
        $bibliothecaire->Email_b = $postData["Email_b"];
        $bibliothecaire->Mot_de_passe_b = $postData["Mot_de_passe_b"];

        try {
            if ($bibliothecaire->login()) {
                session_start();
                $_SESSION["ID_Bibliothecaire"] = $bibliothecaire->ID_Bibliothecaire;
                $_SESSION["Nom_b"] = $bibliothecaire->Nom_b;
                $_SESSION["Prenom_b"] = $bibliothecaire->Prenom_b;
                $_SESSION["Email_b"] = $bibliothecaire->Email_b;

                header("Location: dashboard_bibliothecaire.php");
                exit();
            } else {
                $message = "Email ou mot de passe incorrect.";
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
    } elseif (isset($postData['forgot_email'])) {
        // Récupération du mot de passe
        $email = $postData['forgot_email'];
        $bibliothecaire = new self();
        $bibliothecaire->Email_b = $email;

        try {
            if ($bibliothecaire->exists()) {
                $newPassword = bin2hex(random_bytes(4)); // Générer un mot de passe temporaire
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                if ($bibliothecaire->updatePassword($hashedPassword)) {
                    // Envoyer un e-mail avec le nouveau mot de passe
                    $notification = new Notification();
                    $subject = "Réinitialisation de votre mot de passe";
                    $body = "<p>Bonjour,</p>
                             <p>Votre mot de passe a été réinitialisé. Voici votre nouveau mot de passe temporaire :</p>
                             <p><strong>$newPassword</strong></p>
                             <p>Veuillez vous connecter et changer votre mot de passe dès que possible.</p>";
                    if ($notification->sendNotification($email, $subject, $body)) {
                        $message = "Un e-mail avec un nouveau mot de passe a été envoyé.";
                    } else {
                        $message = "Erreur lors de l'envoi de l'e-mail.";
                    }
                } else {
                    $message = "Erreur lors de la mise à jour du mot de passe.";
                }
            } else {
                $message = "Aucun compte trouvé avec cet e-mail.";
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }
    }

    return $message;
}
public static function handleRegistration($postData) {
    $message = "";

    $bibliothecaire = new self();
    $bibliothecaire->Nom_b = trim($postData["Nom_b"]);
    $bibliothecaire->Prenom_b = trim($postData["Prenom_b"]);
    $bibliothecaire->Email_b = trim($postData["Email_b"]);
    $bibliothecaire->Mot_de_passe_b = $postData["Mot_de_passe_b"];
    $confirmPassword = $postData["Confirm_Mot_de_passe_b"];

    try {
        // Validation des champs
        if (empty($bibliothecaire->Nom_b) || empty($bibliothecaire->Prenom_b) || empty($bibliothecaire->Email_b) || empty($bibliothecaire->Mot_de_passe_b) || empty($confirmPassword)) {
            throw new Exception("Veuillez remplir tous les champs !");
        }

        // Vérification des mots de passe
        if ($bibliothecaire->Mot_de_passe_b !== $confirmPassword) {
            throw new Exception("Les mots de passe ne correspondent pas !");
        }

        // Vérification de l'adresse e-mail
        if (!filter_var($bibliothecaire->Email_b, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Adresse e-mail invalide !");
        }

        // Enregistrement du bibliothécaire
        if ($bibliothecaire->register()) {
            header("Location: login_bibliothecaire.php");
            exit();
        } else {
            throw new Exception("Erreur lors de l'inscription. Veuillez réessayer.");
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }

    return $message;
}
}





