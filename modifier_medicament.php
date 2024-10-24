<?php
session_start();
if (isset($_GET['id'])) {
    $medicament_id = $_GET['id'];
    try {
        // Connexion à la base de données
        $conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer les informations actuelles du médicament
        $stmt = $conn->prepare("SELECT * FROM medicaments WHERE id = :id");
        $stmt->bindParam(':id', $medicament_id);
        $stmt->execute();
        $medicament = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si le médicament existe
        if (!$medicament) {
            echo "Médicament introuvable.";
            exit();
        }

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $stmt = $conn->prepare("UPDATE medicaments SET nom = :nom, description = :description, quantite = :quantite, date_expiration = :date_expiration, categorie = :categorie, type_produit = :type_produit WHERE id = :id");
            $stmt->bindParam(':nom', $_POST['nom']);
            $stmt->bindParam(':description', $_POST['description']);
            $stmt->bindParam(':quantite', $_POST['quantite']);
            $stmt->bindParam(':date_expiration', $_POST['date_expiration']);
            $stmt->bindParam(':categorie', $_POST['categorie']);
            $stmt->bindParam(':type_produit', $_POST['type_produit']);
            $stmt->bindParam(':id', $medicament_id);

            if ($stmt->execute()) {
                echo "Médicament mis à jour avec succès.";
                header('Location: gestion_medicaments.php');
                exit();
            } else {
                echo "Erreur lors de la mise à jour.";
                var_dump($stmt->errorInfo());
            }
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    header('Location: gestion_medicaments.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Modifier un Médicament</title>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Modifier le Médicament</h2>
    <form method="POST" action="">
        <div class="form-group">
            <label for="nom">Nom du médicament</label>
            <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($medicament['nom']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($medicament['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="quantite">Quantité</label>
            <input type="number" class="form-control" id="quantite" name="quantite" value="<?php echo htmlspecialchars($medicament['quantite']); ?>" required>
        </div>
        <div class="form-group">
            <label for="date_expiration">Date d'expiration</label>
            <input type="date" class="form-control" id="date_expiration" name="date_expiration" value="<?php echo htmlspecialchars($medicament['date_expiration']); ?>" required>
        </div>
        <div class="form-group">
            <label for="categorie">Catégorie</label>
            <input type="text" class="form-control" id="categorie" name="categorie" value="<?php echo htmlspecialchars($medicament['categorie']); ?>">
        </div>
        <div class="form-group">
            <label for="type_produit">Type de Produit</label>
            <select class="form-control" id="type_produit" name="type_produit">
                <option value="PER OS" <?php echo ($medicament['type_produit'] == 'PER OS') ? 'selected' : ''; ?>>PER OS</option>
                <option value="Injectable" <?php echo ($medicament['type_produit'] == 'Injectable') ? 'selected' : ''; ?>>Injectable</option>
                <option value="Inhalable" <?php echo ($medicament['type_produit'] == 'Inhalable') ? 'selected' : ''; ?>>Inhalable</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
    </form>
    <div class="mt-4">
        <a href="gestion_medicaments.php" class="btn btn-secondary">Retour à la liste des médicaments</a>
    </div>
</div>
</body>
</html>
