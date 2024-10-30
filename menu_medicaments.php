<?php
// Ajoutez cette partie au début pour récupérer les données utilisateur
session_start();
$user_id = $_SESSION['user_id'];
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
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
                        <a class="dropdown-item" href="ajouter_medicament.php"><i class="bi bi-plus-square"></i> Ajouter un Médicaments</a>
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
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo $user['profile_picture']; ?>" alt="Photo de profil" class="rounded-circle" style="width: 40px; height: 40px;">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Mon profil</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                    </div>
                    <li class="nav-item dropdown">
    <a class="nav-link" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <?php if ($unreadNotifications > 0): ?>
            <span class="badge badge-danger"><?php echo $unreadNotifications; ?></span>
        <?php endif; ?>
    </a>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationDropdown">
        <?php if (empty($notifications)): ?>
            <p class="dropdown-item">Aucune notification</p>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="dropdown-item">
                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                    <a href="mark_as_read.php?id=<?php echo $notification['id']; ?>" class="btn btn-sm btn-secondary">Marquer comme lu</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</li>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm ml-2" style="border-radius: 50px;" href="login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- CSS Directement dans le menu.php -->
<style>
    .navbar {
        background-color: #f8f9fa;
    }
    .nav-link {
        color: #007bff;
    }
    .nav-link:hover {
        color: #0056b3;
    }
    .btn-info {
        background-color: #17a2b8;
        border-color: #17a2b8;
    }
    .btn-danger {
        background-color: #dc3545;
        border-color: #dc3545;
    }
    .rounded-circle {
        border: 2px solid #007bff;
    }
    .dropdown-menu {
        background-color: #f8f9fa;
    }
    .dropdown-item:hover {
        background-color: #e9ecef;
    }
</style>
