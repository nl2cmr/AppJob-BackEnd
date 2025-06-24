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

    if (empty($data['iduser']) || empty($data['titre']) || empty($data['date_obtention'])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    $stmt = $conn->prepare("INSERT INTO diplome (utilisateur_id, offre_id, titre, date_obtention) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss",
        $data['iduser'],
        $data['idoffre'],
        $data['titre'],
        $data['date_obtention']
    );


if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Diplome added']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add diplome']);
}

$stmt->close();
} else {
echo json_encode(['success' => false, 'message' => 'Invalid method']);
}

$conn->close();
?>