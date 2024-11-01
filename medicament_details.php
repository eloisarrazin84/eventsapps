<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer l'ID du médicament
$medicament_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT medicaments.*, stock_locations.location_name, stock_locations.bag_name 
                        FROM medicaments 
                        LEFT JOIN stock_locations ON medicaments.stock_location_id = stock_locations.id 
                        WHERE medicaments.id = :id");
$stmt->bindParam(':id', $medicament_id, PDO::PARAM_INT);
$stmt->execute();
$medicament = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$medicament) {
    echo "Médicament non trouvé.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Détails du Médicament</title>
    <style>
        .card-identity {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        .card-identity img {
            max-width: 100%;
            border-radius: 10px;
        }
        .card-identity h2 {
            font-size: 1.8em;
            color: #007bff;
        }
        .card-identity .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .card-identity .info-label {
            font-weight: bold;
            color: #333;
            width: 150px;
        }
        .card-identity .info-value {
            color: #555;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card-identity">
        <h2><?php echo htmlspecialchars($medicament['nom']); ?></h2>
        
        <?php if (!empty($medicament['photo_path'])): ?>
            <img src="<?php echo htmlspecialchars($medicament['photo_path']); ?>" alt="Photo de <?php echo htmlspecialchars($medicament['nom']); ?>" class="img-fluid my-3">
        <?php else: ?>
            <img src="default_med_image.png" alt="Image par défaut" class="img-fluid my-3">
        <?php endif; ?>
        
        <div class="info-row">
            <div class="info-label"><i class="fas fa-info-circle"></i> Description:</div>
            <div class="info-value"><?php echo htmlspecialchars($medicament['description']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label"><i class="fas fa-tag"></i> Type:</div>
            <div class="info-value"><?php echo htmlspecialchars($medicament['type_produit']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label"><i class="fas fa-barcode"></i> N° de Lot:</div>
            <div class="info-value"><?php echo htmlspecialchars($medicament['numero_lot']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label"><i class="fas fa-cubes"></i> Quantité:</div>
            <div class="info-value"><?php echo htmlspecialchars($medicament['quantite']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label"><i class="fas fa-calendar-alt"></i> Date d'Expiration:</div>
            <div class="info-value"><?php echo htmlspecialchars($medicament['date_expiration']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label"><i class="fas fa-map-marker-alt"></i> Lieu de Stockage:</div>
            <div class="info-value"><?php echo htmlspecialchars($medicament['location_name'] . ($medicament['bag_name'] ? " - " . $medicament['bag_name'] : '')); ?></div>
        </div>

        <a href="gestion_medicaments.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Retour à la Liste des Médicaments</a>
    </div>
</div>
</body>
</html>
