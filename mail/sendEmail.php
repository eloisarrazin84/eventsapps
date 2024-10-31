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
        $mail->Body = loadTemplate($template, $variables);

        // Envoi de l'email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email : {$mail->ErrorInfo}");
        return false;
    }
}

// Fonction pour charger et personnaliser le template d'email
function loadTemplate($templateName, $variables = []) {
    $templatePath = "/var/www/html/outdoorsecevent/email_templates/$templateName.html";
    if (file_exists($templatePath)) {
        $content = file_get_contents($templatePath);
        
        // Remplacement des variables du template
        foreach ($variables as $key => $value) {
            $content = str_replace("{{ $key }}", $value, $content);
        }
        return $content;
    }
    return '';
}
