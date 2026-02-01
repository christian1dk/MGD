<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
require_once 'models/Job.php';

$conn = getDbConnection();

// Hent parametre fra URL
$search   = isset($_GET['search']) ? $_GET['search'] : null;
$location = isset($_GET['location']) ? $_GET['location'] : null;
$jobType  = isset($_GET['job_type']) ? $_GET['job_type'] : null;

// Håndter remote så den accepterer 'true', 'false', '1', '0'
$remote = null;
if (isset($_GET['remote']) && $_GET['remote'] !== '') {
    $remote = filter_var($_GET['remote'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
}

$sort   = (isset($_GET['sort']) && $_GET['sort'] === 'asc') ? 'ASC' : 'DESC';
$sortBy = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'created_at';
$page   = isset($_GET['page']) ? (int)$_GET['page'] : null;
$limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : null;

// Brug Job-model til at hente jobs
$jobModel = new Job($conn);

// Hvis ingen filtre (undtagen sortering), brug getAllJobs for simplicity
if (!$search && !$location && !$jobType && $remote === null && $page === null && $limit === null) {
    $jobs = $jobModel->getAllJobs($sort, $sortBy);
    $response = [
        'status' => 'success',
        'meta' => [
            'total_items' => count($jobs),
            'count' => count($jobs)
        ],
        'data' => $jobs
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $conn->close();
    exit;
}

// Ellers brug getFilteredJobs med filtre
$result = $jobModel->getFilteredJobs($search, $location, $jobType, $remote, $sort, $page, $limit, $sortBy);
$response = [
    'status' => 'success',
    'meta' => [
        'total_items' => $result['total'],
        'count' => $result['count']
    ],
    'data' => $result['jobs']
];

if ($limit !== null && $page !== null) {
    $response['meta']['current_page'] = $page;
    $response['meta']['total_pages'] = ceil($result['total'] / $limit);
    $response['meta']['limit'] = $limit;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
$conn->close();
?>