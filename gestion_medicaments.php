<?php
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les valeurs des filtres
$filterNom = isset($_GET['filter_nom']) ? $_GET['filter_nom'] : '';
$filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filterLocation = isset($_GET['filter_location']) ? $_GET['filter_location'] : '';
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'nom';
$sortOrder = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';

// Construire la requête avec filtres et tri
$query = "SELECT medicaments.*, stock_locations.location_name, stock_locations.bag_name 
          FROM medicaments 
          LEFT JOIN stock_locations ON medicaments.stock_location_id = stock_locations.id 
          WHERE 1=1";

if (!empty($filterNom)) {
    $query .= " AND nom LIKE :filterNom";
}
if (!empty($filterType)) {
    $query .= " AND type_produit = :filterType";
}
if (!empty($filterLocation)) {
    $query .= " AND stock_location_id = :filterLocation";
}

$query .= " ORDER BY $sortColumn $sortOrder";

$stmt = $conn->prepare($query);

if (!empty($filterNom)) {
    $stmt->bindValue(':filterNom', "%$filterNom%");
}
if (!empty($filterType)) {
    $stmt->bindValue(':filterType', $filterType);
}
if (!empty($filterLocation)) {
    $stmt->bindValue(':filterLocation', $filterLocation);
}

$stmt->execute();
$medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM stock_locations");
$stmt->execute();
$stockLocations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Médicaments</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container {
            max-width: 800px;
            margin-top: 50px;
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-weight: bold;
            color: #007bff;
            text-align: center;
            margin-bottom: 30px;
        }
        .action-menu {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .btn-primary, .btn-danger, .btn-secondary {
            border-radius: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-back {
            margin-bottom: 20px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard_medicaments.php" class="btn btn-back mb-4"><i class="fas fa-arrow-left"></i> Retour au Tableau de Bord</a>
    
    <h1>Gestion des Médicaments</h1>

    <div class="action-menu">
        <a href="ajouter_medicament.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Ajouter un Médicament
        </a>
        <button class="btn btn-info" data-toggle="modal" data-target="#pdfModal">
            <i class="fas fa-file-pdf"></i> Générer Inventaire PDF
        </button>
    </div>

    <!-- Formulaire de Filtrage -->
    <form method="GET" class="form-inline justify-content-center mb-4">
        <input type="text" name="filter_nom" class="form-control mr-2" placeholder="Filtrer par nom" value="<?php echo htmlspecialchars($filterNom); ?>">
        <select name="filter_type" class="form-control mr-2">
            <option value="">Filtrer par type</option>
            <option value="PER OS" <?php echo $filterType == 'PER OS' ? 'selected' : ''; ?>>PER OS</option>
            <option value="Injectable" <?php echo $filterType == 'Injectable' ? 'selected' : ''; ?>>Injectable</option>
            <option value="Inhalable" <?php echo $filterType == 'Inhalable' ? 'selected' : ''; ?>>Inhalable</option>
        </select>
        <select name="filter_location" class="form-control mr-2">
            <option value="">Filtrer par lieu de stockage</option>
            <?php foreach ($stockLocations as $location): ?>
                <option value="<?php echo $location['id']; ?>" <?php echo $filterLocation == $location['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($location['location_name'] . ($location['bag_name'] ? " - " . $location['bag_name'] : '')); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary mr-2">Appliquer les filtres</button>
        <a href="gestion_medicaments.php" class="btn btn-secondary">Réinitialiser</a>
    </form>

    <!-- Liste des Médicaments -->
    <table class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>N° de lot</th>
                <th>Quantité</th>
                <th>Date d'expiration</th>
                <th>Type</th>
                <th>Lieu de Stockage</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($medicaments as $medicament): ?>
                <tr>
                    <td><?php echo htmlspecialchars($medicament['nom']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['description']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['numero_lot']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['quantite']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['date_expiration']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['type_produit']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['location_name'] . ($medicament['bag_name'] ? " - " . $medicament['bag_name'] : '')); ?></td>
                    <td>
                        <a href="medicament_details.php?id=<?php echo $medicament['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Voir Détails</a>
                        <a href="modifier_medicament.php?id=<?php echo $medicament['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Modifier</a>
                        <a href="supprimer_medicament.php?id=<?php echo $medicament['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce médicament ?');"><i class="fas fa-trash-alt"></i> Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal pour sélectionner le lieu de stockage et télécharger la signature pour le PDF -->
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pdfModalLabel">Générer PDF d'Inventaire avec Signature</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="generer_pdf.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="locationSelect">Sélectionner le lieu de stockage</label>
                        <select class="form-control" id="locationSelect" name="location_id" required>
                            <?php foreach ($stockLocations as $location): ?>
                                <option value="<?php echo $location['id']; ?>">
                                    <?php echo htmlspecialchars($location['location_name'] . ($location['bag_name'] ? " - " . $location['bag_name'] : '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="signatureImage">Télécharger une image de la signature :</label>
                        <input type="file" class="form-control" id="signatureImage" name="signature_image" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Générer PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
