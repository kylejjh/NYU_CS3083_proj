<?php
require_once 'config.php';

$randName = 'guest' . rand(1000, 9999);

$insert = $pdo->prepare("
    INSERT INTO player (username, email, password, account_type, created_at)
    VALUES (?, NULL, NULL, 'GUEST', NOW())
");
$insert->execute([$randName]);
$player_id = $pdo->lastInsertId();

$defaultSkin = $pdo->query("SELECT skin_id FROM skin WHERE is_default = 1 LIMIT 1")->fetch();
if ($defaultSkin) {
    $ps = $pdo->prepare("INSERT INTO player_skin (player_id, skin_id, acquired_at, source)
                         VALUES (?, ?, NOW(), 'DEFAULT')");
    $ps->execute([$player_id, $defaultSkin['skin_id']]);
}

$_SESSION['player_id'] = $player_id;
$_SESSION['username']  = $randName;

header('Location: home.php');
exit;
?>