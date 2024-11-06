<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->CharSet = 'UTF-8';
        $this->setupSMTP();
    }

    private function setupSMTP()
    {
        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = 'smtp.office365.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = 'notification@outdoorsecours.fr';
            $this->mailer->Password = 'Lipton2019!';
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = 587;
            $this->mailer->setFrom('notification@outdoorsecours.fr', 'Outdoor Secours');
        } catch (Exception $e) {
            error_log("Erreur de configuration SMTP : {$e->getMessage()}");
        }
    }

    public function sendEmail($to, $subject, $body)
    {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email : " . $this->mailer->ErrorInfo);
            error_log("Détails de l'erreur : " . $e->getMessage());
        }
    }

    public function loadTemplate($templateContent, $variables)
    {
        foreach ($variables as $key => $value) {
            $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $templateContent = str_replace("{{ $key }}", $escapedValue, $templateContent);
        }
        return $templateContent;
    }
}
