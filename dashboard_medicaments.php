<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les statistiques des médicaments
$totalMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments")->fetchColumn();
$expiredMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration < CURDATE()")->fetchColumn();
$typesProduits = $conn->query("SELECT type_produit, COUNT(*) as count FROM medicaments GROUP BY type_produit")->fetchAll(PDO::FETCH_ASSOC);

// Liste des médicaments expirés
$medicamentsExpires = $conn->query("SELECT nom, quantite, date_expiration FROM medicaments WHERE date_expiration < CURDATE()")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Dashboard Médicaments</title>
</head>
<body>
<?php include 'menu_medicaments.php'; ?>
<div class="container mt-5">
    <h1 class="text-center mb-4">Dashboard des Médicaments</h1>

    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-pills"></i> Total des Médicaments</h5>
                    <p class="display-4"><?php echo $totalMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Médicaments Expirés</h5>
                    <p class="display-4"><?php echo $expiredMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-layer-group"></i> Types de Produits</h5>
                    <canvas id="typeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des médicaments expirés -->
    <h3 class="mt-5">Médicaments Expirés</h3>
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>Nom</th>
                <th>Quantité</th>
                <th>Date d'expiration</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($medicamentsExpires as $medicament): ?>
                <tr>
                    <td><?php echo htmlspecialchars($medicament['nom']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['quantite']); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($medicament['date_expiration'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
<script>
    // Graphique Type de Produit
    const ctx = document.getElementById('typeChart').getContext('2d');
    const typeChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($typesProduits, 'type_produit')); ?>,
            datasets: [{
                label: 'Types de Produits',
                data: <?php echo json_encode(array_column($typesProduits, 'count')); ?>,
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
</script>

</body>
</html>
