<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: UPDATE");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $conn->prepare("UPDATE experience SET 
        poste = ?,
        entreprise = ?,
        date_debut = ?,
        date_fin = ?,
        description = ?
        WHERE idexperience = ?");
    
    $stmt->bind_param("sssssi",
        $data['poste'],
        $data['entreprise'],
        $data['date_debut'],
        $data['date_fin'],
        $data['description'],
        $data['idexperience']
    );
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Experience updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
    
    $stmt->close();
}

$conn->close();
?>