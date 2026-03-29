<?php
/**
 * Admin - Export CSV des leads
 */
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Accès non autorisé');
}

// Paramètres de filtrage
$filter_status = $_GET['status'] ?? '';
$filter_date_from = $_GET['date_from'] ?? '';
$filter_date_to = $_GET['date_to'] ?? '';

// Construction de la requête
$where = [];
$params = [];

if ($filter_status === 'new') {
    $where[] = 'is_treated = 0';
} elseif ($filter_status === 'treated') {
    $where[] = 'is_treated = 1';
}

if ($filter_date_from) {
    $where[] = 'created_at >= ?';
    $params[] = $filter_date_from . ' 00:00:00';
}

if ($filter_date_to) {
    $where[] = 'created_at <= ?';
    $params[] = $filter_date_to . ' 23:59:59';
}

$where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Récupérer les leads
$sql = "SELECT l.*, m.nom as modele_nom 
        FROM leads l 
        LEFT JOIN modeles m ON l.modele_interesse = m.id 
        $where_clause 
        ORDER BY l.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$leads = $stmt->fetchAll();

// Nom du fichier
$filename = 'leads_orca_' . date('Y-m-d_H-i-s') . '.csv';

// Headers pour le téléchargement
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Ouvrir le flux de sortie
$output = fopen('php://output', 'w');

// BOM UTF-8 pour Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// En-têtes des colonnes
$headers = [
    'ID',
    'Date',
    'Type',
    'Civilité',
    'Nom',
    'Prénom',
    'Email',
    'Téléphone',
    'Code Postal',
    'Ville',
    'Département',
    'Modèle intéressé',
    'Terrain prévu',
    'Délai souhaité',
    'Commentaire',
    'Statut',
    'Page source'
];

fputcsv($output, $headers, ';');

// Données
foreach ($leads as $lead) {
    $row = [
        $lead['id'],
        date('d/m/Y H:i', strtotime($lead['created_at'])),
        $lead['type_demande'],
        $lead['civilite'],
        $lead['nom'],
        $lead['prenom'],
        $lead['email'],
        $lead['telephone'],
        $lead['code_postal'],
        $lead['ville'],
        $lead['departement'],
        $lead['modele_nom'] ?? 'Non spécifié',
        $lead['terrain_prevu'] ? 'Oui' : 'Non',
        $lead['delai_souhaite'],
        str_replace(["\r\n", "\n", "\r"], ' ', $lead['commentaire']),
        $lead['is_treated'] ? 'Traité' : 'Nouveau',
        $lead['page_source']
    ];
    
    fputcsv($output, $row, ';');
}

fclose($output);
exit;
