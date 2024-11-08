<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
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
$stmt = $conn->prepare("SELECT lots.id, lots.name 
                        FROM lots 
                        JOIN bag_lots ON lots.id = bag_lots.lot_id 
                        WHERE bag_lots.bag_id = :bag_id");
$stmt->bindParam(':bag_id', $bagId);
$stmt->execute();
$lots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gérer la soumission de l'inventaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inventory'])) {
    $inspectorName = $_POST['inspector_name'];
    $inventoryResults = [];

    foreach ($lots as $lot) {
        $lotId = $lot['id'];
        $present = isset($_POST["present_$lotId"]) ? 1 : 0;
        $operational = $_POST["operational_$lotId"] ?? '';
        $comment = $_POST["comment_$lotId"] ?? '';

        $inventoryResults[] = [
            'lot_id' => $lotId,
            'present' => $present,
            'operational' => $operational,
            'comment' => $comment,
        ];
    }

    // Mettre à jour la date du dernier inventaire
    $stmt = $conn->prepare("UPDATE bags SET last_inventory_date = NOW() WHERE id = :id");
    $stmt->bindParam(':id', $bagId);
    $stmt->execute();

    // Sauvegarder les résultats de l'inventaire
    foreach ($inventoryResults as $result) {
        $stmt = $conn->prepare("INSERT INTO inventory_logs (bag_id, lot_id, present, operational, comment, inspector_name, inventory_date) 
                                VALUES (:bag_id, :lot_id, :present, :operational, :comment, :inspector_name, NOW())");
        $stmt->execute([
            ':bag_id' => $bagId,
            ':lot_id' => $result['lot_id'],
            ':present' => $result['present'],
            ':operational' => $result['operational'],
            ':comment' => $result['comment'],
            ':inspector_name' => $inspectorName
        ]);
    }

    header("Location: /sacs/bag_tracking.php?bag_id=" . $bagId);
    exit();
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
            <p><strong>Lots Présents :</strong> <?php echo !empty($lots) ? implode(", ", array_map(fn($lot) => htmlspecialchars($lot['name']), $lots)) : 'Aucun lot associé'; ?></p>
            <p><strong>Date du Dernier Inventaire :</strong> <?php echo htmlspecialchars($bag['last_inventory_date'] ?? 'Non défini'); ?></p>
        </div>
    </div>

    <h4>Actions</h4>
    <button class="btn btn-primary" data-toggle="modal" data-target="#inventoryModal">Faire un Inventaire</button>
</div>

<!-- Modal d'Inventaire -->
<div class="modal fade" id="inventoryModal" tabindex="-1" role="dialog" aria-labelledby="inventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="inventoryModalLabel">Inventaire du Sac</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="inspector_name">Nom et Prénom de l'inspecteur :</label>
                        <input type="text" name="inspector_name" id="inspector_name" class="form-control" required>
                    </div>
                    <?php foreach ($lots as $lot): ?>
                        <div class="form-group">
                            <label><?php echo htmlspecialchars($lot['name']); ?></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="present_<?php echo $lot['id']; ?>" id="present_<?php echo $lot['id']; ?>">
                                <label class="form-check-label" for="present_<?php echo $lot['id']; ?>">Présent</label>
                            </div>
                            <select name="operational_<?php echo $lot['id']; ?>" class="form-control mt-2">
                                <option value="">Statut</option>
                                <option value="Opérationnel">Opérationnel</option>
                                <option value="Non opérationnel">Non opérationnel</option>
                            </select>
                            <input type="text" name="comment_<?php echo $lot['id']; ?>" class="form-control mt-2" placeholder="Commentaire">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="submit_inventory" class="btn btn-primary">Valider l'Inventaire</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
