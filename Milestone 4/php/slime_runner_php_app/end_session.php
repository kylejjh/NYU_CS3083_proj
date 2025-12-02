<?php
require_once 'auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php'); exit;
}

$session_id       = (int)($_POST['session_id'] ?? 0);
$score            = (int)($_POST['score'] ?? 0);
$distance_m       = (int)($_POST['distance_m'] ?? 0);
$top_speed        = (float)($_POST['top_speed'] ?? 0);
$crash_type       = $_POST['crash_type'] ?? 'QUIT';
$obstacle_type_id = $_POST['obstacle_type_id'] !== '' ? (int)$_POST['obstacle_type_id'] : null;

$chk = $pdo->prepare("SELECT * FROM session WHERE session_id = ? AND player_id = ?");
$chk->execute([$session_id, current_user_id()]);
if (!$chk->fetch()) {
    exit("Invalid session.");
}

if ($crash_type === 'COLLIDE' && !$obstacle_type_id) {
    $row = $pdo->query("SELECT obstacle_type_id FROM obstacle_type ORDER BY obstacle_type_id LIMIT 1")->fetch();
    $obstacle_type_id = $row['obstacle_type_id'] ?? null;
}

$upd = $pdo->prepare("
    UPDATE session
       SET ended_at        = NOW(),
           score           = ?,
           distance_m      = ?,
           top_speed       = ?,
           crash_type      = ?,
           obstacle_type_id = ?
     WHERE session_id      = ?
");
$upd->execute([$score, $distance_m, $top_speed, $crash_type, $obstacle_type_id, $session_id]);

$pdo->prepare("
    INSERT INTO obstacle_spawn (session_id, obstacle_type_id, t_offset_ms, speed_at_spawn, cleared)
    VALUES (?, ?, 1000, ?, ?)
")->execute([
    $session_id,
    $obstacle_type_id ?? 1,
    max(10.0, $top_speed - 2),
    $crash_type === 'COLLIDE' ? 0 : 1
]);

$pdo->prepare("
    INSERT INTO input_event (session_id, t_offset_ms, action, source)
    VALUES (?, 900, 'JUMP', 'KEYBOARD')
")->execute([$session_id]);

$proc = $pdo->prepare("CALL sp_validate_session_crash(?)");
$proc->execute([$session_id]);

if ($score >= 3000) {
    $a = $pdo->prepare("SELECT achievement_id FROM achievement WHERE name = 'Marathon Runner' LIMIT 1");
    $a->execute();
    if ($aid = $a->fetchColumn()) {
        $ins = $pdo->prepare("
            INSERT IGNORE INTO player_achievement (player_id, achievement_id, session_id, unlocked_at)
            VALUES (?, ?, ?, NOW())
        ");
        $ins->execute([current_user_id(), $aid, $session_id]);
    }
}

$_SESSION['flash'] = "Run saved! Session #$session_id.";
header("Location: session_detail.php?id=" . $session_id);
exit;
?>