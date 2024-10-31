<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '/var/www/html/outdoorsecevent/vendor/autoload.php'; // Chemin vers autoload.php de Composer

class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

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
            $this->mailer->addAddress($to); // Destinataire
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;

            // Charger le contenu du template
            $this->mailer->Body = EmailTemplate::loadTemplate($templateName, $variables);

            // Envoi de l'e-mail
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email : {$this->mailer->ErrorInfo}");
            return false;
        }
    }
}
