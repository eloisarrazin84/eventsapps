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
    <title>Dashboard Médicaments</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .card {
            border-radius: 10px;
        }
        .card-title {
            font-size: 1.25rem;
        }
        .text-center {
            margin-bottom: 20px;
        }
        .stats-list {
            list-style-type: none;
            padding-left: 0;
        }
        .stats-list li {
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
<?php include 'menu_medicaments.php'; ?>

<div class="container mt-5">
    <h1 class="text-center">Dashboard des Médicaments</h1>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total des Médicaments</h5>
                    <p class="card-text display-4"><?php echo $totalMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Médicaments Expirés</h5>
                    <p class="card-text display-4"><?php echo $expiredMedicaments; ?></p>
                </div>
            </div>
        </div>
         <div class="col-md-4">
       <div class="container mt-5">
    <h2 class="text-center">Répartition des Médicaments par Type</h2>
    <canvas id="typeChart"></canvas>
</div>
        </div>     
    </div>
</div>

<!-- Scripts Bootstrap et jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>    
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('typeChart').getContext('2d');
        const typeChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: [
                    <?php foreach ($categories as $categorie): ?>
                        "<?php echo $categorie['categorie']; ?>",
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Médicaments par Catégorie',
                    data: [
                        <?php foreach ($categories as $categorie): ?>
                            <?php echo $categorie['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 206, 86, 0.2)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                },
            },
        });
    });
</script>    
</body>
</html>
