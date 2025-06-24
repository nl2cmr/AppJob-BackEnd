<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database connection configuration
$host = "localhost";
$db_name = "apprecherche";
$username = "root";
$password = "";

try {
    // Create database connection using PDO
    $db = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Include the Offre class
    include_once './offres.php';
    
    // Create Offre object
    $offre = new Offre($db);

    // Récupération des filtres
    $typeContrat = isset($_GET['type_contrat']) ? $_GET['type_contrat'] : null;
    $salaireMin = isset($_GET['salaire_min']) ? $_GET['salaire_min'] : null;
    $lieu = isset($_GET['lieu']) ? $_GET['lieu'] : null;

    // Récupération des offres avec filtres
    $stmt = $offre->readFull($typeContrat, $salaireMin, $lieu);
    $num = $stmt->rowCount();

    if ($num > 0) {
        $offres_arr = array();
        $offres_arr["success"] = true;
        $offres_arr["data"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $offre_item = array(
                "idoffre" => $row['idoffre'],
                "titre" => $row['titre'],
                "reference" => $row['reference'],
                "description" => $row['description'],
                "date_publication" => $row['date_publication'],
                "date_expiration" => $row['date_expiration'],
                "salaire" => $row['salaire'],
                "type_contrat" => $row['type_contrat'],
                "recruteur_id" => $row['recruteur_id'],
                "recruteur_nom" => $row['recruteur_nom'],
                "missions" => array(),
                "avantages" => array(),
                "document_requis" => array(),
                "competences" => array(),
                "diplomes" => array(),
                "qualites" => array(),
                "langues" => array()
            );

            // Récupération des diplômes
            $stmt_diplomes = $offre->getDiplomes($row['idoffre']);
            while ($diplome = $stmt_diplomes->fetch(PDO::FETCH_ASSOC)) {
                $offre_item["diplomes"][] = $diplome;
            }

            // Récupération des qualités
            $stmt_qualites = $offre->getQualites($row['idoffre']);
            while ($qualite = $stmt_qualites->fetch(PDO::FETCH_ASSOC)) {
                $offre_item["qualites"][] = $qualite;
            }

            // Récupération des langues
            $stmt_langues = $offre->getLangues($row['idoffre']);
            while ($langue = $stmt_langues->fetch(PDO::FETCH_ASSOC)) {
                $offre_item["langues"][] = $langue;
            }

            // Récupération des missions
            $stmt_missions = $offre->getMissions($row['idoffre']);
            while ($mission = $stmt_missions->fetch(PDO::FETCH_ASSOC)) {
                $offre_item["missions"][] = $mission;
            }

            // Récupération des avantages
            $stmt_avantages = $offre->getAvantages($row['idoffre']);
            while ($avantage = $stmt_avantages->fetch(PDO::FETCH_ASSOC)) {
                $offre_item["avantages"][] = $avantage;
            }

            // Récupération des documents requis
            $stmt_docs = $offre->getDocumentsRequis($row['idoffre']);
            while ($doc = $stmt_docs->fetch(PDO::FETCH_ASSOC)) {
                $offre_item["document_requis"][] = $doc;
            }

            // Récupération des compétences requises
            $stmt_competences = $offre->getCompetences($row['idoffre']);
            while ($competence = $stmt_competences->fetch(PDO::FETCH_ASSOC)) {
                $offre_item["competences"][] = $competence;
            }

            array_push($offres_arr["data"], $offre_item);
        }

        http_response_code(200);
        echo json_encode($offres_arr);
    } else {
        http_response_code(404);
        echo json_encode(array(
            "success" => true,
            "data" => array(),
            "message" => "Aucune offre trouvée."
        ));
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Erreur de base de données: " . $e->getMessage()
    ));
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(array(
        "success" => false,
        "message" => $e->getMessage()
    ));
}
?>