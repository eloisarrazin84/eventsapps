<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les statistiques
$totalMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments")->fetchColumn();
$expiredMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration < CURDATE()")->fetchColumn();
$expiringMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)")->fetchColumn();
$typesProduits = $conn->query("SELECT type_produit, COUNT(*) as count FROM medicaments GROUP BY type_produit")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les médicaments expirant bientôt
$medicamentsExpiringSoon = $conn->query("SELECT * FROM medicaments WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 MONTH)")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Dashboard Médicaments</title>
</head>
<body>
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
                    <h5 class="card-title">Expirant Bientôt</h5>
                    <p class="card-text"><?php echo $expiringMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Types de Produits</h5>
                    <ul>
                        <?php foreach ($typesProduits as $type): ?>
                            <li><?php echo htmlspecialchars($type['type_produit']); ?>: <?php echo $type['count']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des médicaments expirant bientôt -->
    <h3 class="mt-5">Liste des Médicaments Expirant Bientôt</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Description</th>
                <th>Quantité</th>
                <th>Date d'expiration</th>
                <th>Type de Produit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($medicamentsExpiringSoon as $medicament): ?>
                <tr>
                    <td><?php echo htmlspecialchars($medicament['nom']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['description']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['quantite']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['date_expiration']); ?></td>
                    <td><?php echo htmlspecialchars($medicament['type_produit']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Scripts Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
