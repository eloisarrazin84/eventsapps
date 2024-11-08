<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Vérifier si l'ID du lot est défini dans l'URL
if (!isset($_GET['lot_id'])) {
    echo "ID du lot non spécifié.";
    exit();
}

$lotId = $_GET['lot_id'];

// Récupérer les informations du lot
$stmt = $conn->prepare("SELECT * FROM lots WHERE id = :lot_id");
$stmt->bindParam(':lot_id', $lotId);
$stmt->execute();
$lot = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lot) {
    echo "Lot non trouvé.";
    exit();
}

// Mettre à jour les informations du lot
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lotName = $_POST['name'];
    $description = $_POST['description'];

    try {
        $stmt = $conn->prepare("UPDATE lots SET name = :name, description = :description WHERE id = :id");
        $stmt->bindParam(':name', $lotName);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':id', $lotId);
        $stmt->execute();
        
        header("Location: manage_lots.php");
        exit();
    } catch (PDOException $e) {
        echo "Erreur lors de la mise à jour du lot : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le Lot</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Modifier le Lot : <?php echo htmlspecialchars($lot['name']); ?></h2>
    <form method="POST">
        <div class="form-group">
            <label for="name">Nom du Lot</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($lot['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($lot['description']); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="manage_lots.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
</body>
</html>
