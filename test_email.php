<?php
require 'mail/sendEmail.php'; // Chemin vers le fichier sendEmail.php

// Variables pour le test
$to = 'eloi@famillesarrazin.com'; // Remplacez par votre email pour tester la réception
$subject = 'Test d’envoi de mail depuis Outdoor Secours';
$template = 'user_approved'; // Nom du template dans email_templates
$variables = [
    'first_name' => 'Prénom Test',
    'last_name' => 'Nom Test'
];

// Appel de la fonction sendEmail pour le test
if (sendEmail($to, $subject, $template, $variables)) {
    echo "Email envoyé avec succès !";
} else {
    echo "L'envoi de l'email a échoué.";
}
?>
