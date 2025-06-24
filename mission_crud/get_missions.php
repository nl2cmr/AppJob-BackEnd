<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $conn->prepare("
            SELECT 
                m.idmission,
                m.titre,
                m.description,
                DATE_FORMAT(m.date_debut, '%Y-%m-%d') as date_debut,
                DATE_FORMAT(m.date_fin, '%Y-%m-%d') as date_fin,
                o.reference as reference_offre
            FROM 
                mission m
            JOIN 
                offre o ON m.offre_id = o.idoffre
            ORDER BY 
                m.date_debut DESC
        ");
        $stmt->execute();
        
        $result = $stmt->get_result();
        $missions = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode($missions);
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