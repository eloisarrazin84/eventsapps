<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Vérifier si l'ID du document est passé en paramètre
if (!isset($_GET['id'])) {
    echo "Aucun document sélectionné.";
    exit();
}

$document_id = $_GET['id'];

// Connexion à la base de données
$servername = "localhost";
$username_db = "root";  
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les informations du document
    $stmt = $conn->prepare("SELECT * FROM documents WHERE id = :document_id");
    $stmt->bindParam(':document_id', $document_id);
    $stmt->execute();
    $document = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si le document existe
    if (!$document) {
        echo "Document non trouvé.";
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
    <title>Voir le Document</title>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center">Document: <?php echo htmlspecialchars($document['document_name']); ?></h1>

    <div class="text-center mt-4">
        <embed src="<?php echo htmlspecialchars($document['file_path']); ?>" width="100%" height="600px" alt="Document PDF" />
    </div>

    <div class="text-center mt-4">
        <a href="profile.php" class="btn btn-secondary">Retour à mon profil</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
