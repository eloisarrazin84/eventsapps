<?php
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Date de vérification pour les médicaments expirant dans 30 jours ou moins
$dateLimite = date('Y-m-d', strtotime('+30 days'));

// Récupérer les médicaments expirant dans 30 jours
$stmt = $conn->prepare("SELECT id, nom, date_expiration FROM medicaments WHERE date_expiration <= :date_limite");
$stmt->bindParam(':date_limite', $dateLimite);
$stmt->execute();
$medicamentsExpirants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Insérer les notifications pour chaque médicament
foreach ($medicamentsExpirants as $medicament) {
    $message = "Le médicament '{$medicament['nom']}' expire le {$medicament['date_expiration']}.";
    
    // Ajouter la notification pour les administrateurs uniquement
    $stmtNotif = $conn->prepare("
        INSERT INTO notifications (user_id, message, is_read) 
        SELECT id, :message, 0 
        FROM users 
        WHERE role = 'admin'
    ");
    $stmtNotif->bindParam(':message', $message);
    $stmtNotif->execute();
}

echo "Notifications de médicaments expirants créées avec succès.";
?>
