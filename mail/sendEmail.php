<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Charge le fichier .env avec les variables de configuration
$dotenv = Dotenv\Dotenv::createImmutable('/var/www/html/outdoorsecevent/mail');
$dotenv->load();

function sendEmail($to, $subject, $body, $from = 'notification@outdoorsecours.fr', $fromName = 'Outdoor Secours') {
    $mail = new PHPMailer(true);

    try {
        // Configuration de l'email
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST'); 
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME'); 
        $mail->Password = getenv('SMTP_PASSWORD'); 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = getenv('SMTP_PORT');

        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email : {$mail->ErrorInfo}");
        return false;
    }
}
