<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les statistiques des médicaments
$totalMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments")->fetchColumn();
$expiredMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration < CURDATE()")->fetchColumn();
$categories = $conn->query("SELECT type_produit, COUNT(*) as count FROM medicaments GROUP BY type_produit")->fetchAll(PDO::FETCH_ASSOC);

// Médicaments proches de la date de péremption (dans un mois)
$soonToExpire = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)")->fetchColumn();

// Exportation des médicaments expirant bientôt
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="rapport_peremption.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Nom', 'Description', 'Quantité', 'Date d\'expiration', 'Type de Produit']);

    $expiringSoon = $conn->query("SELECT * FROM medicaments WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)");
    foreach ($expiringSoon as $row) {
        fputcsv($output, [$row['nom'], $row['description'], $row['quantite'], $row['date_expiration'], $row['type_produit']]);
    }
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Dashboard Médicaments</title>
    <style>
        .bg-warning { background-color: #ffa500 !important; }
        .bg-danger { background-color: #dc3545 !important; }
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
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Proches de la Péremption</h5>
                    <p class="card-text"><?php echo $soonToExpire; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Type de Médicaments</h5>
                    <ul>
                        <?php foreach ($categories as $categorie): ?>
                            <li><?php echo $categorie['type_produit']; ?>: <?php echo $categorie['count']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bouton d'exportation de rapport -->
    <div class="text-right mt-3">
        <a href="dashboard_medicaments.php?export=1" class="btn btn-outline-info">Exporter le Rapport de Péremption</a>
    </div>
</div>

<!-- Scripts Bootstrap et jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
