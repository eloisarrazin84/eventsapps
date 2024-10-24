<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Gestion des Médicaments</title>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center">Gestion des Médicaments</h1>

    <!-- Formulaire d'ajout de médicament -->
    <div class="card mt-4">
        <div class="card-header">
            Ajouter un Médicament
        </div>
        <div class="card-body">
            <form method="POST" action="ajouter_medicament.php">
                <div class="form-group">
                    <label for="nom">Nom du médicament</label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="quantite">Quantité</label>
                    <input type="number" class="form-control" id="quantite" name="quantite" min="0">
                </div>
                <div class="form-group">
                    <label for="date_expiration">Date d'expiration</label>
                    <input type="date" class="form-control" id="date_expiration" name="date_expiration">
                </div>
                <div class="form-group">
                    <label for="categorie">Catégorie</label>
                    <input type="text" class="form-control" id="categorie" name="categorie">
                </div>
                <button type="submit" class="btn btn-success">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Liste des médicaments -->
    <div class="card mt-5">
        <div class="card-header">
            Médicaments Disponibles
        </div>
        <div class="card-body">
           <table class="table table-bordered">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Description</th>
            <th>Quantité</th>
            <th>Date d'expiration</th>
            <th>Catégorie</th>
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
                <td><?php echo htmlspecialchars($medicament['categorie']); ?></td>
                <td>
                    <!-- Bouton Modifier -->
                    <a href="modifier_medicament.php?id=<?php echo $medicament['id']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                    
                    <!-- Bouton Supprimer avec confirmation -->
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
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
