<?php
require_once 'auth.php';
require_login();

$season_id  = isset($_GET['season_id']) ? (int)$_GET['season_id'] : null;
$crash_type = $_GET['crash_type'] ?? '';
$date_from  = $_GET['date_from'] ?? '';
$date_to    = $_GET['date_to'] ?? '';

$where = ["player_id = :pid"];
$params = ['pid' => current_user_id()];

if ($season_id) {
    $where[] = "season_id = :sid";
    $params['sid'] = $season_id;
}
if ($crash_type !== '') {
    $where[] = "crash_type = :ct";
    $params['ct'] = $crash_type;
}
if ($date_from !== '') {
    $where[] = "started_at >= :df";
    $params['df'] = $date_from . " 00:00:00";
}
if ($date_to !== '') {
    $where[] = "started_at <= :dt";
    $params['dt'] = $date_to . " 23:59:59";
}

$sql = "SELECT session_id, season_id, score, distance_m, top_speed, crash_type, started_at, ended_at
        FROM session
        WHERE " . implode(' AND ', $where) . "
        ORDER BY started_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=\"run_history.csv\"');

$out = fopen('php://output', 'w');
fputcsv($out, ['session_id','season_id','score','distance_m','top_speed','crash_type','started_at','ended_at']);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($out, $row);
}
fclose($out);
exit;
?>