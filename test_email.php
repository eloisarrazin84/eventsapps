<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 3; // Niveau de debug détaillé
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'eloi@bewitness.fr'; // Nom d'utilisateur entre guillemets
    $mail->Password = 'kliy flmk mbff laob'; // Mot de passe entre guillemets
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('eloi@bewitness.fr', 'Outdoor Secours');
    $mail->addAddress('eloi@famillesarrazin.com'); // Adresse email corrigée

    $mail->isHTML(true);
    $mail->Subject = 'Test';
    $mail->Body = 'Ceci est un test d\'envoi de mail avec PHPMailer';

    $mail->send();
    echo 'L\'email a été envoyé avec succès.';
} catch (Exception $e) {
    echo "L'envoi de l'email a échoué : {$mail->ErrorInfo}";
}
