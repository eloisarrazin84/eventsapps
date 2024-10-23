<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm p-3">
    <a class="navbar-brand" href="home.php">
        <img src="https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1.png" alt="Logo" style="width: 50px;">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-gear"></i> Administration
                    </a>
                    <div class="dropdown-menu" aria-labelledby="adminDropdown">
                        <a class="dropdown-item" href="manage_events.php">Gestion des événements</a>
                        <a class="dropdown-item" href="manage_users.php">Gestion des utilisateurs</a>
                    </div>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="home.php"><i class="bi bi-grid"></i> Mes applications</a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-primary text-white" href="profile.php"><i class="bi bi-person"></i> Mon profil</a>
                </li>
            <?php endif; ?>
        </ul>
        <ul class="navbar-nav ml-auto">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a class="btn btn-danger btn-sm ml-2" href="logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm ml-2" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Connexion</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
