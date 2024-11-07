<?php
// Récupérer les informations du sac
$bagId = $_GET['bag_id'];
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$stmt = $conn->prepare("SELECT bags.*, stock_locations.location_name, stock_locations.bag_name
                        FROM bags
                        JOIN stock_locations ON bags.location_id = stock_locations.id
                        WHERE bags.id = :bag_id");
$stmt->bindParam(':bag_id', $bagId);
$stmt->execute();
$bag = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi du Sac</title>
</head>
<body>
<div class="container mt-5">
    <h2>Suivi du Sac : <?php echo htmlspecialchars($bag['name']); ?></h2>
    <p><strong>Lieu de stockage :</strong> <?php echo htmlspecialchars($bag['location_name'] . " - " . $bag['bag_name']); ?></p>
    <p><strong>Dernier inventaire :</strong> <?php echo htmlspecialchars($bag['last_inventory_date']); ?></p>
    <p><strong>Contenu :</strong> <?php echo htmlspecialchars($bag['contents']); ?></p>

    <div class="mt-4">
        <a href="inventory_action.php?bag_id=<?php echo $bagId; ?>&action=prise" class="btn btn-primary">Inventaire de Prise</a>
        <a href="inventory_action.php?bag_id=<?php echo $bagId; ?>&action=remise" class="btn btn-secondary">Inventaire de Remise</a>
    </div>
</div>
</body>
</html>
