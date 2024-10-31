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

// Appliquer les filtres
if (!empty($filterNom)) {
    $query .= " AND nom LIKE :filterNom";
}
if (!empty($filterType)) {
    $query .= " AND type_produit = :filterType";
}
if (!empty($filterLocation)) {
    $query .= " AND stock_location_id = :filterLocation";
}

// Ajouter le tri
$query .= " ORDER BY $sortColumn $sortOrder";

// Préparer la requête
$stmt = $conn->prepare($query);

// Lier les paramètres des filtres
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

// Récupérer les lieux de stockage
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

    .filter-form .form-control {
        margin-bottom: 15px;
        padding: 10px;
        border-radius: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-primary,
    .btn-secondary {
        border-radius: 20px;
        padding: 10px 20px;
    }

    .card-header {
        background-color: #007bff;
        color: #fff;
        font-weight: bold;
        padding: 15px;
        font-size: 1.2em;
    }

    .table {
        margin-top: 15px;
        font-size: 1em;
        border-spacing: 0 10px;
    }

    .table th,
    .table td {
        vertical-align: middle;
        padding: 15px;
        text-align: center;
    }

    .table tbody tr {
        background-color: #f9f9f9;
        border-radius: 10px;
    }

    .btn-warning,
    .btn-danger {
        border-radius: 15px;
        padding: 5px 15px;
        margin-right: 5px;
    }

    .btn-warning {
        background-color: #ffc107;
        color: #000;
        border: none;
    }

    .btn-danger {
        background-color: #dc3545;
        color: #fff;
        border: none;
    }

    .btn-warning:hover {
        background-color: #ffca2c;
    }

    .btn-danger:hover {
        background-color: #e02a32;
    }
</style>

</head>
<body>
<div class="container mt-5">
    <!-- Titre de la page et bouton de retour -->
    <a href="dashboard_medicaments.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> Retour au Tableau de Bord</a>
    <h1>Gestion des Médicaments</h1>

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
    <div class="card">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-capsules"></i> Médicaments Disponibles
        </div>
        <div class="card-body">
            <table class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th><a href="?sort=nom&order=<?php echo $sortOrder == 'ASC' ? 'desc' : 'asc'; ?>">Nom</a></th>
                        <th>Description</th>
                        <th>N° de lot</th>
                        <th><a href="?sort=quantite&order=<?php echo $sortOrder == 'ASC' ? 'desc' : 'asc'; ?>">Quantité</a></th>
                        <th><a href="?sort=date_expiration&order=<?php echo $sortOrder == 'ASC' ? 'desc' : 'asc'; ?>">Date d'expiration</a></th>
                        <th><a href="?sort=type_produit&order=<?php echo $sortOrder == 'ASC' ? 'desc' : 'asc'; ?>">Type</a></th>
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
                                <a href="modifier_medicament.php?id=<?php echo $medicament['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Modifier</a>
                                <a href="supprimer_medicament.php?id=<?php echo $medicament['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce médicament ?');"><i class="fas fa-trash-alt"></i> Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Scripts JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
