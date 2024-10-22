<?php
session_start();

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur via l'URL
if (!isset($_GET['user_id'])) {
    echo "Aucun utilisateur sélectionné.";
    exit();
}

$user_id = $_GET['user_id'];

// Connexion à la base de données
$servername = "localhost";
$username_db = "root";
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations de l'utilisateur
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les documents de l'utilisateur
    $stmt = $conn->prepare("SELECT document_name, file_path FROM documents WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $userDocuments = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Documents de l'utilisateur</title>
</head>
<body>
<div class="container mt-5">
    <h1>Documents pour l'utilisateur : <?php echo htmlspecialchars($user['username']); ?></h1>

    <!-- Table des documents -->
    <?php if (!empty($userDocuments)): ?>
        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Nom du Document</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userDocuments as $doc): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($doc['document_name']); ?></td>
                        <td><a href="<?php echo htmlspecialchars($doc['file_path']); ?>" download>Télécharger</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucun document disponible pour cet utilisateur.</p>
    <?php endif; ?>

    <!-- Bouton pour retourner à la gestion des utilisateurs -->
    <a href="manage_users.php" class="btn btn-secondary mt-4">Retour à la gestion des utilisateurs</a>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
