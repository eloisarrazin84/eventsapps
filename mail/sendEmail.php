<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require 'vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function loadTemplate($templateName, $variables = []) {
    $templatePath = __DIR__ . "/../email_templates/{$templateName}.html";
    if (!file_exists($templatePath)) {
        return false;
    }

    $templateContent = file_get_contents($templatePath);
    foreach ($variables as $key => $value) {
        $templateContent = str_replace("{{{$key}}}", htmlspecialchars($value), $templateContent);
    }

    return $templateContent;
}

function sendEmail($to, $subject, $templateName, $variables = [], $from = 'notification@outdoorsecours.fr', $fromName = 'Outdoor Secours') {
    $mail = new PHPMailer(true);

    try {
        // Charger le template d'email
        $body = loadTemplate($templateName, $variables);
        if (!$body) {
            throw new Exception("Template email introuvable : {$templateName}");
        }

        // Configuration de l'email
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['SMTP_PORT'];

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
