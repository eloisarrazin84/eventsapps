<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm p-3">
    <a class="navbar-brand" href="#">
        <img src="https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1-100x100.png" alt="Logo" style="width: 50px;">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <!-- Autres liens du menu -->
        </ul>
        <ul class="navbar-nav ml-auto">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php if (isset($user['profile_picture'])): ?>
                            <img src="<?php echo $user['profile_picture']; ?>" alt="Photo de profil" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                        <?php else: ?>
                            <span>Photo de profil</span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="profile.php">Voir mon profil</a>
                        <a class="dropdown-item" href="logout.php">Déconnexion</a>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>

<!-- CSS direct dans le fichier menu.php -->
<style>
    .rounded-circle {
        border-radius: 50%;
    }
</style>
