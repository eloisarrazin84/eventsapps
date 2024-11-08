<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . '/../vendor/phpqrcode/qrlib.php';

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function generateQRCode($bagId) {
    $url = "https://event.outdoorsecours.fr/sacs/bag_tracking.php?bag_id=" . $bagId;
    $qrCodePath = __DIR__ . '/../uploads/qrcodes/bag_' . $bagId . '.png';

    if (!is_dir(__DIR__ . '/../uploads/qrcodes')) {
        mkdir(__DIR__ . '/../uploads/qrcodes', 0777, true);
    }
    
    try {
        QRcode::png($url, $qrCodePath, QR_ECLEVEL_L, 10);
    } catch (Exception $e) {
        error_log("Erreur de génération de QR Code : " . $e->getMessage());
        return null;
    }
    
    return $qrCodePath;
}

$stmt = $conn->prepare("SELECT * FROM stock_locations");
$stmt->execute();
$stockLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_bag'])) {
    $locationId = $_POST['location_id'];
    $bagName = $_POST['name'];
    $contents = $_POST['contents'];

    $stmt = $conn->prepare("INSERT INTO bags (location_id, name, contents) VALUES (:location_id, :name, :contents)");
    $stmt->bindParam(':location_id', $locationId);
    $stmt->bindParam(':name', $bagName);
    $stmt->bindParam(':contents', $contents);
    $stmt->execute();

    $bagId = $conn->lastInsertId();
    $qrCodePath = generateQRCode($bagId);

    $stmt = $conn->prepare("UPDATE bags SET qr_code_path = :qr_code_path WHERE id = :id");
    $stmt->bindParam(':qr_code_path', $qrCodePath);
    $stmt->bindParam(':id', $bagId);
    $stmt->execute();

    header("Location: manage_bags.php");
    exit();
}

$stmt = $conn->prepare("SELECT bags.*, stock_locations.location_name, stock_locations.bag_name FROM bags JOIN stock_locations ON bags.location_id = stock_locations.id");
$stmt->execute();
$bags = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Sacs</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Gestion des Sacs</h2>

    <form method="POST" class="form-inline mb-4">
        <select name="location_id" class="form-control mr-2" required>
            <option value="">Sélectionner un lieu de stockage</option>
            <?php foreach ($stockLocations as $location): ?>
                <option value="<?php echo $location['id']; ?>">
                    <?php echo htmlspecialchars($location['location_name'] . " - " . $location['bag_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="name" class="form-control mr-2" placeholder="Nom du sac" required>
        <input type="text" name="contents" class="form-control mr-2" placeholder="Contenu du sac">
        <button type="submit" name="add_bag" class="btn btn-primary">Ajouter</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom du sac</th>
                <th>Lieu de stockage</th>
                <th>QR Code</th>
                <th>Date dernier inventaire</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bags as $bag): ?>
                <tr>
                    <td><?php echo htmlspecialchars($bag['name']); ?></td>
                    <td><?php echo htmlspecialchars($bag['location_name'] . " - " . $bag['bag_name']); ?></td>
                    <td>
                        <?php if ($bag['qr_code_path']): ?>
                            <img src="<?php echo htmlspecialchars('/uploads/qrcodes/' . basename($bag['qr_code_path'])); ?>" width="100">
                        <?php else: ?>
                            Pas de QR Code
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($bag['last_inventory_date'] ?? 'Non défini'); ?></td>
                    <td>
                        <a href="sacs/bag_tracking.php?bag_id=<?php echo $bag['id']; ?>" class="btn btn-info">Suivre</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
