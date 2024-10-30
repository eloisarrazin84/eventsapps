<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les statistiques des médicaments
$totalMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments")->fetchColumn();
$expiredMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration < CURDATE()")->fetchColumn();
$types = $conn->query("SELECT type_produit, COUNT(*) as count FROM medicaments GROUP BY type_produit")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <title>Dashboard Médicaments</title>
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .stat-card {
            padding: 20px;
            color: #fff;
        }
        .stat-title {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .stat-value {
            font-size: 2rem;
        }
        .chart-container {
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-5">Dashboard des Médicaments</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card stat-card bg-primary">
                    <div class="card-body">
                        <div class="stat-title">Total des Médicaments</div>
                        <div class="stat-value"><?php echo $totalMedicaments; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-danger">
                    <div class="card-body">
                        <div class="stat-title">Médicaments Expirés</div>
                        <div class="stat-value"><?php echo $expiredMedicaments; ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card bg-success">
                    <div class="card-body">
                        <div class="stat-title">Types de Médicaments</div>
                        <div class="stat-value">
                            <ul class="list-unstyled">
                                <?php foreach ($categories as $categorie): ?>
                                    <li><?php echo $categorie['categorie']; ?>: <?php echo $categorie['count']; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="chart-container mt-5">
            <h4 class="text-center">Répartition des Médicaments par Type</h4>
            <canvas id="medicamentChart"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('medicamentChart').getContext('2d');
        var medicamentChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($categories, 'categorie')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($categories, 'count')); ?>,
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
