<?php
class Job {
    private $conn;
    private $allowedFields = [
        'id',
        'title',
        'description',
        'company',
        'location',
        'job_type',
        'is_remote',
        'created_at',
        'updated_at'
    ];

    private $sortableFields = [
        'id',
        'title',
        'company',
        'location',
        'job_type',
        'is_remote',
        'created_at',
        'updated_at'
    ];

    public function __construct($connection) {
        $this->conn = $connection;
    }

    public function getAllJobs($sort = 'DESC', $sortBy = 'created_at') {
        // Valider sortBy feltet mod listen over felter man må sortere efter
        if (!in_array($sortBy, $this->sortableFields)) {
            $sortBy = 'created_at';
        }

        // Valider sort retning
        $sort = (strtoupper($sort) === 'ASC') ? 'ASC' : 'DESC';

        $fields = implode(", ", $this->allowedFields);
        $sql = "SELECT $fields FROM job_postings ORDER BY $sortBy $sort";
        $result = $this->conn->query($sql);

        if ($result === false) {
            return null;
        }

        $jobs = [];
        while ($row = $result->fetch_assoc()) {
            // Konverter is_remote fra 0/1 til boolean for bedre JSON-repræsentation
            $row['is_remote'] = (bool)$row['is_remote'];
            $jobs[] = $row;
        }

        return $jobs;
    }

    public function getFilteredJobs($search = null, $location = null, $jobType = null, $remote = null, $sort = 'DESC', $page = null, $limit = null, $sortBy = 'created_at') {
        $whereClauses = [];
        $params = [];
        $types = "";

        // Valider sortBy feltet mod listen over felter man må sortere efter
        if (!in_array($sortBy, $this->sortableFields)) {
            $sortBy = 'created_at';
        }

        // Valider sort retning
        $sort = (strtoupper($sort) === 'ASC') ? 'ASC' : 'DESC';

        if ($search) {
            $whereClauses[] = "(title LIKE ? OR description LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }

        if ($location) {
            $whereClauses[] = "location = ?";
            $params[] = $location;
            $types .= "s";
        }

        if ($jobType) {
            $whereClauses[] = "job_type = ?";
            $params[] = $jobType;
            $types .= "s";
        }

        if ($remote !== null) {
            $whereClauses[] = "is_remote = ?";
            $params[] = (int)$remote;
            $types .= "i";
        }

        $whereSql = "";
        if (count($whereClauses) > 0) {
            $whereSql = " WHERE " . implode(" AND ", $whereClauses);
        }

        $fields = implode(", ", $this->allowedFields);
        $dataSql = "SELECT $fields FROM job_postings" . $whereSql . " ORDER BY $sortBy $sort";

        if ($limit !== null && $page !== null) {
            $countSql = "SELECT COUNT(*) as total FROM job_postings" . $whereSql;
            $stmtCount = $this->conn->prepare($countSql);
            if (count($params) > 0) {
                $stmtCount->bind_param($types, ...$params);
            }
            $stmtCount->execute();
            $totalItems = (int)$stmtCount->get_result()->fetch_assoc()['total'];

            $offset = ($page - 1) * $limit;
            $dataSql .= " LIMIT ? OFFSET ?";
            $dataTypes = $types . "ii";
            $dataParams = array_merge($params, [$limit, $offset]);
            
            $stmtData = $this->conn->prepare($dataSql);
            $stmtData->bind_param($dataTypes, ...$dataParams);
        } else {
            $stmtData = $this->conn->prepare($dataSql);
            if (count($params) > 0) {
                $stmtData->bind_param($types, ...$params);
            }
        }

        $stmtData->execute();
        $result = $stmtData->get_result();

        $jobs = [];
        while ($row = $result->fetch_assoc()) {
            $row['is_remote'] = (bool)$row['is_remote'];
            $jobs[] = $row;
        }

        if ($limit === null) {
            $totalItems = count($jobs);
        }

        return [
            'jobs' => $jobs,
            'total' => $totalItems,
            'count' => count($jobs),
            'page' => $page,
            'limit' => $limit
        ];
    }
}
?>