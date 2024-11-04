<?php
// Inclure les fichiers nécessaires
require_once 'EmailService.php'; // Assurez-vous que le chemin est correct
require_once 'EmailTemplate.php'; // Assurez-vous que cette classe est disponible

// Chemin vers le modèle d'e-mail
$templatePath = __DIR__ . '/email_templates/med_expiration_report.html';

// Vérifier si le fichier de modèle existe
if (!file_exists($templatePath)) {
    die("Le fichier de modèle d'e-mail n'a pas été trouvé.");
}

// Charger le modèle
$emailContent = file_get_contents($templatePath);

// Logiciel pour récupérer les médicaments expirant dans 30 jours
// (vous devez implémenter cette partie selon votre logique d'application)

// Exemple d'utilisation du service d'e-mail
$emailService = new EmailService();
$emailService->sendEmail('contact@outdoorsecours.fr', 'Rapport d\'expiration des médicaments', $emailContent);
