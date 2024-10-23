<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm p-3">
    <a class="navbar-brand" href="#">
        <img src="https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png" alt="Logo" style="width: 50px;">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <!-- Menu Administration si l'utilisateur est admin -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-tools"></i> Administration
                    </a>
                    <div class="dropdown-menu" aria-labelledby="adminDropdown">
                        <a class="dropdown-item" href="manage_events.php"><i class="fas fa-calendar-alt"></i> Gestion des événements</a>
                        <a class="dropdown-item" href="manage_users.php"><i class="fas fa-users"></i> Gestion des utilisateurs</a>
                    </div>
                </li>
            <?php endif; ?>
            <!-- Tableau de bord et Mes applications -->
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
            </li>
            <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : ''; ?>">
                <a class="nav-link" href="home.php"><i class="fas fa-th-large"></i> Mes applications</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <!-- Si l'utilisateur est connecté -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo $user['profile_picture']; ?>" alt="Photo de profil" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> Mon profil</a>
                        <a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Paramètres</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
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
