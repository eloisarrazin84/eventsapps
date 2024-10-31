<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $medicament_id = $_GET['id'];
    try {
        $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les informations actuelles du médicament
        $stmt = $conn->prepare("SELECT * FROM medicaments WHERE id = :id");
        $stmt->bindParam(':id', $medicament_id);
        $stmt->execute();
        $medicament = $stmt->fetch(PDO::FETCH_ASSOC);

        // Récupérer les lieux de stockage disponibles
        $stmt = $conn->prepare("SELECT * FROM stock_locations");
        $stmt->execute();
        $stockLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $stmt = $conn->prepare("UPDATE medicaments SET nom = :nom, description = :description, numero_lot = :numero_lot, quantite = :quantite, date_expiration = :date_expiration, type_produit = :type_produit, stock_location_id = :stock_location_id WHERE id = :id");
            $stmt->bindParam(':nom', $_POST['nom']);
            $stmt->bindParam(':description', $_POST['description']);
            $stmt->bindParam(':numero_lot', $_POST['numero_lot']);
            $stmt->bindParam(':quantite', $_POST['quantite']);
            $stmt->bindParam(':date_expiration', $_POST['date_expiration']);
            $stmt->bindParam(':type_produit', $_POST['type_produit']);
            $stmt->bindParam(':stock_location_id', $_POST['stock_location_id']);
            $stmt->bindParam(':id', $medicament_id);
            $stmt->execute();

            header('Location: gestion_medicaments.php');
            exit();
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    header('Location: gestion_medicaments.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Modifier le Médicament</title>
    <style>
        body {
            background-color: #f4f6f9;
        }
        .container {
            margin-top: 20px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-size: 2em;
            margin-bottom: 20px;
            color: #007bff;
            text-align: center;
        }
        .form-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-submit, .btn-cancel, .btn-back {
            font-size: 1em;
            padding: 10px 20px;
            border-radius: 20px;
            transition: all 0.3s ease;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
        .btn-submit {
            background-color: #28a745;
            color: white;
            border: none;
        }
        .btn-cancel {
            background-color: #dc3545;
            color: white;
            border: none;
            margin-left: 15px;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            border: none;
        }
        .btn-submit:hover {
            background-color: #218838;
            transform: translateY(-3px);
        }
        .btn-cancel:hover {
            background-color: #c82333;
            transform: translateY(-3px);
        }
        .btn-back:hover {
            background-color: #5a6268;
            transform: translateY(-3px);
        }
        .button-group {
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>
<body>
<div class="container">
    <a href="gestion_medicaments.php" class="btn btn-back mb-3">
        <i class="fas fa-arrow-left"></i> Retour à la Gestion des Médicaments
    </a>
    <h2>Modifier le Médicament</h2>
    
    <form method="POST">
        <div class="form-section">
            <div class="form-group">
                <label for="nom">Nom du médicament</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($medicament['nom']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description"><?php echo htmlspecialchars($medicament['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="numero_lot">N° de lot</label>
                <input type="text" class="form-control" id="numero_lot" name="numero_lot" value="<?php echo htmlspecialchars($medicament['numero_lot']); ?>">
            </div>
            <div class="form-group">
                <label for="quantite">Quantité</label>
                <input type="number" class="form-control" id="quantite" name="quantite" value="<?php echo htmlspecialchars($medicament['quantite']); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_expiration">Date d'expiration</label>
                <input type="date" class="form-control" id="date_expiration" name="date_expiration" value="<?php echo htmlspecialchars($medicament['date_expiration']); ?>" required>
            </div>
            <div class="form-group">
                <label for="type_produit">Type de Produit</label>
                <select class="form-control" id="type_produit" name="type_produit">
                    <option value="PER OS" <?php if ($medicament['type_produit'] == 'PER OS') echo 'selected'; ?>>PER OS</option>
                    <option value="Injectable" <?php if ($medicament['type_produit'] == 'Injectable') echo 'selected'; ?>>Injectable</option>
                    <option value="Inhalable" <?php if ($medicament['type_produit'] == 'Inhalable') echo 'selected'; ?>>Inhalable</option>
                </select>
            </div>
            <div class="form-group">
                <label for="stock_location_id">Lieu de Stockage</label>
                <select class="form-control" id="stock_location_id" name="stock_location_id">
                    <?php foreach ($stockLocations as $location): ?>
                        <option value="<?php echo $location['id']; ?>" <?php if ($medicament['stock_location_id'] == $location['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($location['location_name'] . ($location['bag_name'] ? " - " . $location['bag_name'] : '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Boutons de Soumission et Annulation -->
        <div class="button-group">
            <button type="submit" class="btn btn-submit">Modifier</button>
            <a href="gestion_medicaments.php" class="btn btn-cancel">Annuler</a>
        </div>
    </form>
</div>

<!-- Scripts JavaScript -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
