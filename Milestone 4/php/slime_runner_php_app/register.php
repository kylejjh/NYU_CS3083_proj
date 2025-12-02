<?php
require_once 'config.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $email === '' || $password === '') {
        $errors[] = "All fields are required.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT 1 FROM player WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email already in use.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare("
                INSERT INTO player (username, email, password, account_type, created_at)
                VALUES (?, ?, ?, 'REGISTERED', NOW())
            ");
            $insert->execute([$username, $email, $hash]);
            $player_id = $pdo->lastInsertId();

            $defaultSkin = $pdo->query("SELECT skin_id FROM skin WHERE is_default = 1 LIMIT 1")->fetch();
            if ($defaultSkin) {
                $ps = $pdo->prepare("INSERT INTO player_skin (player_id, skin_id, acquired_at, source)
                                     VALUES (?, ?, NOW(), 'DEFAULT')");
                $ps->execute([$player_id, $defaultSkin['skin_id']]);
            }

            $_SESSION['player_id'] = $player_id;
            $_SESSION['username']  = $username;
            header('Location: home.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Register</title></head>
<body>
<h1>Create Account</h1>
<?php foreach ($errors as $e): ?>
<p style="color:red;"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Username <input name="username" /></label><br>
    <label>Email <input type="email" name="email" /></label><br>
    <label>Password <input type="password" name="password" /></label><br>
    <button type="submit">Create Account</button>
</form>

<p><a href="login.php">Back to Sign In</a></p>
</body>
</html>