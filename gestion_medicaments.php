<?php
// Connexion à la base de données
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les valeurs des filtres
$filterNom = isset($_GET['filter_nom']) ? $_GET['filter_nom'] : '';
$filterType = isset($_GET['filter_type']) ? $_GET['filter_type'] : '';
$sortColumn = isset($_GET['sort']) ? $_GET['sort'] : 'nom';
$sortOrder = isset($_GET['order']) && $_GET['order'] == 'desc' ? 'DESC' : 'ASC';

// Construire la requête avec filtres et tri
$query = "SELECT * FROM medicaments WHERE 1=1";

// Appliquer les filtres
if (!empty($filterNom)) {
    $query .= " AND nom LIKE :filterNom";
}
if (!empty($filterType)) {
    $query .= " AND type_produit = :filterType";
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

$stmt->execute();
$medicaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Gestion des Médicaments</title>
    <style>
        .card-header {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }
        .filter-form .form-control {
            border-radius: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-primary, .btn-secondary, .btn-warning, .btn-danger {
            border-radius: 20px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h1 class="text-center mb-4">Gestion des Médicaments</h1>

    <!-- Formulaire de Filtrage -->
    <form method="GET" class="filter-form mb-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <input type="text" name="filter_nom" class="form-control" placeholder="Filtrer par nom" value="<?php echo htmlspecialchars($filterNom); ?>">
            </div>
            <div class="col-md-4 mb-3">
                <select name="filter_type" class="form-control">
                    <option value="">Filtrer par type</option>
                    <option value="PER OS" <?php echo $filterType == 'PER OS' ? 'selected' : ''; ?>>PER OS</option>
                    <option value="Injectable" <?php echo $filterType == 'Injectable' ? 'selected' : ''; ?>>Injectable</option>
                    <option value="Inhalable" <?php echo $filterType == 'Inhalable' ? 'selected' : ''; ?>>Inhalable</option>
                </select>
            </div>
            <div class="col-md-4 text-center">
                <button type="submit" class="btn btn-primary mr-2">Appliquer les filtres</button>
                <a href="gestion_medicaments.php" class="btn btn-secondary">Réinitialiser</a>
            </div>
        </div>
    </form>

    <!-- Liste des Médicaments -->
    <div class="card">
        <div class="card-header">
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
