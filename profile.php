<?php
session_start();
$user_id = $_SESSION['user_id'];

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

        echo "Profil mis à jour avec succès!";
    }

    // Handle file upload
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $documentName = $_FILES['document']['name'];
        $documentTmpPath = $_FILES['document']['tmp_name'];
        $destination = 'uploads/' . $documentName;

        if (move_uploaded_file($documentTmpPath, $destination)) {
            $stmt = $conn->prepare("INSERT INTO documents (user_id, document_name) VALUES (:user_id, :document_name)");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':document_name', $documentName);
            $stmt->execute();
        }
    }

    // Fetch user documents
    $stmt = $conn->prepare("SELECT * FROM documents WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .btn {
            border-radius: 20px;
        }
        .document-table {
            margin-top: 20px;
        }
        .document-table th, .document-table td {
            text-align: center;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header text-center">
            <h1>Votre profil</h1>
        </div>
        <div class="card-body">
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
                <button type="submit" class="btn btn-primary btn-block">Modifier</button>
            </form>
            <a href="dashboard.php" class="btn btn-secondary btn-block mt-4">Retour au tableau de bord</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Vos Documents</h3>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="document">Télécharger un document</label>
                    <input type="file" class="form-control-file" id="document" name="document">
                </div>
                <button type="submit" class="btn btn-success btn-block">Télécharger</button>
            </form>

            <table class="table table-striped mt-4">
    <thead>
        <tr>
            <th>Nom du Document</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($documents as $document): ?>
            <tr>
                <td><?php echo htmlspecialchars($document['file_name']); ?></td>
                <td>
                    <a href="<?php echo $document['file_path']; ?>" class="btn btn-primary btn-sm" target="_blank">Voir</a>
                    <form action="delete_document.php" method="POST" style="display:inline;">
                        <input type="hidden" name="document_id" value="<?php echo $document['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                    </form>
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
