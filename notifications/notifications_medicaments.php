<?php
function getUnreadNotifications($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getNotificationSetting($conn, $user_id) {
    $stmt = $conn->prepare("SELECT is_enabled FROM user_notifications WHERE user_id = :user_id AND notification_type = 'expire_soon'");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['is_enabled'] ?? 1; // Activer par défaut si pas trouvé
}

function getExpiringSoonMeds($conn) {
    $stmt = $conn->prepare("SELECT nom, date_expiration, numero_lot FROM medicaments WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
