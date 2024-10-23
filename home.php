<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Mes Applications</title>
    <style>
        .app-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 50px;
        }

        .app-icon {
            width: 200px;
            height: 200px;
            background: linear-gradient(145deg, #1e90ff, #00bfff);
            border-radius: 15px;
            margin: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
        }

        .app-icon:hover {
            transform: scale(1.05);
            cursor: pointer;
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.2);
        }

        .app-icon i {
            font-size: 3rem;
            color: white;
        }

        .app-name {
            margin-top: 10px;
            font-size: 1.2rem;
            color: white;
            text-align: center;
        }

        h1 {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5rem;
            color: #333;
        }

        .logo-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .logo-container img {
            width: 100px;
            height: auto;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .app-icon {
                width: 150px;
                height: 150px;
            }

            .app-name {
                font-size: 1rem;
            }

            h1 {
                font-size: 2rem;
            }

            .logo-container img {
                width: 80px;
            }
        }
    </style>
</head>
<body>

<div class="logo-container">
    <a href="home.php">
        <img src="https://outdoorsecours.fr/wp-content/uploads/2023/07/thumbnail_image001-1.png" alt="Logo Outdoor Secours">
    </a>
</div>

<div class="container">
    <h1 class="text-center mt-5">Mes Applications</h1>
    <div class="app-container">

        <!-- Application 1: Dashboard -->
        <div class="app-icon" onclick="window.location.href='dashboard.php'">
            <i class="bi bi-calendar"></i>
            <div class="app-name">Mes événements</div>
        </div>

        <!-- Application 2: Future Application -->
        <div class="app-icon" onclick="window.location.href='#'">
            <i class="bi bi-app-indicator"></i>
            <div class="app-name">Future App</div>
        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
