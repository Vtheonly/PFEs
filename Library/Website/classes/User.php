<?php


require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Notification.php';

class User {
    private $conn;
    private $table_name = "utilisateur";

    public $ID_Utilisateur;
    public $Nom_u;
    public $Prenom_u;
    public $Email_u;
    public $Matricule; // Nouveau champ ajouté
    public $Mot_de_passe_u;
    public $Salt;
    public $Statut_Compte;
    public $Role;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function register() {
        // Vérifier si les champs sont vides
        if (empty($this->Nom_u) || empty($this->Prenom_u) || empty($this->Email_u) || empty($this->Mot_de_passe_u) || empty($this->Role) || empty($this->Matricule)) {
            throw new Exception("Veuillez remplir tous les champs !");
        }
    
        // Vérifier si l'email ou le matricule est déjà utilisé
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE Email_u = ? OR Matricule = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->Email_u, $this->Matricule]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Cet email ou ce matricule est déjà associé à un compte.");
        }
    
        // Hasher le mot de passe
        $this->Salt = bin2hex(random_bytes(32)); // Générer un sel unique
        $hashed_password = hash('sha256', $this->Mot_de_passe_u . $this->Salt); // Hacher le mot de passe avec le sel
    
        // Préparer la requête d'insertion
        $query = "INSERT INTO " . $this->table_name . " (Nom_u, Prenom_u, Email_u, Matricule, Mot_de_passe_u, Salt, Statut_Compte, Role) VALUES (?, ?, ?, ?, ?, ?, 'Désactivé', ?)";
        $stmt = $this->conn->prepare($query);
    
        // Exécuter la requête
        try {
            if ($stmt->execute([$this->Nom_u, $this->Prenom_u, $this->Email_u, $this->Matricule, $hashed_password, $this->Salt, $this->Role])) {
                return true;
            } else {
                throw new Exception("Erreur lors de l'enregistrement de l'utilisateur.");
            }
        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'inscription : " . $e->getMessage());
        }}

 
        public function login() {
            // Vérifier si les champs sont vides
            if (empty($this->Email_u) || empty($this->Mot_de_passe_u)) {
                throw new Exception("Veuillez entrer votre email et mot de passe !");
            }
        
            // Récupérer l'utilisateur par email
            $query = "SELECT * FROM utilisateur WHERE Email_u = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$this->Email_u]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
            // Vérifier si l'utilisateur existe
            if (!$user) {
                throw new Exception("Email ou mot de passe incorrect !");
            }
        
            // Vérifier si le compte est désactivé
            if ($user['Statut_Compte'] === 'Désactivé') {
                throw new Exception("Votre compte est désactivé. Veuillez contacter un bibliothécaire pour l'activer.");
            }
        
            // Vérifier le mot de passe
            $hashed_input = hash('sha256', $this->Mot_de_passe_u . $user['Salt']);
            if ($hashed_input !== $user['Mot_de_passe_u']) {
                throw new Exception("Email ou mot de passe incorrect !");
            }
        
            // Initialiser les propriétés de l'utilisateur
            $this->ID_Utilisateur = $user['ID_Utilisateur'];
            $this->Nom_u = $user['Nom_u'];
            $this->Prenom_u = $user['Prenom_u'];
            $this->Email_u = $user['Email_u'];
            $this->Role = $user['Role'];
            $this->Matricule = $user['Matricule']; 
            return true;
        }
    public function logout() {
        // Démarrer ou reprendre la session
        session_start();

        // Détruire toutes les données de session
        session_unset();
        session_destroy();

        // Rediriger vers la page d'accueil
        header("Location: index.php");
        exit;
    }

    public function exists() {
        $query = "SELECT * FROM utilisateur WHERE Email_u = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$this->Email_u]);
        return $stmt->rowCount() > 0;
    }

   
    public function updatePassword($newPassword) {
        $query = "UPDATE utilisateur SET Mot_de_passe_u = ? WHERE Email_u = ?";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([$newPassword, $this->Email_u]);
        if (!$result) {
            error_log("Erreur lors de la mise à jour du mot de passe pour l'email : " . $this->Email_u);
        }
        return $result;
    }

    public static function getAll() {
        require_once(__DIR__ . '/../config.php');
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->query("SELECT * FROM utilisateur");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function deleteById($id) {
        require_once(__DIR__ . '/../config.php');
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("DELETE FROM utilisateur WHERE ID_Utilisateur = ?");
        $stmt->execute([$id]);
    }

public static function updateById($id, $nom, $prenom, $email, $role, $statut) {
    require_once(__DIR__ . '/../config.php');
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("UPDATE utilisateur SET Nom_u = ?, Prenom_u = ?, Email_u = ?, Role = ?, Statut_Compte = ? WHERE ID_Utilisateur = ?");
    $stmt->execute([$nom, $prenom, $email, $role, $statut, $id]);
}
    public static function getById($id) {
        require_once(__DIR__ . '/../config.php');
        $db = new Database();
        $conn = $db->getConnection();
        $stmt = $conn->prepare("SELECT * FROM utilisateur WHERE ID_Utilisateur = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
public static function activateUser($id) {
    require_once(__DIR__ . '/../config.php');
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("UPDATE utilisateur SET Statut_Compte = 'Actif' WHERE ID_Utilisateur = ?");
    $stmt->execute([$id]);
}
public static function updateUser($data) {
    // Valider les rôles et statuts
    $validRoles = ['Etudiant', 'Enseignant'];
    $validStatuses = ['Actif', 'Désactivé'];

    if (!in_array($data['Role'], $validRoles) || !in_array($data['Statut_Compte'], $validStatuses)) {
        return ['error' => 'invalid_data'];
    }

    // Mettre à jour l'utilisateur
    self::updateById(
        $data['update_id'],
        $data['Nom_u'],
        $data['Prenom_u'],
        $data['Email_u'],
        $data['Role'],
        $data['Statut_Compte']
    );

    return ['success' => 'modif'];
}

public static function handleResult($result, $redirectUrl) {
    if (isset($result['success'])) {
        header("Location: $redirectUrl?success=" . $result['success']);
        exit();
    } elseif (isset($result['error'])) {
        header("Location: $redirectUrl?error=" . $result['error']);
        exit();
    }
}
public static function handleRegistration($postData) {
    $message = "";

    $user = new self();
    $user->Nom_u = trim($postData["Nom_u"]);
    $user->Prenom_u = trim($postData["Prenom_u"]);
    $user->Email_u = trim($postData["Email_u"]);
    $user->Matricule = trim($postData["Matricule"]); // Nouveau champ Matricule
    $user->Mot_de_passe_u = $postData["Mot_de_passe_u"];
    $confirmPassword = $postData["Confirm_Mot_de_passe_u"];
    $user->Role = $postData["Role"];

    try {
        // Validation des champs
        if (empty($user->Nom_u) || empty($user->Prenom_u) || empty($user->Email_u) || empty($user->Matricule) || empty($user->Mot_de_passe_u) || empty($confirmPassword) || empty($user->Role)) {
            throw new Exception("Veuillez remplir tous les champs !");
        }

        // Vérification du rôle
        if (!in_array($user->Role, ['Etudiant', 'Enseignant'])) {
            throw new Exception("Rôle invalide !");
        }

        // Vérification des mots de passe
        if ($user->Mot_de_passe_u !== $confirmPassword) {
            throw new Exception("Les mots de passe ne correspondent pas !");
        }

        // Vérification de l'adresse e-mail
        if (!filter_var($user->Email_u, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Adresse e-mail invalide !");
        }

        // Enregistrement de l'utilisateur
        if ($user->register()) {
            $message = "Votre compte a été créé avec succès. Il doit être activé par un bibliothécaire avant que vous puissiez vous connecter.";
        } else {
            throw new Exception("Erreur lors de l'inscription. Veuillez réessayer.");
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }

    return $message;
}
}
?>