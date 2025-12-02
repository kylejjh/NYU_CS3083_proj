<?php
require_once 'auth.php';
require_login();

$tab = $_GET['tab'] ?? 'owned';

$ownedStmt = $pdo->prepare("
    SELECT s.skin_id, s.name, s.rarity, s.is_default, ps.acquired_at, ps.source
    FROM skin s
    JOIN player_skin ps ON ps.skin_id = s.skin_id
    WHERE ps.player_id = ?
");
$ownedStmt->execute([current_user_id()]);
$owned = $ownedStmt->fetchAll();

$lockedStmt = $pdo->prepare("
    SELECT s.skin_id, s.name, s.rarity
    FROM skin s
    WHERE s.skin_id NOT IN (
        SELECT skin_id FROM player_skin WHERE player_id = ?
    )
");
$lockedStmt->execute([current_user_id()]);
$locked = $lockedStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $skin_id = (int)($_POST['skin_id'] ?? 0);

    $checkStmt = $pdo->prepare("SELECT fn_player_owns_skin(?, ?, NOW()) AS owns");
    $checkStmt->execute([current_user_id(), $skin_id]);
    $row = $checkStmt->fetch();

    if ($row && $row['owns']) {
        $_SESSION['selected_skin_id'] = $skin_id;
        $_SESSION['flash'] = "Skin set for next run.";
    } else {
        $_SESSION['flash'] = "You do not own that skin.";
    }
    header('Location: skins.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>Skins</title></head>
<body>
<h1>Skins</h1>
<p><a href="home.php">Back to Home</a></p>

<?php if (!empty($_SESSION['flash'])): ?>
<p style="color:green;"><?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></p>
<?php endif; ?>

<p>Current selected skin ID: <?php echo $_SESSION['selected_skin_id'] ?? 'default'; ?></p>

<p>
    <a href="?tab=owned">Owned</a> |
    <a href="?tab=locked">Locked</a>
</p>

<?php if ($tab === 'owned'): ?>
<h2>Owned Skins</h2>
<ul>
<?php foreach ($owned as $s): ?>
    <li>
        <?php echo htmlspecialchars($s['name'] . " ({$s['rarity']})"); ?>
        <form method="post" style="display:inline;">
            <input type="hidden" name="skin_id" value="<?php echo (int)$s['skin_id']; ?>">
            <button type="submit">Use</button>
        </form>
    </li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<h2>Locked Skins</h2>
<ul>
<?php foreach ($locked as $s): ?>
    <li><?php echo htmlspecialchars($s['name'] . " ({$s['rarity']}) - Locked"); ?></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>

</body>
</html>