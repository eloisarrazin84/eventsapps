<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (!isset($_GET['id'])) {
    echo "ID de consommable non spécifié.";
    exit();
}

$consommableId = $_GET['id'];

// Récupérer les informations actuelles du consommable
$stmt = $conn->prepare("SELECT * FROM consommables WHERE id = :id");
$stmt->bindParam(':id', $consommableId);
$stmt->execute();
$consommable = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$consommable) {
    echo "Consommable non trouvé.";
    exit();
}

// Mettre à jour les informations du consommable
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE consommables SET name = :name, description = :description WHERE id = :id");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':id', $consommableId);
    $stmt->execute();

    header("Location: manage_consommables.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le Consommable</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Modifier le Consommable : <?php echo htmlspecialchars($consommable['name']); ?></h2>
    <form method="POST">
        <div class="form-group">
            <label for="name">Nom du consommable</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($consommable['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($consommable['description']); ?>">
        </div>
        
        <button type="submit" class="btn btn-primary">Enregistrer</button>
        <a href="/consommables/manage_consommables.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>
</body>
</html>
