<?php
// Fonction pour récupérer les notifications non lues
function getUnreadNotifications($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer les paramètres de notification de l'utilisateur
function getNotificationSetting($conn, $user_id) {
    $stmt = $conn->prepare("SELECT is_enabled FROM user_notifications WHERE user_id = :user_id AND notification_type = 'expire_soon'");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['is_enabled'] : 1; // Activer par défaut si pas trouvé
}

// Fonction pour récupérer les médicaments qui expirent bientôt
function getExpiringSoonMeds($conn) {
    $stmt = $conn->prepare("SELECT nom, date_expiration, numero_lot FROM medicaments WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour marquer une notification comme lue
function markNotificationAsRead($conn, $notification_id, $user_id) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :notification_id AND user_id = :user_id");
    $stmt->bindParam(':notification_id', $notification_id);
    $stmt->bindParam(':user_id', $user_id);
    
    if (!$stmt->execute()) {
        // En cas d'erreur, afficher un message d'erreur
        error_log("Erreur lors de la mise à jour de la notification : " . implode(", ", $stmt->errorInfo()));
        return false;
    }
    
    return true; // Indiquer que l'opération a réussi
}
?>
