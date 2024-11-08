<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les informations du sac et les lots disponibles
if (!isset($_GET['bag_id'])) {
    echo "ID de sac non spécifié.";
    exit();
}

$bagId = $_GET['bag_id'];

// Récupérer les informations actuelles du sac
$stmt = $conn->prepare("SELECT * FROM bags WHERE id = :bag_id");
$stmt->bindParam(':bag_id', $bagId);
$stmt->execute();
$bag = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$bag) {
    echo "Sac non trouvé.";
    exit();
}

// Récupérer les lots actuellement associés au sac
$stmt = $conn->prepare("SELECT lot_id FROM bag_lots WHERE bag_id = :bag_id");
$stmt->bindParam(':bag_id', $bagId);
$stmt->execute();
$assignedLotIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Récupérer la liste complète des lots
$stmt = $conn->prepare("SELECT * FROM lots");
$stmt->execute();
$lots = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mise à jour des informations du sac
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bagName = $_POST['name'];
    $selectedLots = isset($_POST['lots']) ? $_POST['lots'] : [];

    // Mettre à jour le sac
    $stmt = $conn->prepare("UPDATE bags SET name = :name WHERE id = :id");
    $stmt->bindParam(':name', $bagName);
    $stmt->bindParam(':id', $bagId);
    $stmt->execute();

    // Mettre à jour les lots du sac
    $stmt = $conn->prepare("DELETE FROM bag_lots WHERE bag_id = :bag_id");
    $stmt->bindParam(':bag_id', $bagId);
    $stmt->execute();

    foreach ($selectedLots as $lotId) {
        $stmt = $conn->prepare("INSERT INTO bag_lots (bag_id, lot_id) VALUES (:bag_id, :lot_id)");
        $stmt->bindParam(':bag_id', $bagId);
        $stmt->bindParam(':lot_id', $lotId);
        $stmt->execute();
    }

    header("Location: manage_bags.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le Sac</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Modifier le Sac : <?php echo htmlspecialchars($bag['name']); ?></h2>
    <form method="POST">
        <div class="form-group">
            <label for="name">Nom du Sac</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($bag['name']); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="lots">Lots dans le Sac</label>
            <select name="lots[]" id="lots" class="form-control" multiple>
                <?php
                foreach ($lots as $lot) {
                    $selected = in_array($lot['id'], $assignedLotIds) ? 'selected' : '';
                    echo "<option value='{$lot['id']}' $selected>{$lot['name']}</option>";
                }
                ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="manage_bags.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
</body>
</html>
