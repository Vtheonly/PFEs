<?php

$dbHost     = 'localhost'; 
$dbUsername = 'root';      
$dbPassword = '74532180';          
$dbName     = 'exam_platform'; 


$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);


if ($conn->connect_error) {
    
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion BDD: ' . $conn->connect_error]);
    exit(); 
}



$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password_provided = isset($_POST['password']) ? trim($_POST['password']) : '';


$response = ['success' => false, 'message' => 'Nom d\'utilisateur ou mot de passe invalide.'];


if (!empty($username) && !empty($password_provided)) {

    
    
    $sql = "SELECT password FROM account WHERE username = ?"; 
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        
        $stmt->bind_param("s", $username);

        
        $stmt->execute();

        
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            
            $user = $result->fetch_assoc();
            $stored_password = $user['password']; 

            
            
            
            
            if ($password_provided === $stored_password) {
                
                $response = ['success' => true];
            } else {
                
                 $response['message'] = 'Mot de passe incorrect.'; 
            }

        } else {
            
             $response['message'] = 'Utilisateur non trouvé.'; 
        }

        
        $stmt->close();
    } else {
        
        $response['message'] = 'Erreur interne du serveur (preparation requete).';
         error_log("Erreur de préparation SQL: " . $conn->error); 
    }
} else {
     $response['message'] = 'Nom d\'utilisateur et mot de passe requis.';
}



$conn->close();


header('Content-Type: application/json'); 
echo json_encode($response); 

?>