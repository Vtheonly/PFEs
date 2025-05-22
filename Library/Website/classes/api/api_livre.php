<?php
// Force display of all errors for debugging (you might remove this in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

error_log("[api_livre.php] Headers sent.");

try {
    // Corrected include:
    require_once dirname(__DIR__, 2) . '/config.php';
    error_log("[api_livre.php] config.php included successfully.");
} catch (Throwable $e) {
    error_log("[api_livre.php] FATAL ERROR including config.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server configuration error.']);
    exit;
}

try {
    // Corrected include:
    require_once dirname(__DIR__) . '/Livre.php';
    error_log("[api_livre.php] Livre.php included successfully.");
} catch (Throwable $e) {
    error_log("[api_livre.php] FATAL ERROR including Livre.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server class loading error.']);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    error_log("[api_livre.php] Request method is GET.");

    $queryParams = [
        'Titre' => isset($_GET['Titre']) ? $_GET['Titre'] : null,
        'Auteur' => isset($_GET['Auteur']) ? $_GET['Auteur'] : null,
        'Categorie' => isset($_GET['Categorie']) ? $_GET['Categorie'] : null,
    ];
    error_log("[api_livre.php] Query Params: " . print_r($queryParams, true));

    // Call the static method to handle the search and get results
    $result = Livre::handleSearchRequest($queryParams);
    error_log("[api_livre.php] Result from Livre::handleSearchRequest: " . print_r($result, true));

    // Set the HTTP response code (optional, default is 200)
    // http_response_code(200);

    // Return the response in JSON format
    $jsonResponse = json_encode($result);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("[api_livre.php] JSON Encoding Error: " . json_last_error_msg());
        http_response_code(500);
        echo json_encode(['error' => 'Error encoding JSON response.', 'details' => json_last_error_msg()]);
    } else {
        error_log("[api_livre.php] Sending JSON response to client: " . $jsonResponse);
        echo $jsonResponse;
    }

} else {
    error_log("[api_livre.php] Request method not GET: " . $_SERVER['REQUEST_METHOD']);
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Méthode non autorisée. Utilisez GET.']);
}

error_log("--- [api_livre.php] Script execution finished ---");
?>