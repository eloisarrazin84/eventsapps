<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier que le sac existe et récupérer ses informations
if (!isset($_GET['bag_id'])) {
    echo "ID de sac non spécifié.";
    exit();
}

$bagId = $_GET['bag_id'];

// Récupérer les informations du sac
$stmt = $conn->prepare("SELECT * FROM bags WHERE id = :bag_id");
$stmt->bindParam(':bag_id', $bagId);
$stmt->execute();
$bag = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bag) {
    echo "Sac non trouvé.";
    exit();
}

// Gérer les actions d'inventaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['take_action'])) {
        // Prendre le sac pour un événement
        $stmt = $conn->prepare("UPDATE bags SET last_inventory_date = NOW(), status = 'taken' WHERE id = :id");
        $stmt->bindParam(':id', $bagId);
        $stmt->execute();
        echo "<script>alert('Sac pris pour l\'événement.'); window.location.href='manage_bags.php';</script>";
        exit();
    } elseif (isset($_POST['return_action'])) {
        // Remettre le sac en service après l'événement
        $stmt = $conn->prepare("UPDATE bags SET last_inventory_date = NOW(), status = 'in_service' WHERE id = :id");
        $stmt->bindParam(':id', $bagId);
        $stmt->execute();
        echo "<script>alert('Sac remis en service.'); window.location.href='manage_bags.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inventaire du Sac</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Inventaire du Sac : <?php echo htmlspecialchars($bag['name']); ?></h2>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Détails du Sac</h5>
            <p><strong>Lieu de Stockage :</strong> <?php echo htmlspecialchars($bag['location_id']); ?></p>
            <p><strong>Contenu :</strong> <?php echo htmlspecialchars($bag['contents']); ?></p>
            <p><strong>Date du Dernier Inventaire :</strong> <?php echo htmlspecialchars($bag['last_inventory_date'] ?? 'Non défini'); ?></p>
            <p><strong>Statut :</strong> <?php echo htmlspecialchars($bag['status'] ?? 'inconnu'); ?></p>
        </div>
    </div>

    <h4>Actions d'Inventaire</h4>
    <form method="POST">
        <button type="submit" name="take_action" class="btn btn-primary">Prendre pour Événement</button>
        <button type="submit" name="return_action" class="btn btn-secondary">Remettre en Service</button>
    </form>
    
    <a href="manage_bags.php" class="btn btn-light mt-3">Retour à la Gestion des Sacs</a>
</div>
</body>
</html>
