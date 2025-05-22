<?php
// classes/Livre.php
// No need for ini_set here if already in api_livre.php, but ensure Database.php is robust
error_log("--- [Livre.php] File included/parsed ---");


require_once __DIR__ . '/Database.php';


class Livre {
    private $conn;
    private $table_name = "livre";

    // ... other properties ...

    public function __construct() {
        error_log("[Livre.php] Constructor called.");
        try {
            $database = new Database(); // This will call Database constructor
            $this->conn = $database->getConnection();
            if ($this->conn) {
                error_log("[Livre.php] Database connection established successfully in constructor.");
            } else {
                error_log("[Livre.php] FAILED to establish database connection in constructor.");
                // Consider throwing an exception here if connection fails critically
            }
        } catch (Throwable $e) {
            error_log("[Livre.php] ERROR in constructor (Database instantiation/connection): " . $e->getMessage());
            // Propagate or handle error appropriately
            throw $e; // Re-throw if critical
        }
    }




        public function search($criteria = []) {
        error_log("[Livre.php] search() method called with criteria: " . print_r($criteria, true));

        // Include the Image column in the SELECT statement
        $query = "SELECT ID_Livre, Titre, Auteur, Categorie, Statut_Livre, Image FROM " . $this->table_name . " WHERE 1=1";
        $params = [];

        if (!empty($criteria['Titre'])) {
            $query .= " AND LOWER(Titre) LIKE LOWER(?)";
            $params[] = "%" . $criteria['Titre'] . "%";
            error_log("[Livre.php] Added Titre criterion: " . $criteria['Titre']);
        }
        if (!empty($criteria['Auteur'])) {
            $query .= " AND LOWER(Auteur) LIKE LOWER(?)";
            $params[] = "%" . $criteria['Auteur'] . "%";
            error_log("[Livre.php] Added Auteur criterion: " . $criteria['Auteur']);
        }
        if (!empty($criteria['Categorie'])) {
            $query .= " AND LOWER(Categorie) LIKE LOWER(?)";
            $params[] = "%" . $criteria['Categorie'] . "%";
            error_log("[Livre.php] Added Categorie criterion: " . $criteria['Categorie']);
        }

        error_log("[Livre.php] Final SQL Query: " . $query);
        error_log("[Livre.php] Parameters for query: " . print_r($params, true));

        if (!$this->conn) {
            error_log("[Livre.php] Database connection is not available in search().");
            throw new Exception("Database connection not available.");
        }

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            $errorInfo = $this->conn->errorInfo();
            error_log("[Livre.php] Erreur lors de la préparation de la requête: " . print_r($errorInfo, true));
            throw new Exception("Erreur lors de la préparation de la requête: " . ($errorInfo[2] ?? 'Unknown error'));
        }
        error_log("[Livre.php] Statement prepared successfully.");

        if (!$stmt->execute($params)) {
            $errorInfo = $stmt->errorInfo();
            error_log("[Livre.php] La recherche a échoué (execute): " . print_r($errorInfo, true));
            throw new Exception("La recherche a échoué, veuillez réessayer: " . ($errorInfo[2] ?? 'Unknown error'));
        }
        error_log("[Livre.php] Statement executed successfully.");

        $livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("[Livre.php] Number of rows fetched: " . count($livres));

        // Process books to Base64 encode the image
        $processedLivres = [];
        foreach ($livres as $livre) {
            if (isset($livre['Image']) && $livre['Image'] !== null) {
                // Check if it's already base64 encoded (less likely from DB blob but good for idempotency)
                // A simple check might be to see if it starts with common base64 characters or is very long.
                // For raw blob, just encode.
                $livre['Image'] = base64_encode($livre['Image']);
                error_log("[Livre.php] Image for book ID " . $livre['ID_Livre'] . " base64 encoded.");
            } else {
                $livre['Image'] = null; // Or an empty string, depending on how you want to handle missing images
                error_log("[Livre.php] No image or null image for book ID " . $livre['ID_Livre'] . ".");
            }
            $processedLivres[] = $livre;
        }

        if (count($processedLivres) > 0 && isset($processedLivres[0]['Image'])) { // Log only a snippet of the base64
             error_log("[Livre.php] Processed rows (Image column base64 encoded, showing snippet of first): " . substr($processedLivres[0]['Image'] ?? '', 0, 50) . "...");
        }


        if (empty($processedLivres)) {
            error_log("[Livre.php] No books found matching criteria.");
            return [];
        }

        return $processedLivres; // Return the array with base64 encoded images
    }




    public static function handleSearchRequest($queryParams) {
        error_log("[Livre.php] handleSearchRequest() called with queryParams: " . print_r($queryParams, true));
        $message = "";
        $livres = [];

        try {
            $livreInstance = new self(); // Changed variable name for clarity
            $livres = $livreInstance->search($queryParams);

            if (empty($livres)) {
                $message = "Aucun résultat trouvé";
                error_log("[Livre.php] handleSearchRequest: No results found, message set.");
            } else {
                $message = "Livres trouvés: " . count($livres);
                error_log("[Livre.php] handleSearchRequest: Books found, count: " . count($livres));
            }
        } catch (Exception $e) {
            $message = "La recherche a échoué: " . $e->getMessage();
            error_log("[Livre.php] handleSearchRequest: Exception caught: " . $e->getMessage());
            // Optionally, log the full stack trace if needed: error_log($e->getTraceAsString());
            $livres = []; // Ensure livres is empty on error
        }

        $returnArray = ['livres' => $livres, 'message' => $message];
        error_log("[Livre.php] handleSearchRequest returning: " . print_r($returnArray, true));
        return $returnArray;
    }
}
?>