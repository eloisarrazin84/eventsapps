<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les statistiques des médicaments
$totalMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments")->fetchColumn();
$expiredMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration < CURDATE()")->fetchColumn();
$categories = $conn->query("SELECT type_produit, COUNT(*) as count FROM medicaments GROUP BY type_produit")->fetchAll(PDO::FETCH_ASSOC);
$totalLocations = $conn->query("SELECT COUNT(DISTINCT stock_location_id) FROM medicaments")->fetchColumn();

// Récupérer les médicaments expirant dans les 7 jours
$expiringSoon7Days = $conn->prepare("SELECT medicaments.nom, medicaments.date_expiration, medicaments.numero_lot, stock_locations.location_name, stock_locations.bag_name 
                                     FROM medicaments 
                                     LEFT JOIN stock_locations ON medicaments.stock_location_id = stock_locations.id 
                                     WHERE medicaments.date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                                     ORDER BY medicaments.date_expiration ASC");
$expiringSoon7Days->execute();
$expiringSoonMeds7Days = $expiringSoon7Days->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les données pour les graphiques
// 1. Graphique des types de médicaments
$categoryData = ['labels' => [], 'data' => []];
foreach ($categories as $categorie) {
    $categoryData['labels'][] = $categorie['type_produit'];
    $categoryData['data'][] = $categorie['count'];
}

// 2. Graphique des expirations par mois
$expirationData = $conn->query("SELECT DATE_FORMAT(date_expiration, '%Y-%m') as month, COUNT(*) as count 
                                FROM medicaments 
                                WHERE date_expiration >= CURDATE()
                                GROUP BY month 
                                ORDER BY month ASC")->fetchAll(PDO::FETCH_ASSOC);
$expirationGraphData = ['labels' => [], 'data' => []];
foreach ($expirationData as $exp) {
    $expirationGraphData['labels'][] = $exp['month'];
    $expirationGraphData['data'][] = $exp['count'];
}

// 3. Graphique de stock par lieu de stockage
$stockByLocation = $conn->query("SELECT stock_locations.location_name, COUNT(medicaments.id) as count
                                FROM medicaments
                                LEFT JOIN stock_locations ON medicaments.stock_location_id = stock_locations.id
                                GROUP BY stock_locations.location_name")->fetchAll(PDO::FETCH_ASSOC);
$locationGraphData = ['labels' => [], 'data' => []];
foreach ($stockByLocation as $location) {
    $locationGraphData['labels'][] = $location['location_name'] ?? 'Non spécifié';
    $locationGraphData['data'][] = $location['count'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard Médicaments</title>
    <style>
        body { background-color: #f4f6f9; }
        .container { margin-top: 20px; }
        h1 { color: #007bff; font-size: 2.5em; text-align: center; }
        .card-stats { transition: transform 0.3s ease, box-shadow 0.3s ease; cursor: pointer; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); }
        .card-stats:hover { transform: scale(1.05); box-shadow: 0px 8px 12px rgba(0, 0, 0, 0.2); }
        .chart-container { margin-top: 30px; }
        .highlight-warning { background-color: #fff3cd; }
        .reminder-section { background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .reminder-section h4 { margin: 0 0 10px; }
    </style>
</head>
<body>
<?php include 'menus/menu_medicaments.php'; ?>

<div class="container">
    <h1 class="mb-4">Dashboard des Médicaments</h1>
    
    <!-- Section de Rappels pour Médicaments Expirant sous 7 Jours -->
    <div class="reminder-section">
        <h4><i class="fas fa-exclamation-circle"></i> Médicaments expirant dans les 7 jours</h4>
        <?php if (count($expiringSoonMeds7Days) > 0): ?>
            <ul>
                <?php foreach ($expiringSoonMeds7Days as $med): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($med['nom']); ?></strong> - Lot: <?php echo htmlspecialchars($med['numero_lot']); ?>,
                        Lieu: <?php echo htmlspecialchars($med['location_name'] . ($med['bag_name'] ? " - " . $med['bag_name'] : '')); ?>,
                        Expire le : <?php echo htmlspecialchars($med['date_expiration']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>Aucun médicament n'expire dans les 7 prochains jours.</p>
        <?php endif; ?>
    </div>
    
    <!-- Widgets de Statistiques -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card card-stats text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-pills"></i> Total des Médicaments</h5>
                    <p class="card-text display-4"><?php echo $totalMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Médicaments Expirés</h5>
                    <p class="card-text display-4"><?php echo $expiredMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-tags"></i> Types de Médicaments</h5>
                    <ul>
                        <?php foreach ($categories as $categorie): ?>
                            <li><strong><?php echo htmlspecialchars($categorie['type_produit']); ?>:</strong> <?php echo $categorie['count']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-warehouse"></i> Lieux de Stockage</h5>
                    <p class="card-text display-4"><?php echo $totalLocations; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section des Graphiques -->
    <div class="chart-container">
        <div class="row">
            <div class="col-md-4">
                <canvas id="typeChart"></canvas>
            </div>
            <div class="col-md-4">
                <canvas id="expirationChart"></canvas>
            </div>
            <div class="col-md-4">
                <canvas id="locationChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Script pour les graphiques avec Chart.js -->
<script>
    const typeData = <?php echo json_encode($categoryData); ?>;
    const expirationData = <?php echo json_encode($expirationGraphData); ?>;
    const locationData = <?php echo json_encode($locationGraphData); ?>;

    new Chart(document.getElementById('typeChart'), {
        type: 'pie',
        data: {
            labels: typeData.labels,
            datasets: [{
                label: 'Types de Médicaments',
                data: typeData.data,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } }
        }
    });

    new Chart(document.getElementById('expirationChart'), {
        type: 'bar',
        data: {
            labels: expirationData.labels,
            datasets: [{
                label: 'Médicaments Expirant par Mois',
                data: expirationData.data,
                backgroundColor: '#36A2EB'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });

    new Chart(document.getElementById('locationChart'), {
        type: 'bar',
        data: {
            labels: locationData.labels,
            datasets: [{
                label: 'Stock par Lieu de Stockage',
                data: locationData.data,
                backgroundColor: '#FF6384'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });
</script>

<!-- Scripts Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

