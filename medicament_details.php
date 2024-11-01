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
        body {
            background-color: #f4f6f9;
        }
        .container {
            margin-top: 40px;
        }
        .card-identity {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            margin: auto;
        }
        .card-identity h2 {
            font-size: 1.8em;
            color: #007bff;
            margin-bottom: 20px;
        }
        .card-identity img {
            max-width: 200px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        }
        .info-section {
            text-align: left;
        }
        .info-row {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            font-size: 1em;
        }
        .info-label {
            font-weight: bold;
            color: #333;
            width: 140px;
            display: flex;
            align-items: center;
        }
        .info-label i {
            margin-right: 8px;
            color: #007bff;
        }
        .info-value {
            color: #555;
            flex: 1;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            font-size: 1em;
            padding: 10px 20px;
            background-color: #6c757d;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-back:hover {
            background-color: #5a6268;
            color: #fff;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card-identity">
        <h2><?php echo htmlspecialchars($medicament['nom']); ?></h2>
        
        <!-- Image du Médicament -->
        <?php if (!empty($medicament['photo_path'])): ?>
            <img src="<?php echo htmlspecialchars($medicament['photo_path']); ?>" alt="Photo de <?php echo htmlspecialchars($medicament['nom']); ?>" class="img-fluid">
        <?php else: ?>
            <img src="default_med_image.png" alt="Image par défaut" class="img-fluid">
        <?php endif; ?>
        
        <!-- Informations du Médicament -->
        <div class="info-section">
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
                <div class="info-label"><i class="fas fa-calendar-alt"></i> Expiration:</div>
                <div class="info-value"><?php echo htmlspecialchars($medicament['date_expiration']); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label"><i class="fas fa-map-marker-alt"></i> Stockage:</div>
                <div class="info-value"><?php echo htmlspecialchars($medicament['location_name'] . ($medicament['bag_name'] ? " - " . $medicament['bag_name'] : '')); ?></div>
            </div>
        </div>

        <!-- Bouton Retour -->
        <a href="gestion_medicaments.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à la Liste des Médicaments</a>
    </div>
</div>
</body>
</html>
