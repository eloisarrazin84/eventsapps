<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Ajouter un Médicament</title>
    <!-- Ajoutez la bibliothèque jQuery UI pour l'auto-suggestion -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
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
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description"></textarea>
        </div>
        <div class="form-group">
            <label for="description">N° de lot</label>
            <textarea class="form-control" id="numero_lot" name="numero_lot"></textarea>
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
            <label for="categorie">Catégorie</label>
            <input type="text" class="form-control" id="categorie" name="categorie">
        </div>
        <button type="submit" class="btn btn-success">Ajouter</button>
        <a href="dashboard_medicaments.php" class="btn btn-secondary">Retour</a>
    </form>
</div>

<script>
$(document).ready(function() {
    $("#medicament_nom").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "search_medicaments.php",
                dataType: "json",
                data: {
                    term: request.term
                },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 2 // Commence la suggestion après 2 caractères
    });
});
</script>
</body>
</html>
