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
    $stmt = $conn->prepare("INSERT INTO document_requis (offre_id, intitule) VALUES (?, ?)");
    $stmt->bind_param("is",
        $data['idoffre'],
        $data['intitule']
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