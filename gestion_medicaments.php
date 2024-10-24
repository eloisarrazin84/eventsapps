<?php
// Include the database connection
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Get filter values if they are set
$filterNom = isset($_GET['filter_nom']) ? $_GET['filter_nom'] : '';
$filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'nom';
$sortOrder = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';

// Create query with filters and sorting
$query = "SELECT * FROM medicaments WHERE 1=1";

// Apply filters
if (!empty($filterNom)) {
    $query .= " AND nom LIKE :filterNom";
}
if (!empty($filterType)) {
    $query .= " AND type_produit = :filterType";
}

// Add sorting
$query .= " ORDER BY $sortColumn $sortOrder";

// Prepare the statement
$stmt = $conn->prepare($query);

// Bind filter parameters if they exist
if (!empty($filterNom)) {
    $stmt->bindValue(':filterNom', "%$filterNom%");
}
if (!empty($filterType)) {
    $stmt->bindValue(':filterType', $filterType);
}

$stmt->execute();
$medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Gestion des Médicaments</title>
    <style>
        .card { margin-bottom: 20px; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Gestion des Médicaments</h1>

    <!-- Filtering Form -->
    <form method="GET" class="mb-3">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="filter_nom" class="form-control" placeholder="Filtrer par nom" value="<?php echo htmlspecialchars($filterNom); ?>">
            </div>
            <div class="col-md-4">
                <select name="filter_type" class="form-control">
                    <option value="">Filtrer par type</option>
                    <option value="PER OS" <?php echo $filterType == 'PER OS' ? 'selected' : ''; ?>>PER OS</option>
                    <option value="Injectable" <?php echo $filterType == 'Injectable' ? 'selected' : ''; ?>>Injectable</option>
                    <option value="Inhalable" <?php echo $filterType == 'Inhalable' ? 'selected' : ''; ?>>Inhalable</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Appliquer les filtres</button>
                <a href="gestion_medicaments.php" class="btn btn-secondary">Réinitialiser</a>
            </div>
        </div>
    </form>

    <!-- Liste des médicaments -->
    <div class="card">
        <div class="card-header bg-info text-white">
            Médicaments Disponibles
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
                            <td>
                                <a href="modifier_medicament.php?id=<?php echo $medicament['id']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                                <a href="supprimer_medicament.php?id=<?php echo $medicament['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce médicament ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="mt-4 text-center">
                <a href="dashboard_medicaments.php" class="btn btn-secondary">Retour au Tableau de Bord</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
