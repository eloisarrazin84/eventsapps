<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Rediriger vers la page de connexion si non connecté
    exit();
}

$user_id = $_SESSION['user_id'];

// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer la photo de profil de l'utilisateur
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Inclure le fichier de notifications
include 'notifications/notifications_medicaments.php'; // Inclure le fichier de notifications

// Récupérer les notifications non lues
$notifications = getUnreadNotifications($conn, $user_id);
$unreadNotifications = count($notifications);

// Vérifier si les notifications pour les médicaments expirant bientôt sont activées
$notificationEnabled = getNotificationSetting($conn, $user_id);

// Récupérer les médicaments expirant dans moins de 30 jours si la notification est activée
$expiringSoonMeds = [];
if ($notificationEnabled) {
    $expiringSoonMeds = getExpiringSoonMeds($conn);
}

// Gérer le marquage des notifications comme lues
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $notification_id = $_POST['notification_id'];
    markNotificationAsRead($conn, $notification_id, $user_id);
    header("Location: " . $_SERVER['PHP_SELF']); // Recharger la page pour mettre à jour l'affichage
    exit();
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm p-3">
    <a class="navbar-brand" href="#">
        <img src="https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png" alt="Logo" style="width: 50px; margin-right: 10px;">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="medicamentDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-capsules"></i> Gestion des Médicaments
                    </a>
                    <div class="dropdown-menu" aria-labelledby="medicamentDropdown">
                        <a class="dropdown-item" href="gestion_medicaments.php"><i class="fas fa-pills"></i> Liste des Médicaments</a>
                        <a class="dropdown-item" href="gestion_lieux_stockage.php"><i class="fas fa-warehouse"></i> Lieux de stockage</a>
                    </div>
                </li>
            <?php endif; ?>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard_medicaments.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="dashboard_medicaments.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="home.php"><i class="fas fa-th-large"></i> Mes applications</a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link notification-toggle" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell notification-bell"></i>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="badge badge-danger notification-badge"><?php echo $unreadNotifications; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right notification-dropdown" aria-labelledby="notificationDropdown">
                        <div class="dropdown-header">Notifications</div>
                        <?php if ($notificationEnabled): ?>
                            <?php if (empty($expiringSoonMeds)): ?>
                                <p class="dropdown-item">Aucun médicament expirant bientôt.</p>
                            <?php else: ?>
                                <?php foreach ($expiringSoonMeds as $med): ?>
                                    <div class="dropdown-item">
                                        <strong><?php echo htmlspecialchars($med['nom']); ?></strong> - Lot: <?php echo htmlspecialchars($med['numero_lot']); ?>,
                                        Expire le: <?php echo htmlspecialchars($med['date_expiration']); ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="notification_id" value="<?php echo $med['id']; ?>">
                                            <button type="submit" name="mark_as_read" class="btn btn-link" style="padding: 0; color: #007bff;">Marquer comme lu</button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="dropdown-item">Notifications désactivées.</p>
                        <?php endif; ?>
                    </div>
                </li>
                <li class="nav-item dropdown ml-3">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo $user['profile_picture']; ?>" alt="Photo de profil" class="rounded-circle profile-picture">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right profile-dropdown" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Mon profil</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                    </div>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm ml-2" style="border-radius: 50px;" href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- CSS pour le menu amélioré -->
<style>
.navbar {
    background-color: #f8f9fa;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

.navbar .nav-link {
    color: #007bff;
    font-weight: bold;
    padding: 10px 15px;
    font-size: 1.1em;
}

.notification-toggle {
    position: relative;
    display: inline-block;
    color: #007bff;
}

.notification-bell {
    font-size: 1.7em;
}

.notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: #ff3e3e;
    color: white;
    border-radius: 50%;
    padding: 5px 8px;
    font-size: 0.8em;
    font-weight: bold;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
}

.notification-dropdown {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.15);
    max-width: 320px;
    min-width: 300px;
}

.notification-dropdown .dropdown-header {
    font-size: 0.9em;
    font-weight: bold;
    color: #333;
    padding: 10px 15px;
    border-bottom: 1px solid #f1f1f1;
}

.profile-picture {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #007bff;
}

.profile-dropdown {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    min-width: 200px;
}
</style>
