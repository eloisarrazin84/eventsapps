<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les statistiques des médicaments
$totalMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments")->fetchColumn();
$expiredMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration < CURDATE()")->fetchColumn();
$upcomingExpirations = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchColumn();
$types = $conn->query("SELECT type_produit, COUNT(*) as count FROM medicaments GROUP BY type_produit")->fetchAll(PDO::FETCH_ASSOC);
$medicaments = $conn->query("SELECT * FROM medicaments ORDER BY date_expiration ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard Médicaments</title>
    <style>
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .chart-container {
            position: relative;
            height: 40vh;
            margin-top: 20px;
        }
        .table-responsive {
            margin-top: 30px;
        }
        .alert-badge {
            font-size: 0.8em;
            padding: 5px 10px;
            border-radius: 10px;
        }
    </style>
</head>
<body>
<?php include 'menu_medicaments.php'; ?>

<div class="container mt-5">
    <h1 class="text-center">Dashboard des Médicaments</h1>

    <!-- Statistiques des médicaments -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total des Médicaments</h5>
                    <p class="card-text"><?php echo $totalMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Médicaments Expirés</h5>
                    <p class="card-text"><?php echo $expiredMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Prochaines Expirations (30 jours)</h5>
                    <p class="card-text"><?php echo $upcomingExpirations; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques des types de médicaments -->
    <div class="chart-container">
        <canvas id="typeChart"></canvas>
    </div>

    <!-- Tableau des médicaments -->
    <div class="table-responsive">
        <h3 class="text-center mt-5">Liste des Médicaments</h3>
        <table class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Quantité</th>
                    <th>Date d'expiration</th>
                    <th>Type de Produit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($medicaments as $medicament): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($medicament['nom']); ?></td>
                        <td><?php echo htmlspecialchars($medicament['description']); ?></td>
                        <td><?php echo htmlspecialchars($medicament['quantite']); ?></td>
                        <td><?php echo htmlspecialchars($medicament['date_expiration']); ?></td>
                        <td><?php echo htmlspecialchars($medicament['type_produit']); ?></td>
                        <td>
                            <a href="modifier_medicament.php?id=<?php echo $medicament['id']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                            <a href="supprimer_medicament.php?id=<?php echo $medicament['id']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce médicament ?');">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Graphique Chart.js -->
<script>
    const typeData = {
        labels: <?php echo json_encode(array_column($types, 'type_produit')); ?>,
        datasets: [{
            label: 'Types de Médicaments',
            data: <?php echo json_encode(array_column($types, 'count')); ?>,
            backgroundColor: [
                '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'
            ],
            borderColor: [
                '#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'
            ],
            borderWidth: 1
        }]
    };

    const config = {
        type: 'bar',
        data: typeData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    };

    var typeChart = new Chart(
        document.getElementById('typeChart'),
        config
    );
</script>
</body>
</html>
