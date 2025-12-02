<?php
require_once 'auth.php';
require_login();

$active = $pdo->query("SELECT * FROM season WHERE is_active = 1 LIMIT 1")->fetch();
if (!$active) {
    exit("No active season configured.");
}
$season_id = $active['season_id'];

$skin_id = $_SESSION['selected_skin_id'] ?? null;
if ($skin_id) {
    $check = $pdo->prepare("SELECT fn_player_owns_skin(?, ?, NOW()) AS owns");
    $check->execute([current_user_id(), $skin_id]);
    $row = $check->fetch();
    if (!$row || !$row['owns']) {
        $skin_id = null;
    }
}
if (!$skin_id) {
    $default = $pdo->query("SELECT skin_id FROM skin WHERE is_default = 1 LIMIT 1")->fetch();
    $skin_id = $default['skin_id'] ?? null;
}

$seed = random_int(1000, 999999);
$ins = $pdo->prepare("
    INSERT INTO session (player_id, season_id, skin_id, obstacle_type_id,
                         started_at, ended_at, score, distance_m, top_speed,
                         crash_type, is_offline, device_type, seed)
    VALUES (?, ?, ?, NULL, NOW(), NULL, 0, 0, 0, NULL, 0, 'browser', ?)
");
$ins->execute([current_user_id(), $season_id, $skin_id, $seed]);
$session_id = $pdo->lastInsertId();

$_SESSION['current_session_id'] = $session_id;
?>
<!DOCTYPE html>
<html>
<head><title>Simulated Run</title></head>
<body>
<h1>New Run Started</h1>
<p>Session ID: <?php echo (int)$session_id; ?> | Season: <?php echo htmlspecialchars($active['name']); ?></p>
<p>(For this DB project we use a simple form instead of the full canvas game.)</p>

<form method="post" action="end_session.php">
    <input type="hidden" name="session_id" value="<?php echo (int)$session_id; ?>">
    <label>Score: <input type="number" name="score" value="2000"></label><br>
    <label>Distance (m): <input type="number" name="distance_m" value="600"></label><br>
    <label>Top Speed: <input type="number" step="0.1" name="top_speed" value="20.5"></label><br>
    <label>Crash Type:
        <select name="crash_type">
            <option value="QUIT">QUIT</option>
            <option value="TIMEOUT">TIMEOUT</option>
            <option value="COLLIDE">COLLIDE</option>
        </select>
    </label><br>
    <label>If COLLIDE, obstacle type:
        <select name="obstacle_type_id">
            <option value="">(auto or none)</option>
            <?php
            $ots = $pdo->query("SELECT obstacle_type_id, name FROM obstacle_type")->fetchAll();
            foreach ($ots as $ot) {
                echo '<option value="'.(int)$ot['obstacle_type_id'].'">' .
                     htmlspecialchars($ot['name']) . '</option>';
            }
            ?>
        </select>
    </label><br>
    <button type="submit">Finish Run</button>
</form>

</body>
</html>