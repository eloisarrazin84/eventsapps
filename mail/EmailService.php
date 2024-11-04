<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php'; // Assurez-vous que votre service d'envoi d'e-mails est correctement inclus
require_once 'EmailTemplate.php';

class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->CharSet = 'UTF-8'; // Encodage UTF-8 pour le support des accents

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

    public function sendEmail($to, $subject, $body)
    {
        try {
            $this->mailer->addAddress($to); // Destinataire
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email : {$this->mailer->ErrorInfo}");
        }
    }
}

