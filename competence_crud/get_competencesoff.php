<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

$conn = new mysqli("localhost", "root", "", "apprecherche");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $iduser = $_GET['iduser'] ?? 0;
    
    try {
        // Récupère les compétences de l'utilisateur et celles liées à ses offres
        $stmt = $conn->prepare("
            SELECT c.idcompetence, c.intutile, c.niveau, o.reference as reference_offre
            FROM competence c
            LEFT JOIN offre o ON c.offre_id = o.idoffre
            WHERE c.utilisateur_id = ? OR o.recruteur_id = ?
            ORDER BY c.intutile
        ");
        $stmt->bind_param("ii", $iduser, $iduser);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $competences = $result->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode($competences);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) $stmt->close();
    }
}

$conn->close();
?>