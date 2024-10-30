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
        <img src="https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png" alt="Logo" style="width: 50px;">
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
                        <a class="dropdown-item" href="ajouter_medicament.php"><i class="bi bi-plus-square"></i> Ajouter un Médicament</a>
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
                <!-- Icône de notifications -->
                <li class="nav-item dropdown">
                    <a class="nav-link" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="badge badge-danger" id="notification-badge"><?php echo $unreadNotifications; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown">
                        <?php if (empty($notifications)): ?>
                            <p class="dropdown-item">Aucune notification</p>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="dropdown-item notification-item" data-id="<?php echo $notification['id']; ?>">
                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <button class="btn btn-sm btn-secondary mark-as-read">Marquer comme lu</button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </li>

                <!-- Profil utilisateur -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo $user['profile_picture']; ?>" alt="Photo de profil" class="rounded-circle" style="width: 40px; height: 40px;">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
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

<!-- Script pour marquer les notifications comme lues -->
<script>
document.querySelectorAll('.mark-as-read').forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        
        // Récupération de l'ID de la notification
        const notificationItem = this.closest('.notification-item');
        const notificationId = notificationItem.getAttribute('data-id');

        // Envoi de la requête AJAX
        fetch('mark_as_read.php?id=' + notificationId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Marque la notification comme lue
                    notificationItem.remove();
                    
                    // Mettre à jour le badge de notifications
                    let badge = document.getElementById('notification-badge');
                    let unreadCount = parseInt(badge.innerText);
                    badge.innerText = unreadCount - 1;
                    if (unreadCount - 1 <= 0) {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(error => console.error('Erreur:', error));
    });
});
</script>

<!-- CSS pour les notifications -->
<style>
.navbar {
    background-color: #f8f9fa;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}

.navbar .nav-link {
    color: #007bff;
    font-weight: bold;
    padding: 10px 15px;
}

.navbar .nav-link:hover {
    color: #0056b3;
    background-color: rgba(0, 123, 255, 0.1);
    border-radius: 5px;
}

.badge-danger {
    position: absolute;
    top: -5px;
    right: -10px;
    font-size: 0.8em;
    padding: 5px 8px;
    border-radius: 50%;
    background-color: #dc3545;
    color: white;
}

.dropdown-menu {
    background-color: #ffffff;
    border-radius: 5px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
}

.dropdown-item:hover {
    background-color: #e9ecef;
}


</style>
