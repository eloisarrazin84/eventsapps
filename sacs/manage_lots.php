<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ajouter un lot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lot'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO lots (name, description) VALUES (:name, :description)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->execute();

    header("Location: manage_lots.php");
    exit();
}

// Récupérer tous les lots
$stmt = $conn->prepare("SELECT * FROM lots");
$stmt->execute();
$lots = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Lots</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Gestion des Lots</h2>

    <!-- Formulaire pour ajouter un lot -->
    <form method="POST" class="form-inline mb-4">
        <input type="text" name="name" class="form-control mr-2" placeholder="Nom du lot" required>
        <input type="text" name="description" class="form-control mr-2" placeholder="Description du lot">
        <button type="submit" name="add_lot" class="btn btn-primary">Ajouter</button>
    </form>

    <!-- Table pour afficher les lots existants -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom du lot</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($lots as $lot): ?>
                <tr>
                    <td><?php echo htmlspecialchars($lot['name']); ?></td>
                    <td><?php echo htmlspecialchars($lot['description']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
