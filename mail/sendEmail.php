<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/var/www/html/outdoorsecevent/vendor/autoload.php'; // Chemin vers autoload.php de Composer

function sendEmail($to, $subject, $template, $variables = [], $from = 'notification@outdoorsecours.fr', $fromName = 'Outdoor Secours') {
    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 2; // Ajoutez cette ligne après `$mail = new PHPMailer(true);`

    try {
        // Configuration SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.office365.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'notification@outdoorsecours.fr';
        $mail->Password = 'Lipton2019!';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Debug pour diagnostic
        $mail->SMTPDebug = 2; // 2 pour obtenir des informations sur le serveur
        $mail->Debugoutput = 'html';

        // Définition des adresses email
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);

        // Préparation du corps de l'email avec template
        $mail->isHTML(true);
        $mail->Subject = $subject;

        // Charger le contenu du template via une fonction séparée
        if (function_exists('loadTemplate')) {
            $mail->Body = loadTemplate($template, $variables);
        } else {
            throw new Exception("La fonction loadTemplate n'est pas définie.");
        }

        // Envoi de l'email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email : {$mail->ErrorInfo}");
        return false;
    }
}
