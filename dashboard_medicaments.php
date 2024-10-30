<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <title>Dashboard Médicaments</title>
    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .bg-primary, .bg-danger, .bg-success {
            background: linear-gradient(135deg, #007bff, #0056b3);
        }
        .bg-danger {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }
        .bg-success {
            background: linear-gradient(135deg, #28a745, #218838);
        }
        .badge {
            font-size: 1rem;
            padding: 0.5rem;
        }
    </style>
</head>
<body>
<?php include 'menu_medicaments.php'; ?>
<div class="container mt-5">
    <h1 class="text-center mb-5">Dashboard des Médicaments</h1>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body text-center">
                    <i class="fas fa-pills fa-3x mb-3"></i>
                    <h5 class="card-title">Total des Médicaments</h5>
                    <p class="card-text fs-3"><?php echo $totalMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h5 class="card-title">Médicaments Expirés</h5>
                    <p class="card-text fs-3"><?php echo $expiredMedicaments; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body text-center">
                    <i class="fas fa-tags fa-3x mb-3 text-success"></i>
                    <h5 class="card-title">Catégories</h5>
                    <ul class="list-unstyled">
                        <?php foreach ($categories as $categorie): ?>
                            <li><span class="badge bg-secondary"><?php echo htmlspecialchars($categorie['categorie']); ?>: <?php echo $categorie['count']; ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
