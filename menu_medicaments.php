<?php
// Démarrer la session et récupérer les informations utilisateur
session_start();
$user_id = $_SESSION['user_id'];

// Connexion unique à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer la photo de profil de l'utilisateur
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les notifications non lues
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
$unreadNotifications = count($notifications);
?>

<?php
// Démarrer la session et récupérer les informations utilisateur
session_start();
$user_id = $_SESSION['user_id'];

// Connexion unique à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer la photo de profil de l'utilisateur
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les notifications non lues
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
$unreadNotifications = count($notifications);
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
                        <a class="dropdown-item" href="gestion_lieux_stockage.php"><i class="bi bi-buildings-fill"></i> Lieux de stockage</a>
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
                <!-- Icône de notifications avec cloche et badge -->
                <li class="nav-item dropdown">
                    <a class="nav-link notification-toggle" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell notification-bell"></i>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="badge badge-danger notification-badge"><?php echo $unreadNotifications; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right notification-dropdown" aria-labelledby="notificationDropdown">
                        <div class="dropdown-header">Notifications</div>
                        <?php if (empty($notifications)): ?>
                            <p class="dropdown-item">Aucune notification</p>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="dropdown-item notification-item" data-id="<?php echo $notification['id']; ?>">
                                    <p class="notification-text"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <button class="btn btn-sm btn-secondary mark-as-read">Marquer comme lu</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </li>

                <!-- Profil utilisateur -->
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

.notification-dropdown .notification-item {
    padding: 10px 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #f1f1f1;
}

.notification-text {
    font-size: 0.9em;
    color: #333;
    margin-right: 10px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mark-as-read {
    font-size: 0.85em;
    color: #fff;
    background-color: #007bff;
    border: none;
    padding: 5px 10px;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
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

.mark-as-read:hover {
    background-color: #0056b3;
}
</style>
