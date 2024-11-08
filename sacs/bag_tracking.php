<?php
// Désactiver la vérification de session pour rendre la page accessible à tous
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2>Suivi du Sac : <?php echo htmlspecialchars($bag['name']); ?></h2>
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Détails du Sac</h5>
            <p><strong>Lieu de Stockage :</strong> <?php echo htmlspecialchars($bag['location_name'] . " - " . $bag['bag_name']); ?></p>
            <p><strong>Lots Présents :</strong> <?php echo !empty($lots) ? implode(", ", array_map('htmlspecialchars', $lots)) : 'Aucun lot associé'; ?></p>
            <p><strong>Date du Dernier Inventaire :</strong> <?php echo htmlspecialchars($bag['last_inventory_date'] ?? 'Non défini'); ?></p>
        </div>
    </div>

    <h4>Actions</h4>
    <form method="POST" class="mb-3">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#inventoryModal">Faire un Inventaire</button>
        <button type="submit" name="reset_action" class="btn btn-secondary">Remettre en Service</button>
    </form>
</div>

<!-- Modal pour l'inventaire -->
<div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="inventoryModalLabel">Faire l'Inventaire du Sac</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php foreach ($lots as $lot): ?>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="lot_status[<?php echo htmlspecialchars($lot); ?>]" value="1">
                                <?php echo htmlspecialchars($lot); ?> - Opérationnel
                            </label>
                            <textarea class="form-control mt-2" name="comments[<?php echo htmlspecialchars($lot); ?>]" placeholder="Commentaire"></textarea>
                        </div>
                    <?php endforeach; ?>
                    <div class="form-group">
                        <label>Nom de l'Inspecteur</label>
                        <input type="text" name="inspector_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="inventory_action" class="btn btn-primary">Valider l'Inventaire</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
