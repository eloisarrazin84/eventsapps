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
    <style>
        .form-container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        .btn-custom {
            border-radius: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Ajouter un Médicament</h2>
    <div class="form-container">
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
                <label for="numero_lot">N° de lot</label>
                <input type="text" class="form-control" id="numero_lot" name="numero_lot">
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
                <label for="type_produit">Type de Produit</label>
                <select class="form-control" id="type_produit" name="type_produit">
                    <option value="PER OS">PER OS</option>
                    <option value="Injectable">Injectable</option>
                    <option value="Inhalable">Inhalable</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success btn-custom">Ajouter</button>
            <a href="dashboard_medicaments.php" class="btn btn-secondary btn-custom">Retour</a>
        </form>
    </div>
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
