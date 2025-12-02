<?php
require_once 'auth.php';
require_login();

$errors = [];
$success = '';

$stmt = $pdo->prepare("SELECT username, email, account_type, created_at FROM player WHERE player_id = ?");
$stmt->execute([current_user_id()]);
$player = $stmt->fetch();

if (!$player) exit("Player not found.");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $player['account_type'] === 'REGISTERED') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');

    if ($username === '' || $email === '') {
        $errors[] = "Username and email required.";
    } else {
        $upd = $pdo->prepare("UPDATE player SET username = ?, email = ? WHERE player_id = ?");
        try {
            $upd->execute([$username, $email, current_user_id()]);
            $_SESSION['username'] = $username;
            $success = "Profile updated.";
        } catch (PDOException $e) {
            $errors[] = "Update failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Settings</title></head>
<body>
<h1>Settings</h1>
<p><a href="home.php">Back to Home</a></p>
<?php foreach ($errors as $e): ?><p style="color:red;"><?php echo htmlspecialchars($e); ?></p><?php endforeach; ?>
<?php if ($success): ?><p style="color:green;"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

<form method="post">
    <p>Account type: <?php echo htmlspecialchars($player['account_type']); ?></p>
    <?php if ($player['account_type'] === 'REGISTERED'): ?>
        <label>Username <input name="username" value="<?php echo htmlspecialchars($player['username']); ?>"></label><br>
        <label>Email <input type="email" name="email" value="<?php echo htmlspecialchars($player['email']); ?>"></label><br>
        <button type="submit">Save Changes</button>
    <?php else: ?>
        <p>Guest accounts cannot change profile info.</p>
    <?php endif; ?>
</form>

</body>
</html>