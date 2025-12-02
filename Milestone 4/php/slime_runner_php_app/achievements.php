<?php
require_once 'auth.php';
require_login();

$tab = $_GET['tab'] ?? 'unlocked';

$unlockedStmt = $pdo->prepare("
    SELECT a.name, a.description, pa.unlocked_at, pa.session_id
    FROM achievement a
    JOIN player_achievement pa
      ON pa.achievement_id = a.achievement_id
    WHERE pa.player_id = ?
    ORDER BY pa.unlocked_at DESC
");
$unlockedStmt->execute([current_user_id()]);
$unlocked = $unlockedStmt->fetchAll();

$lockedStmt = $pdo->prepare("
    SELECT a.name, a.description
    FROM achievement a
    WHERE a.achievement_id NOT IN (
        SELECT achievement_id FROM player_achievement WHERE player_id = ?
    )
");
$lockedStmt->execute([current_user_id()]);
$locked = $lockedStmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head><title>Achievements</title></head>
<body>
<h1>Achievements</h1>
<p><a href="home.php">Back to Home</a></p>

<p>
    <a href="?tab=unlocked">Unlocked</a> |
    <a href="?tab=locked">Locked</a>
</p>

<?php if ($tab === 'unlocked'): ?>
<h2>Unlocked</h2>
<ul>
<?php foreach ($unlocked as $a): ?>
    <li>
        <strong><?php echo htmlspecialchars($a['name']); ?></strong> –
        <?php echo htmlspecialchars($a['description']); ?><br>
        Unlocked at: <?php echo htmlspecialchars($a['unlocked_at']); ?> –
        <a href="session_detail.php?id=<?php echo (int)$a['session_id']; ?>">View Session</a>
    </li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<h2>Locked</h2>
<ul>
<?php foreach ($locked as $a): ?>
    <li>
        <strong><?php echo htmlspecialchars($a['name']); ?></strong> –
        <?php echo htmlspecialchars($a['description']); ?>
    </li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

</body>
</html>