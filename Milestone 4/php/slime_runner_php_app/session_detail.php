<?php
require_once 'auth.php';
require_login();

$session_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT s.*, sk.name AS skin_name
    FROM session s
    LEFT JOIN skin sk ON sk.skin_id = s.skin_id
    WHERE s.session_id = ? AND s.player_id = ?
");
$stmt->execute([$session_id, current_user_id()]);
$session = $stmt->fetch();

if (!$session) {
    exit("Session not found or access denied.");
}

$obs = $pdo->prepare("
    SELECT os.t_offset_ms, os.speed_at_spawn, os.cleared,
           ot.name AS obstacle_name, ot.altitude
    FROM obstacle_spawn os
    JOIN obstacle_type ot ON ot.obstacle_type_id = os.obstacle_type_id
    WHERE os.session_id = ?
");
$obs->execute([$session_id]);
$obstacles = $obs->fetchAll();

$ie = $pdo->prepare("
    SELECT t_offset_ms, action, source
    FROM input_event
    WHERE session_id = ?
");
$ie->execute([$session_id]);
$inputs = $ie->fetchAll();

$events = [];
foreach ($obstacles as $o) {
    $events[] = [
        't' => (int)$o['t_offset_ms'],
        'text' => sprintf(\"Obstacle %s (%s) speed %.2f, cleared=%s\",
            $o['obstacle_name'], $o['altitude'], $o['speed_at_spawn'],
            $o['cleared'] ? 'yes' : 'no')
    ];
}
foreach ($inputs as $i) {
    $events[] = [
        't' => (int)$i['t_offset_ms'],
        'text' => \"Input: {$i['action']} via {$i['source']}\"
    ];
}
usort($events, fn($a, $b) => $a['t'] <=> $b['t']);
?>
<!DOCTYPE html>
<html>
<head><title>Session Detail</title></head>
<body>
<h1>Session #<?php echo (int)$session_id; ?></h1>
<p><a href="history.php">Back to History</a></p>

<ul>
    <li>Score: <?php echo (int)$session['score']; ?></li>
    <li>Distance: <?php echo (int)$session['distance_m']; ?> m</li>
    <li>Top Speed: <?php echo htmlspecialchars($session['top_speed']); ?></li>
    <li>Crash Type: <?php echo htmlspecialchars($session['crash_type']); ?></li>
    <li>Skin: <?php echo htmlspecialchars($session['skin_name'] ?? 'None'); ?></li>
    <li>Device: <?php echo htmlspecialchars($session['device_type']); ?></li>
    <li>Start: <?php echo htmlspecialchars($session['started_at']); ?></li>
    <li>End: <?php echo htmlspecialchars($session['ended_at']); ?></li>
</ul>

<h2>Timeline</h2>
<ol>
    <li>0 ms – START</li>
    <?php foreach ($events as $e): ?>
        <li><?php echo (int)$e['t']; ?> ms – <?php echo htmlspecialchars($e['text']); ?></li>
    <?php endforeach; ?>
    <li><?php echo (int)$session['duration_ms']; ?> ms – END (<?php echo htmlspecialchars($session['crash_type']); ?>)</li>
</ol>

</body>
</html>