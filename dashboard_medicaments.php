<?php
session_start();
$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupérer les statistiques des médicaments
$totalMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments")->fetchColumn();
$expiredMedicaments = $conn->query("SELECT COUNT(*) FROM medicaments WHERE date_expiration < CURDATE()")->fetchColumn();
$categories = $conn->query("SELECT type_produit, COUNT(*) as count FROM medicaments GROUP BY type_produit")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les médicaments expirant dans moins de 30 jours
$expiringSoon = $conn->query("SELECT * FROM medicaments WHERE date_expiration BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Dashboard Médicaments</title>
    <style>
        .card-stats {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card-stats:hover {
            transform: scale(1.05);
            box-shadow: 0px 8px 12px rgba(0, 0, 0, 0.2);
        }
        .card-body ul {
            padding-left: 0;
            list-style: none;
        }
        .card-body ul li {
            font-size: 0.9rem;
        }
        .search-form {
            max-width: 600px;
            margin: auto;
        }
        .highlight-warning {
            background-color: #fff3cd;
        }
    </style>
</head>
<body>
<?php include 'menu_medicaments.php'; ?>
<div class="container mt-5">
    <h1 class="text-center mb-4">Dashboard des Médicaments</h1>
    <div class="text-center mt-3 mb-4">
        <a href="ajouter_medicament.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Ajouter un médicament
        </a>
    </div>
    
    <!-- Widgets de Statistiques -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card card-stats text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-pills"></i> Total des Médicaments</h5>
                    <p class="card-text display-4"><?php echo $totalMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-stats text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Médicaments Expirés</h5>
                    <p class="card-text display-4"><?php echo $expiredMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
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
    </div>

    <!-- Widget de Recherche -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form method="GET" action="recherche_medicament.php" class="form-inline justify-content-center search-form">
                <input type="text" class="form-control mr-2" name="search" placeholder="Rechercher un médicament..." style="width:70%;">
                <button type="submit" class="btn btn-outline-primary"><i class="fas fa-search"></i> Rechercher</button>
            </form>
        </div>
    </div>

    <!-- Médicaments Expirant dans 30 Jours -->
    <div class="card">
        <div class="card-header bg-warning text-white">
            Médicaments expirant dans moins de 30 jours
        </div>
        <div class="card-body">
            <?php if (count($expiringSoon) > 0): ?>
                <table class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th>Nom</th>
                            <th>Quantité</th>
                            <th>Date d'expiration</th>
                            <th>Type de Produit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expiringSoon as $med): ?>
                            <tr class="<?php echo (strtotime($med['date_expiration']) - time() <= 7 * 86400) ? 'highlight-warning' : ''; ?>">
                                <td><?php echo htmlspecialchars($med['nom']); ?></td>
                                <td><?php echo htmlspecialchars($med['quantite']); ?></td>
                                <td><?php echo htmlspecialchars($med['date_expiration']); ?></td>
                                <td><?php echo htmlspecialchars($med['type_produit']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">Aucun médicament n'expire dans les 30 prochains jours.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Scripts Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
