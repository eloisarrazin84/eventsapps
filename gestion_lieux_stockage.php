<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $locationName = htmlspecialchars($_POST['location_name']);
    $bagName = isset($_POST['bag_name']) ? htmlspecialchars($_POST['bag_name']) : null;

    if (isset($_POST['add_location'])) {
        $stmt = $conn->prepare("INSERT INTO stock_locations (location_name, bag_name) VALUES (:location_name, :bag_name)");
        $stmt->bindParam(':location_name', $locationName);
        $stmt->bindParam(':bag_name', $bagName);
        $stmt->execute();
    } elseif (isset($_POST['delete_location'])) {
        $locationId = $_POST['location_id'];
        $stmt = $conn->prepare("DELETE FROM stock_locations WHERE id = :location_id");
        $stmt->bindParam(':location_id', $locationId);
        $stmt->execute();
    }
}

$stmt = $conn->prepare("SELECT * FROM stock_locations");
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des lieux de stockage</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Gestion des lieux de stockage</h2>
    <form method="POST" class="form-inline mb-3">
        <input type="text" name="location_name" class="form-control mr-2" placeholder="Nom du lieu de stockage" required>
        <input type="text" name="bag_name" class="form-control mr-2" placeholder="Nom du sac (facultatif)">
        <button type="submit" name="add_location" class="btn btn-primary">Ajouter</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Lieu de stockage</th>
                <th>Sac</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($locations as $location): ?>
                <tr>
                    <td><?php echo htmlspecialchars($location['location_name']); ?></td>
                    <td><?php echo htmlspecialchars($location['bag_name']); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="location_id" value="<?php echo $location['id']; ?>">
                            <button type="submit" name="delete_location" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce lieu de stockage ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
