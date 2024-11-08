<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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

// Récupérer les lots associés au sac
$stmt = $conn->prepare("SELECT lots.id, lots.name 
                        FROM lots 
                        JOIN bag_lots ON lots.id = bag_lots.lot_id 
                        WHERE bag_lots.bag_id = :bag_id");
$stmt->bindParam(':bag_id', $bagId);
$stmt->execute();
$lots = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event = $_POST['event'];
    $medicName = $_POST['medic_name'];
    $returnDate = $_POST['return_date'];
    $inspectorName = $_POST['inspector_name'];
    $inventoryResults = [];

    foreach ($lots as $lot) {
        $lotId = $lot['id'];
        $rehassortDone = isset($_POST["rehassort_$lotId"]) ? 1 : 0;
        $comment = $_POST["comment_$lotId"] ?? '';

        $inventoryResults[] = [
            'lot_id' => $lotId,
            'rehassort' => $rehassortDone,
            'comment' => $comment,
        ];
    }

    $stmt = $conn->prepare("UPDATE bags SET last_inventory_date = NOW() WHERE id = :id");
    $stmt->bindParam(':id', $bagId);
    $stmt->execute();

    foreach ($inventoryResults as $result) {
        $stmt = $conn->prepare("INSERT INTO return_logs (bag_id, lot_id, rehassort, comment, event, medic_name, inspector_name, return_date) 
                                VALUES (:bag_id, :lot_id, :rehassort, :comment, :event, :medic_name, :inspector_name, NOW())");
        $stmt->execute([
            ':bag_id' => $bagId,
            ':lot_id' => $result['lot_id'],
            ':rehassort' => $result['rehassort'],
            ':comment' => $result['comment'],
            ':event' => $event,
            ':medic_name' => $medicName,
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
    <title>Procédure de Retour du Sac</title>
</head>
<body>
<div class="container mt-5">
    <h2>Procédure de Retour du Sac : <?php echo htmlspecialchars($bag['name']); ?></h2>
    <form method="POST">
        <div class="form-group">
            <label for="event">Événement où était le sac :</label>
            <input type="text" name="event" id="event" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="medic_name">Nom du médecin ou infirmier sur place :</label>
            <input type="text" name="medic_name" id="medic_name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="return_date">Date de retour :</label>
            <input type="date" name="return_date" id="return_date" class="form-control" required>
        </div>
        <hr>
        <h4>Lots présents</h4>
        <?php foreach ($lots as $lot): ?>
            <div class="form-group">
                <label><?php echo htmlspecialchars($lot['name']); ?></label>
                <input type="checkbox" name="rehassort_<?php echo $lot['id']; ?>"> Réassort fait
                <input type="text" name="comment_<?php echo $lot['id']; ?>" class="form-control" placeholder="Commentaire">
            </div>
        <?php endforeach; ?>
        <div class="form-group">
            <label for="inspector_name">Nom et Prénom de la personne qui fait l'inventaire :</label>
            <input type="text" name="inspector_name" id="inspector_name" class="form-control" required>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" class="form-check-input" id="seal_check" required>
            <label class="form-check-label" for="seal_check">Avez-vous mis les scellés sur le sac ?</label>
        </div>
        <button type="submit" class="btn btn-primary" name="submit_return">Valider la Procédure de Retour</button>
    </form>
</div>
</body>
</html>
