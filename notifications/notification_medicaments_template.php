<?php
session_start();
$expiringSoonMeds = isset($_SESSION['expiringSoonMeds']) ? $_SESSION['expiringSoonMeds'] : [];
?>

<div class="notification-dropdown">
    <div class="dropdown-header">Notifications Expirant Bientôt</div>
    <?php if (empty($expiringSoonMeds)): ?>
        <p>Aucun médicament expirant bientôt.</p>
    <?php else: ?>
        <?php foreach ($expiringSoonMeds as $med): ?>
            <div class="dropdown-item">
                <strong><?php echo htmlspecialchars($med['nom']); ?></strong> - Lot: <?php echo htmlspecialchars($med['numero_lot']); ?>,
                Expire le: <?php echo htmlspecialchars($med['date_expiration']); ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
