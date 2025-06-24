<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idformation = $data['idformation'] ?? 0;
    $user_id = $data['user_id'] ?? 0;
    
    // Vérification des droits
    $check = $conn->prepare("SELECT idformation FROM formation WHERE idformation = ? AND utilisateur_id = ?");
    $check->bind_param("ii", $idformation, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM formation WHERE idformation = ?");
    $stmt->bind_param("i", $idformation);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Formation supprimée']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de suppression']);
    }
    
    $stmt->close();
}

$conn->close();
?>