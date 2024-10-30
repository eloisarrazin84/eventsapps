<?php
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Définir les dates limites pour les notifications
$today = date("Y-m-d");
$threshold = date("Y-m-d", strtotime("+7 days")); // 7 jours avant expiration

// Rechercher les médicaments expirés ou proches de l'expiration
$query = "SELECT * FROM medicaments WHERE date_expiration <= :threshold";
$stmt = $conn->prepare($query);
$stmt->bindParam(':threshold', $threshold);
$stmt->execute();
$medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajouter des notifications
foreach ($medicaments as $medicament) {
    $notifMessage = ($medicament['date_expiration'] < $today) ? "Médicament expiré" : "Médicament proche de l'expiration";
    
    $insertNotif = $conn->prepare("INSERT INTO notifications (medicament_id, message, date) VALUES (:medicament_id, :message, NOW())");
    $insertNotif->bindParam(':medicament_id', $medicament['id']);
    $insertNotif->bindParam(':message', $notifMessage);
    $insertNotif->execute();
}
?>
