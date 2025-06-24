<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $utilisateur_id = $_GET['utilisateur_id'] ?? 0;
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                idcertification,
                intitule,
                DATE_FORMAT(date_obtention, '%Y-%m-%d') as date_obtention
            FROM 
                certification
            WHERE 
                utilisateur_id = ?
            ORDER BY 
                date_obtention DESC
        ");
        $stmt->bind_param("i", $utilisateur_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $certifications = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode($certifications);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    } finally {
        if (isset($stmt)) $stmt->close();
    }
}

$conn->close();
?>