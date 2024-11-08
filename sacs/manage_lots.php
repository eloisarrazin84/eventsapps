<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer la liste des consommables
$stmt = $conn->prepare("SELECT * FROM consommables");
$stmt->execute();
$allConsommables = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajouter un lot
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_lot'])) {
    $lotName = $_POST['name'];
    $description = $_POST['description'];

    try {
        $stmt = $conn->prepare("INSERT INTO lots (name, description) VALUES (:name, :description)");
        $stmt->bindParam(':name', $lotName);
        $stmt->bindParam(':description', $description);
        $stmt->execute();
        
        $lotId = $conn->lastInsertId();
        
        // Associer des consommables avec quantité
        if (isset($_POST['consommables']) && is_array($_POST['consommables'])) {
            foreach ($_POST['consommables'] as $consommableId => $quantity) {
                $stmt = $conn->prepare("INSERT INTO lot_consommables (lot_id, consommable_id, quantity) VALUES (:lot_id, :consommable_id, :quantity)");
                $stmt->bindParam(':lot_id', $lotId);
                $stmt->bindParam(':consommable_id', $consommableId);
                $stmt->bindParam(':quantity', $quantity);
                $stmt->execute();
            }
        }

        header("Location: /sacs/manage_lots.php");
        exit();
    } catch (PDOException $e) {
        echo "Erreur lors de l'ajout du lot : " . $e->getMessage();
    }
}
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

    <!-- Formulaire pour ajouter un lot avec des consommables -->
    <form method="POST" class="mb-4">
        <input type="text" name="name" class="form-control mb-2" placeholder="Nom du lot" required>
        <input type="text" name="description" class="form-control mb-2" placeholder="Description">
        
        <h5>Consommables</h5>
        <?php foreach ($allConsommables as $consommable): ?>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label"><?php echo htmlspecialchars($consommable['name']); ?></label>
                <div class="col-sm-8">
                    <input type="number" class="form-control" name="consommables[<?php echo $consommable['id']; ?>]" placeholder="Quantité" min="0">
                </div>
            </div>
        <?php endforeach; ?>
        
        <button type="submit" name="add_lot" class="btn btn-primary">Ajouter</button>
    </form>

    <!-- Table pour afficher les lots existants avec l'option de modification -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom du lot</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->prepare("SELECT * FROM lots");
            $stmt->execute();
            $lots = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($lots as $lot): ?>
                <tr>
                    <td><?php echo htmlspecialchars($lot['name']); ?></td>
                    <td><?php echo htmlspecialchars($lot['description']); ?></td>
                    <td>
                        <a href="edit_lot.php?lot_id=<?php echo $lot['id']; ?>" class="btn btn-warning">Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
