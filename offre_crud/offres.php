<?php
class Offre {
    private $conn;
    private $table_name = "offre";

    public $idoffre;
    public $titre;
    public $reference;
    public $description;
    public $date_publication;
    public $date_expiration;
    public $salaire;
    public $type_contrat;
    public $recruteur_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readFull($typeContrat = null, $salaireMin = null, $lieu = null) {
        $query = "SELECT o.*, u.nom as recruteur_nom 
                  FROM " . $this->table_name . " o
                  JOIN utilisateur u ON o.recruteur_id = u.iduser
                  WHERE 1=1";

        if ($typeContrat) {
            $query .= " AND o.type_contrat = :type_contrat";
        }
        if ($salaireMin) {
            $query .= " AND o.salaire >= :salaire_min";
        }
        if ($lieu) {
            $query .= " AND o.adresse LIKE :lieu";
        }

        $query .= " ORDER BY o.date_publication DESC";

        $stmt = $this->conn->prepare($query);

        if ($typeContrat) {
            $stmt->bindParam(':type_contrat', $typeContrat);
        }
        if ($salaireMin) {
            $stmt->bindParam(':salaire_min', $salaireMin);
        }
        if ($lieu) {
            $lieu = "%$lieu%";
            $stmt->bindParam(':lieu', $lieu);
        }

        $stmt->execute();
        return $stmt;
    }

    public function getDiplomes($offre_id) {
        $query = "SELECT * FROM diplome WHERE offre_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offre_id);
        $stmt->execute();
        return $stmt;
    }

    public function getQualites($offre_id) {
        $query = "SELECT * FROM qualite WHERE offre_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offre_id);
        $stmt->execute();
        return $stmt;
    }

    public function getLangues($offre_id) {
        $query = "SELECT * FROM langue WHERE offre_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offre_id);
        $stmt->execute();
        return $stmt;
    }

    public function getMissions($offre_id) {
        $query = "SELECT * FROM mission WHERE offre_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offre_id);
        $stmt->execute();
        return $stmt;
    }

    public function getAvantages($offre_id) {
        $query = "SELECT * FROM avantage WHERE offre_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offre_id);
        $stmt->execute();
        return $stmt;
    }

    public function getDocumentsRequis($offre_id) {
        $query = "SELECT * FROM document_requis WHERE offre_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offre_id);
        $stmt->execute();
        return $stmt;
    }

    public function getCompetences($offre_id) {
        $query = "SELECT * FROM competence WHERE offre_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offre_id);
        $stmt->execute();
        return $stmt;
    }
}
?>