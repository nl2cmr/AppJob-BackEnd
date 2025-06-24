<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PUT");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validation
    if (empty($data['intitule'])) {
        echo json_encode(['success' => false, 'message' => "L'intitulé est requis"]);
        exit;
    }

    // Vérification des droits
    $check = $conn->prepare("SELECT idinteret FROM interet WHERE idinteret = ? AND utilisateur_id = ?");
    $check->bind_param("ii", $data['idinteret'], $data['user_id']);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE interet SET 
        intitule = ?,
        description = ?
        WHERE idinteret = ?");
    
    $stmt->bind_param("ssi",
        $data['intitule'],
        $data['description'],
        $data['idinteret']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Intérêt mis à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur de mise à jour']);
    }
    
    $stmt->close();
}

$conn->close();
?>