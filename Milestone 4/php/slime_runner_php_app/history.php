<?php
require_once 'auth.php';
require_login();

$season_id  = isset($_GET['season_id']) ? (int)$_GET['season_id'] : null;
$crash_type = $_GET['crash_type'] ?? '';
$date_from  = $_GET['date_from'] ?? '';
$date_to    = $_GET['date_to'] ?? '';

$seasonList = $pdo->query("SELECT season_id, name FROM season ORDER BY start_date")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];

    $del = $pdo->prepare("DELETE FROM session WHERE session_id = ? AND player_id = ?");
    try {
        $del->execute([$delete_id, current_user_id()]);
        $_SESSION['flash'] = "Attempted to delete session #$delete_id (may fail if achievements reference it).";
    } catch (PDOException $e) {
        $_SESSION['flash'] = "Delete failed: " . $e->getMessage();
    }
    header('Location: history.php');
    exit;
}

$where = ["player_id = :pid"];
$params = ['pid' => current_user_id()];

if ($season_id) {
    $where[] = "season_id = :sid";
    $params['sid'] = $season_id;
}
if ($crash_type !== '') {
    $where[] = "crash_type = :ct";
    $params['ct'] = $crash_type;
}
if ($date_from !== '') {
    $where[] = "started_at >= :df";
    $params['df'] = $date_from . " 00:00:00";
}
if ($date_to !== '') {
    $where[] = "started_at <= :dt";
    $params['dt'] = $date_to . " 23:59:59";
}

$sql = "SELECT session_id, season_id, score, distance_m, top_speed, crash_type, started_at, ended_at
        FROM session
        WHERE " . implode(' AND ', $where) . "
        ORDER BY started_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sessions = $stmt->fetchAll();

$totalSessions = count($sessions);
$avgScore = $totalSessions ? array_sum(array_column($sessions, 'score')) / $totalSessions : 0;
$longestDistance = $totalSessions ? max(array_column($sessions, 'distance_m')) : 0;
?>
<!DOCTYPE html>
<html>
<head><title>Run History</title></head>
<body>
<h1>Run History</h1>
<p><a href="home.php">Back to Home</a></p>

<?php if (!empty($_SESSION['flash'])): ?>
<p style="color:blue;"><?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></p>
<?php endif; ?>

<form method="get">
    <label>Season:
        <select name="season_id">
            <option value="">All</option>
            <?php foreach ($seasonList as $s): ?>
                <option value="<?php echo (int)$s['season_id']; ?>"
                    <?php if ($season_id == $s['season_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($s['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Crash Type:
        <select name="crash_type">
            <option value="">All</option>
            <option value="COLLIDE" <?php if ($crash_type==='COLLIDE') echo 'selected'; ?>>COLLIDE</option>
            <option value="QUIT" <?php if ($crash_type==='QUIT') echo 'selected'; ?>>QUIT</option>
            <option value="TIMEOUT" <?php if ($crash_type==='TIMEOUT') echo 'selected'; ?>>TIMEOUT</option>
        </select>
    </label>
    <label>From: <input type="date" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>"></label>
    <label>To: <input type="date" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>"></label>
    <button type="submit">Filter</button>
</form>

<p><a href="export_history_csv.php?<?php echo http_build_query($_GET); ?>">Export as CSV</a></p>

<table border="1" cellpadding="4">
    <tr>
        <th>ID</th><th>Season</th><th>Score</th><th>Distance</th><th>Top Speed</th>
        <th>Crash</th><th>Started</th><th>Ended</th><th>Actions</th>
    </tr>
    <?php foreach ($sessions as $s): ?>
    <tr>
        <td><?php echo (int)$s['session_id']; ?></td>
        <td><?php echo (int)$s['season_id']; ?></td>
        <td><?php echo (int)$s['score']; ?></td>
        <td><?php echo (int)$s['distance_m']; ?></td>
        <td><?php echo htmlspecialchars($s['top_speed']); ?></td>
        <td><?php echo htmlspecialchars($s['crash_type']); ?></td>
        <td><?php echo htmlspecialchars($s['started_at']); ?></td>
        <td><?php echo htmlspecialchars($s['ended_at']); ?></td>
        <td>
            <a href="session_detail.php?id=<?php echo (int)$s['session_id']; ?>">View</a>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this session?');">
                <input type="hidden" name="delete_id" value="<?php echo (int)$s['session_id']; ?>">
                <button type="submit">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<h3>Stats</h3>
<ul>
    <li>Total Sessions: <?php echo $totalSessions; ?></li>
    <li>Average Score: <?php echo number_format($avgScore, 1); ?></li>
    <li>Longest Distance: <?php echo (int)$longestDistance; ?> m</li>
</ul>

</body>
</html>