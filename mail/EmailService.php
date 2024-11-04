<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/path/to/vendor/autoload.php'; // Ajustez le chemin vers autoload.php

class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->mailer->CharSet = 'UTF-8';

        // Configuration SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.example.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'your_email@example.com';
        $this->mailer->Password = 'your_password';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        $this->mailer->setFrom('from@example.com', 'Your Name');
    }

    public function sendEmail($to, $subject, $templateName, $variables = [])
    {
        $this->mailer->addAddress($to);
        $this->mailer->isHTML(true);
        $this->mailer->Subject = $subject;

        // Charger le contenu du template et remplacer les variables
        $this->mailer->Body = EmailTemplate::loadTemplate($templateName, $variables);

        // Envoi de l'email
        return $this->mailer->send();
    }
}
