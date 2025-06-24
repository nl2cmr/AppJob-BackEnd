<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("INSERT INTO offre (recruteur_id, titre, reference, description, date_publication, date_expiration, salaire, type_contrat) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("isssssss",
        $data['iduser'],
        $data['titre'],
        $data['reference'],
        $data['description'],
        $data['date_publication'],
        $data['date_expiration'],
        $data['salaire'],
        $data['type_contrat']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Experience added']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add experience']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
}

$conn->close();
?>