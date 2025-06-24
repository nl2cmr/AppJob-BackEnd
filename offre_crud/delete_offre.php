<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idoffre = $data['idoffre'] ?? 0;
    $user_id = $data['user_id'] ?? 0;
    
    // Vérifier que l'offre appartient bien à l'utilisateur
    $check = $conn->prepare("SELECT idoffre FROM offre WHERE idoffre = ? AND recruteur_id = ?");
    $check->bind_param("ii", $idoffre, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM offre WHERE idoffre = ?");
    $stmt->bind_param("i", $idoffre);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Offre supprimée']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de suppression: ' . $conn->error]);
    }
    
    $stmt->close();
}

$conn->close();
?>