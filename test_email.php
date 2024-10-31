require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 3; // Debug détaillé pour diagnostiquer
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('SMTP_USERNAME');
    $mail->Password = getenv('SMTP_PASSWORD');
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('eloi@bewitness.fr', 'Outdoor Secours');
    $mail->addAddress('eloi@famillesarrazin.com);

    $mail->isHTML(true);
    $mail->Subject = 'Test';
    $mail->Body = 'Ceci est un test d\'envoi de mail avec PHPMailer';

    $mail->send();
    echo 'L\'email a été envoyé avec succès.';
} catch (Exception $e) {
    echo "L'envoi de l'email a échoué : {$mail->ErrorInfo}";
}
