<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Ajouter un Médicament</title>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Ajouter un Médicament</h2>
    <form method="POST" action="process_ajout_medicament.php">
        <div class="form-group">
            <label for="medicament_nom">Nom du médicament</label>
            <input type="text" class="form-control" id="medicament_nom" name="medicament_nom" required>
        </div>
        <div class="form-group">
            <label for="lot">Numéro de lot</label>
            <input type="text" class="form-control" id="lot" name="lot">
        </div>
        <div class="form-group">
            <label for="quantite">Quantité</label>
            <input type="number" class="form-control" id="quantite" name="quantite" required>
        </div>
        <div class="form-group">
            <label for="date_expiration">Date d'expiration</label>
            <input type="date" class="form-control" id="date_expiration" name="date_expiration" required>
        </div>
        <div class="form-group">
            <label for="zone">Zone</label>
            <input type="text" class="form-control" id="zone" name="zone" required>
        </div>
        <div class="form-group">
            <label for="type_produit">Type de produit</label>
            <select class="form-control" id="type_produit" name="type_produit" required>
                <option value="">Sélectionnez un type</option>
                <option value="PER OS">PER OS</option>
                <option value="Injectable">Injectable</option>
                <option value="Inhalable">Inhalable</option>
            </select>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Ajouter</button>
        <a href="dashboard.php" class="btn btn-secondary">Retour</a>
    </form>
</div>
</body>
</html>
