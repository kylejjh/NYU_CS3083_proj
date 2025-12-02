<?php
require_once 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT player_id, username, password FROM player
                           WHERE email = ? AND account_type = 'REGISTERED'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['player_id'] = $user['player_id'];
        $_SESSION['username']  = $user['username'];

        $upd = $pdo->prepare("UPDATE player SET last_login_at = NOW() WHERE player_id = ?");
        $upd->execute([$user['player_id']]);

        header('Location: home.php');
        exit;
    } else {
        $errors[] = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Sign In</title></head>
<body>
<h1>Sign In</h1>
<?php foreach ($errors as $e): ?>
<p style="color:red;"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Email <input type="email" name="email"></label><br>
    <label>Password <input type="password" name="password"></label><br>
    <button type="submit">Sign In</button>
</form>

<p><a href="guest.php">Continue as Guest</a></p>
<p>Don't have an account? <a href="register.php">Register</a></p>
</body>
</html>