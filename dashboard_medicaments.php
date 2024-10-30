<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les statistiques
$totalMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments")->fetchColumn();
$expiredMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration < CURDATE()")->fetchColumn();
$soonExpiringMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)")->fetchColumn();
$types = $conn->query("SELECT type_produit, COUNT(*) as count FROM medicaments GROUP BY type_produit")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Dashboard Médicaments</title>
    <style>
        .bg-soon-expiring { background-color: orange; color: white; }
    </style>
</head>
<body>
<?php include 'menu_medicaments.php'; ?>
<div class="container mt-5">
    <h1 class="text-center">Dashboard des Médicaments</h1>

    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total des Médicaments</h5>
                    <p class="card-text"><?php echo $totalMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Médicaments Expirés</h5>
                    <p class="card-text"><?php echo $expiredMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-soon-expiring mb-3">
                <div class="card-body">
                    <h5 class="card-title">Expirant Bientôt</h5>
                    <p class="card-text"><?php echo $soonExpiringMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Types de Produits</h5>
                    <ul>
                        <?php foreach ($types as $type): ?>
                            <li><?php echo $type['type_produit']; ?>: <?php echo $type['count']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Section de tri et filtrage -->
    <div class="row mt-4">
        <div class="col-md-12">
            <form method="GET" action="dashboard_medicaments.php" class="form-inline">
                <label for="filter_date" class="mr-2">Voir les médicaments expirant dans :</label>
                <select name="filter_date" class="form-control mr-2">
                    <option value="1">1 mois</option>
                    <option value="3">3 mois</option>
                    <option value="6">6 mois</option>
                    <option value="12">1 an</option>
                </select>
                <button type="submit" class="btn btn-info">Filtrer</button>
            </form>
        </div>
    </div>

    <!-- Bouton d'exportation du rapport -->
    <div class="mt-4 text-center">
        <a href="export_report.php" class="btn btn-outline-primary">Exporter le Rapport de Péremption</a>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
