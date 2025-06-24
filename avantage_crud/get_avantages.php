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
                a.idavantage,
                a.description,
                o.reference as reference_offre,
                o.idoffre
            FROM 
                avantage a
            JOIN 
                offre o ON a.offre_id = o.idoffre
            ORDER BY 
                o.reference ASC, a.idavantage DESC
        ");
        $stmt->execute();
        
        $result = $stmt->get_result();
        $avantages = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode($avantages);
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