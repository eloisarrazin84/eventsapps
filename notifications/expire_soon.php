<?php
// expire_soon.php

// Fonction pour récupérer les médicaments expirant bientôt
function getExpiringMedicines($conn, $user_id) {
    $stmt = $conn->prepare("SELECT nom, date_expiration, numero_lot FROM medicaments WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les médicaments expirant dans moins de 30 jours
$expiringMedicines = getExpiringMedicines($conn, $user_id);

// Gérer l'affichage des notifications
if (!empty($expiringMedicines)) {
    foreach ($expiringMedicines as $med) {
        // Logique pour notifier l'utilisateur (par exemple, mail ou affichage)
        echo "Notification : Le médicament <strong>" . htmlspecialchars($med['nom']) . "</strong> expire le <strong>" . htmlspecialchars($med['date_expiration']) . "</strong>.<br>";
    }
} else {
    echo "Aucun médicament n'expire dans les 30 prochains jours.";
}
?>
