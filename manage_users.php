<<?php
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

    // Récupérer les utilisateurs
    $stmt = $conn->prepare("SELECT * FROM users");
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
</head>
<body>

<!-- Inclusion du menu -->
<?php include 'menu.php'; ?>

<div class="container mt-5">
    <h2 class="mb-4">Gestion des utilisateurs</h2>

    <table class="table table-bordered">
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
                        <span class="text-success">Oui</span>
                    <?php else: ?>
                        <a href="approve_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success">Approuver</a>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
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
