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
$stmt = $conn->prepare("SELECT bags.*, stock_locations.location_name, stock_locations.bag_name 
                        FROM bags 
                        JOIN stock_locations ON bags.location_id = stock_locations.id 
                        WHERE bags.id = :bag_id");
$stmt->bindParam(':bag_id', $bagId);
$stmt->execute();
$bag = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bag) {
    echo "Sac non trouvé.";
    exit();
}

// Récupérer les lots associés au sac
$stmt = $conn->prepare("SELECT lots.name 
                        FROM lots 
                        JOIN bag_lots ON lots.id = bag_lots.lot_id 
                        WHERE bag_lots.bag_id = :bag_id");
$stmt->bindParam(':bag_id', $bagId);
$stmt->execute();
$lots = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Gérer les actions pour l'inventaire ou la remise en service
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['inventory_action'])) {
        // Mettre à jour la date du dernier inventaire
        $stmt = $conn->prepare("UPDATE bags SET last_inventory_date = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $bagId);
        $stmt->execute();
        header("Location: /sacs/bag_tracking.php?bag_id=" . $bagId);
        exit();
    } elseif (isset($_POST['reset_action'])) {
        // Effectuer une remise en service
        // (vous pouvez ajouter des opérations spécifiques ici si nécessaire)
        echo "<script>alert('Le sac a été remis en service.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi du Sac</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Suivi du Sac : <?php echo htmlspecialchars($bag['name']); ?></h2>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Détails du Sac</h5>
            <p><strong>Lieu de Stockage :</strong> <?php echo htmlspecialchars($bag['location_name'] . " - " . $bag['bag_name']); ?></p>
            <p><strong>Contenu :</strong> <?php echo !empty($lots) ? implode(", ", array_map('htmlspecialchars', $lots)) : 'Aucun lot associé'; ?></p>
            <p><strong>Date du Dernier Inventaire :</strong> <?php echo htmlspecialchars($bag['last_inventory_date'] ?? 'Non défini'); ?></p>
        </div>
    </div>

    <h4>Actions</h4>
    <form method="POST" class="mb-3">
        <button type="submit" name="inventory_action" class="btn btn-primary">Faire un Inventaire</button>
        <button type="submit" name="reset_action" class="btn btn-secondary">Remettre en Service</button>
    </form>
    
    <a href="/sacs/manage_bags.php" class="btn btn-light">Retour à la Gestion des Sacs</a>
</div>
</body>
</html>
