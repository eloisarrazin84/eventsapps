<?php
session_start();

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Appliquer les filtres si une recherche est soumise
    $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';

    // Récupérer les utilisateurs
    $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE :search OR email LIKE :search");
    $stmt->bindParam(':search', $search);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des utilisateurs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .action-buttons a {
            margin-right: 5px;
        }
        .table {
            margin-top: 20px;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        .btn {
            border-radius: 25px;
        }
        .container h1 {
            margin-bottom: 30px;
        }
        .table th {
            background-color: #f8f9fa;
        }
        /* Styles pour la responsivité */
        @media (max-width: 768px) {
            .table td, .table th {
                font-size: 0.8rem;
            }
            h1 {
                font-size: 1.5rem;
            }
            .btn {
                font-size: 0.8rem;
            }
        }
    .action-buttons .btn {
    margin: 0 2px; /* Ajustez l'espacement entre les boutons */
}

.table td, .table th {
    vertical-align: middle;
    text-align: center;
}

.table th {
    background-color: #f8f9fa; /* Couleur de fond pour les en-têtes de tableau */
}

    </style>
</head>
<body>

<!-- Inclusion du menu -->
<?php include 'menu_admin.php'; ?>

<div class="container">
    <h1 class="mt-5 text-center">Gestion des utilisateurs</h1>
    <form method="GET" class="form-inline justify-content-center mb-3">
        <input type="text" class="form-control mr-2" name="search" placeholder="Rechercher un utilisateur..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>
    <a href="create_user.php" class="btn btn-success mb-3">Créer un nouvel utilisateur</a>
    <table class="table table-bordered table-hover">
    <thead class="thead-light">
        <tr>
            <th>Nom d'utilisateur</th>
            <th>Email</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Rôle</th>
            <th>Approuvé</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                <td><?php echo htmlspecialchars($user['last_name']); ?></td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td>
                    <?php if ($user['is_approved']): ?>
                        <span class="badge badge-success">Oui</span>
                    <?php else: ?>
                        <a href="approve_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success">Approuver</a>
                    <?php endif; ?>
                </td>
                <td class="action-buttons">
                    <div class="btn-group" role="group">
                        <a href="view_user_documents.php?user_id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm">Voir Documents</a>
                        <a href="view_user_profile.php?user_id=<?php echo $user['id']; ?>" class="btn btn-success btn-sm">Voir Profil</a>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                        <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">Aucun utilisateur trouvé.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
