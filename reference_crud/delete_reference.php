<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $idreference = $data['idreference'] ?? 0;
    $user_id = $data['user_id'] ?? 0;
    
    // Vérifier que la référence appartient bien à l'utilisateur
    $check = $conn->prepare("SELECT idreference FROM reference WHERE idreference = ? AND utilisateur_id = ?");
    $check->bind_param("ii", $idreference, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Non autorisé']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM reference WHERE idreference = ?");
    $stmt->bind_param("i", $idreference);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Référence supprimée']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de suppression']);
    }
    
    $stmt->close();
}

$conn->close();
?>