<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php'; // Charge le fichier autoload de Composer
require_once 'EmailTemplate.php'; // Charge la classe EmailTemplate

class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->CharSet = 'UTF-8'; // Définit l'encodage UTF-8

        try {
            // Configuration SMTP
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.office365.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'notification@outdoorsecours.fr';
            $this->mailer->Password = 'Lipton2019!';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;

            // Paramètres de l'expéditeur
            $this->mailer->setFrom('notification@outdoorsecours.fr', 'Outdoor Secours');
        } catch (Exception $e) {
            error_log("Erreur de configuration SMTP : {$e->getMessage()}");
        }
    }

    public function sendEmail($to, $subject, $templateName, $variables = [])
    {
        try {
            $this->mailer->addAddress($to); // Ajoute le destinataire
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;

            // Charge le contenu du modèle d'e-mail
            $this->mailer->Body = $this->loadEmailTemplate($templateName, $variables);

            // Envoi de l'e-mail
            $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email : {$this->mailer->ErrorInfo}");
        }
    }

    private function loadEmailTemplate($templateName, $variables)
    {
        // Chemin vers le modèle d'e-mail
        $templatePath = __DIR__ . "/email_templates/$templateName.html";

        // Vérification de l'existence du fichier
        if (!file_exists($templatePath)) {
            throw new Exception("Template non trouvé : $templatePath");
        }

        // Chargement du contenu du modèle
        $templateContent = file_get_contents($templatePath);
        foreach ($variables as $key => $value) {
            $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $templateContent = str_replace("{{ $key }}", $escapedValue, $templateContent);
        }

        return $templateContent;
    }
}
