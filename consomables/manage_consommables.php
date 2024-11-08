<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Ajouter un consommable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_consommable'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    try {
        $stmt = $conn->prepare("INSERT INTO consommables (name, description) VALUES (:name, :description)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->execute();

        header("Location: manage_consommables.php");
        exit();
    } catch (PDOException $e) {
        echo "Erreur lors de l'ajout du consommable : " . $e->getMessage();
    }
}

// Récupérer les consommables existants
$stmt = $conn->prepare("SELECT * FROM consommables");
$stmt->execute();
$consommables = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Consommables</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Gestion des Consommables</h2>

    <!-- Formulaire pour ajouter un consommable -->
    <form method="POST" class="form-inline mb-4">
        <input type="text" name="name" class="form-control mr-2" placeholder="Nom du consommable" required>
        <input type="text" name="description" class="form-control mr-2" placeholder="Description">
        <button type="submit" name="add_consommable" class="btn btn-primary">Ajouter</button>
    </form>

    <!-- Table pour afficher les consommables existants -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom du consommable</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($consommables as $consommable): ?>
                <tr>
                    <td><?php echo htmlspecialchars($consommable['name']); ?></td>
                    <td><?php echo htmlspecialchars($consommable['description']); ?></td>
                    <td>
                        <a href="edit_consommable.php?id=<?php echo $consommable['id']; ?>" class="btn btn-warning">Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
