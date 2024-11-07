<?php
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les valeurs des filtres
$filterNom = isset($_GET['filter_nom']) ? $_GET['filter_nom'] : '';
$filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$filterLocation = isset($_GET['filter_location']) ? $_GET['filter_location'] : '';
$filterAmpoulier = isset($_GET['filter_ampoulier']) ? $_GET['filter_ampoulier'] : '';
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
if (!empty($filterAmpoulier)) {
    $query .= " AND ampoulier_type = :filterAmpoulier";
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
if (!empty($filterAmpoulier)) {
    $stmt->bindValue(':filterAmpoulier', $filterAmpoulier);
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
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 20px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 2em;
            margin-bottom: 30px;
            color: #007bff;
            text-align: center;
        }

        .action-menu {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-inline {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-right: 10px;
        }

        .btn {
            border-radius: 20px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background-color: #138496;
        }

        .table {
            margin-top: 15px;
            font-size: 1em;
            border-spacing: 0 10px;
        }

        .table th, .table td {
            vertical-align: middle;
            padding: 15px;
            text-align: center;
        }

        .table tbody tr {
            background-color: #f9f9f9;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Gestion des Médicaments</h1>

    <!-- Menu d'Actions -->
    <div class="action-menu">
        <a href="dashboard_medicaments.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <!-- Formulaire de Filtrage -->
    <div class="d-flex justify-content-end mb-4">
        <form method="GET" class="form-inline">
            <input type="text" name="filter_nom" class="form-control" placeholder="Nom" value="<?php echo htmlspecialchars($filterNom); ?>">
            <select name="filter_type" class="form-control">
                <option value="">Sélectionner Type</option>
                <option value="PER OS" <?php echo $filterType == 'PER OS' ? 'selected' : ''; ?>>PER OS</option>
                <option value="Injectable" <?php echo $filterType == 'Injectable' ? 'selected' : ''; ?>>Injectable</option>
                <option value="Inhalable" <?php echo $filterType == 'Inhalable' ? 'selected' : ''; ?>>Inhalable</option>
            </select>
            <select name="filter_location" class="form-control">
                <option value="">Sélectionner Lieu</option>
                <?php foreach ($stockLocations as $location): ?>
                    <option value="<?php echo $location['id']; ?>" <?php echo $filterLocation == $location['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($location['location_name'] . ($location['bag_name'] ? " - " . $location['bag_name'] : '')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="filter_ampoulier" class="form-control">
                <option value="">Sélectionner Ampoulier</option>
                <option value="Ampoulier Principal" <?php echo $filterAmpoulier == 'Ampoulier Principal' ? 'selected' : ''; ?>>Ampoulier Principal</option>
                <option value="Ampoulier de réserve" <?php echo $filterAmpoulier == 'Ampoulier de réserve' ? 'selected' : ''; ?>>Ampoulier de réserve</option>
                <option value="Caisse de réserve" <?php echo $filterAmpoulier == 'Caisse de réserve' ? 'selected' : ''; ?>>Caisse de réserve</option>
                <option value="Pochette médicament" <?php echo $filterAmpoulier == 'Pochette médicament' ? 'selected' : ''; ?>>Pochette médicament</option>
            </select>
            <button type="submit" class="btn btn-primary">Appliquer les filtres</button>
        </form>
    </div>

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
                <th>Ampoulier</th>
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
                    <td><?php echo htmlspecialchars($medicament['ampoulier_type']); ?></td>
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

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

