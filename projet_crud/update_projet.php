<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idprojet = $data['idprojet'] ?? 0;
    $user_id = $data['user_id'] ?? 0;

    // Vérification des droits
    $check = $conn->prepare("SELECT idprojet FROM projet WHERE idprojet = ? AND utilisateur_id = ?");
    $check->bind_param("ii", $idprojet, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE projet SET titre = ?, description = ?, date_debut = ?, date_fin = ?, lien = ? WHERE idprojet = ?");
    $stmt->bind_param("sssssi", 
        $data['titre'],
        $data['description'],
        $data['date_debut'],
        $data['date_fin'],
        $data['lien'],
        $idprojet
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Projet mis à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour']);
    }
    
    $stmt->close();
}

$conn->close();
?>