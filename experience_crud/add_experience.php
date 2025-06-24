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
    $required = ['iduser', 'poste', 'entreprise', 'date_debut', 'date_fin'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(['success' => false, 'message' => "$field is required"]);
            exit;
        }
    }

    $stmt = $conn->prepare("INSERT INTO experience (utilisateur_id, poste, entreprise, date_debut, date_fin, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", 
        $data['iduser'],
        $data['poste'],
        $data['entreprise'],
        $data['date_debut'],
        $data['date_fin'],
        $data['description']
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