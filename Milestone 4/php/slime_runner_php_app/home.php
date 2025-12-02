<?php
require_once 'auth.php';
require_login();

$seasonStmt = $pdo->query("SELECT * FROM season WHERE is_active = 1 LIMIT 1");
$activeSeason = $seasonStmt->fetch();

$lastStmt = $pdo->prepare("
    SELECT * FROM session
    WHERE player_id = ?
    ORDER BY started_at DESC
    LIMIT 1
");
$lastStmt->execute([current_user_id()]);
$last = $lastStmt->fetch();
?>
<!DOCTYPE html>
<html>
<head><title>Home</title></head>
<body>
<p>Welcome back, <?php echo htmlspecialchars(current_username()); ?>!</p>

<?php if ($activeSeason): ?>
<p>Active Season: <?php echo htmlspecialchars($activeSeason['name']); ?></p>
<?php endif; ?>

<nav>
    <a href="start_session.php">Start Run</a> |
    <a href="skins.php">Skins</a> |
    <a href="achievements.php">Achievements</a> |
    <a href="leaderboard.php">Leaderboard</a> |
    <a href="history.php">Run History</a> |
    <a href="settings.php">Settings</a> |
    <a href="logout.php">Logout</a>
</nav>

<h2>Last Run</h2>
<?php if ($last): ?>
<ul>
    <li>Score: <?php echo (int)$last['score']; ?></li>
    <li>Distance: <?php echo (int)$last['distance_m']; ?> m</li>
    <li>Top Speed: <?php echo htmlspecialchars($last['top_speed']); ?></li>
    <li>Crash Type: <?php echo htmlspecialchars($last['crash_type']); ?></li>
</ul>
<?php else: ?>
<p>No runs yet.</p>
<?php endif; ?>

</body>
</html>