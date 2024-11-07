<?php
$bagId = $_GET['bag_id'];
$actionType = $_GET['action'];

$conn = new PDO("mysql:host=localhost;dbname=outdoorsec", "root", "Lipton2019!");
$stmt = $conn->prepare("INSERT INTO bag_inventories (bag_id, action_type) VALUES (:bag_id, :action_type)");
$stmt->bindParam(':bag_id', $bagId);
$stmt->bindParam(':action_type', $actionType);
$stmt->execute();

header("Location: bag_tracking.php?bag_id=$bagId");
exit();
