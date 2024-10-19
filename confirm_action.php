<?php
session_start();

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Vérifier que l'ID de l'utilisateur à valider ou rejeter a été passé
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    header("Location: manage_user.php");
    exit();
}

$userId = $_GET['id'];
$action = $_GET['action'];  // "approve" ou "reject"

// Connexion à la base de données
$servername = "localhost";
$username = "root";
$password = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations de l'utilisateur
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Si l'utilisateur n'existe pas
        header("Location: manage_user.php");
        exit();
    }

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
    <title>Confirmation de l'action</title>
</head>
<body>
<div class="container">
    <h2 class="mt-5">Confirmation de l'action</h2>

    <p>Vous êtes sur le point de <strong><?php echo $action === 'approve' ? 'valider' : 'rejeter'; ?></strong> l'utilisateur suivant :</p>

    <ul>
        <li><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></li>
        <li><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></li>
        <li><strong>Prénom :</strong> <?php echo htmlspecialchars($user['first_name']); ?></li>
        <li><strong>Nom :</strong> <?php echo htmlspecialchars($user['last_name']); ?></li>
    </ul>

    <p>Êtes-vous sûr de vouloir <strong><?php echo $action === 'approve' ? 'valider' : 'rejeter'; ?></strong> cet utilisateur ?</p>

    <!-- Formulaire de confirmation -->
    <form method="POST" action="process_confirmation.php">
        <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
        <input type="hidden" name="action" value="<?php echo $action; ?>">
        <button type="submit" class="btn btn-success">Confirmer</button>
        <a href="manage_user.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
