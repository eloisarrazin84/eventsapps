<?php
session_start();
$user_id = $_SESSION['user_id'];
$updateSuccess = false;

// Connect to database
$servername = "localhost";
$username_db = "root";  
$password_db = "Lipton2019!";
$dbname = "outdoorsec";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username_db, $password_db);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch the user's profile information
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Update user details
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $updateStmt = $conn->prepare("UPDATE users SET username = :username, email = :email, password = :password WHERE id = :user_id");
                $updateStmt->bindParam(':password', $hashed_password);
            } else {
                echo "Les mots de passe ne correspondent pas.";
                exit();
            }
        } else {
            $updateStmt = $conn->prepare("UPDATE users SET username = :username, email = :email WHERE id = :user_id");
        }

        $updateStmt->bindParam(':username', $username);
        $updateStmt->bindParam(':email', $email);
        $updateStmt->bindParam(':user_id', $user_id);
        $updateStmt->execute();

        $updateSuccess = true;
    }
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Mon profil</title>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center text-white bg-primary p-3">Votre profil</h1>
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label for="new_password">Nouveau mot de passe</label>
            <input type="password" class="form-control" id="new_password" name="new_password">
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirmer le nouveau mot de passe</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
        </div>
        <button type="submit" class="btn btn-primary">Modifier</button>
    </form>

    <!-- Button to return to the dashboard -->
    <a href="dashboard.php" class="btn btn-secondary mt-4">Retour au tableau de bord</a>

    <!-- Documents Section -->
    <h2 class="text-center mt-5">Vos Documents</h2>
    <form action="upload_document.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="document">Télécharger un document</label>
            <input type="file" class="form-control" id="document" name="document">
        </div>
        <button type="submit" class="btn btn-success">Télécharger</button>
    </form>

    <!-- List of uploaded documents -->
    <h3 class="mt-4">Documents Actuels</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom du Document</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Fetch and display the user's documents
            $stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($documents as $document) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($document['file_name']) . "</td>";
                echo "<td><a href='" . htmlspecialchars($document['file_path']) . "' target='_blank' class='btn btn-primary'>Voir</a>";
                echo " <a href='delete_document.php?doc_id=" . $document['id'] . "' class='btn btn-danger'>Supprimer</a></td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Succès</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Profil mis à jour avec succès!
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($_SERVER["REQUEST_METHOD"] == "POST" && $updateSuccess): ?>
<script>
    $(document).ready(function(){
        $('#successModal').modal('show');
    });
</script>
<?php endif; ?>

</body>
</html>
