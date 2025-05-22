<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;



require_once dirname(__DIR__) . '/vendor/autoload.php';


class Notification {
    public function sendNotification($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            // Paramètres du serveur SMTP
            $mail->SMTPDebug = SMTP::DEBUG_OFF;  
            $mail->isSMTP();                    
            $mail->Host = 'smtp.gmail.com';     
            $mail->SMTPAuth = true;             
            $mail->Username = 'aissouc600@gmail.com'; 
            $mail->Password = 'gjck gxhy bvad umkf';  
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port = 587;                  

            // Destinataires
            $mail->setFrom('aissouc600@gmail.com', 'Bibliothèque'); 
            $mail->addAddress($to);                           

            // Contenu de l'e-mail
            $mail->isHTML(true);                                   
            $mail->Subject = $subject;                             
            $mail->Body = $body;                                   

            // Envoyer l'e-mail
            $mail->send();
            return true;
        } catch (Exception $e) {
            // Enregistrer l'erreur dans les logs
            error_log("Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}");
            return false;
        }
    }
}
?>