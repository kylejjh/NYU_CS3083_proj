<?php
require_once 'auth.php';
require_login();

$seasonList = $pdo->query("SELECT season_id, name FROM season ORDER BY start_date")->fetchAll();

$active = $pdo->query("SELECT season_id FROM season WHERE is_active = 1 LIMIT 1")->fetch();
$defaultSeasonId = $active['season_id'] ?? ($seasonList[0]['season_id'] ?? null);

$season_id = (int)($_GET['season_id'] ?? $defaultSeasonId);
$sort = $_GET['sort'] ?? 'score';

$allowedSorts = [
    'score'    => 's.score',
    'distance' => 's.distance_m',
    'speed'    => 's.top_speed',
    'date'     => 's.ended_at'
];
$orderBy = $allowedSorts[$sort] ?? $allowedSorts['score'];

$stmt = $pdo->prepare("
    SELECT s.session_id, p.username, s.score, s.distance_m, s.top_speed, s.ended_at
    FROM session s
    JOIN player p ON p.player_id = s.player_id
    WHERE s.season_id = ?
    ORDER BY $orderBy DESC
    LIMIT 20
");
$stmt->execute([$season_id]);
$rows = $stmt->fetchAll();

$rankStmt = $pdo->prepare("
    SELECT COUNT(*) + 1 AS rank, MAX(s2.score) AS my_score
    FROM session s1
    JOIN session s2 ON s2.player_id = ? AND s2.season_id = ? 
    WHERE s1.season_id = ? AND s1.score > s2.score
");
$rankStmt->execute([current_user_id(), $season_id, $season_id]);
$rankRow = $rankStmt->fetch();
?>
<!DOCTYPE html>
<html>
<head><title>Leaderboard</title></head>
<body>
<h1>Leaderboard</h1>
<p><a href="home.php">Back to Home</a></p>

<form method="get">
    <label>Season:
        <select name="season_id">
            <?php foreach ($seasonList as $s): ?>
                <option value="<?php echo (int)$s['season_id']; ?>"
                    <?php if ($s['season_id'] == $season_id) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($s['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Sort by:
        <select name="sort">
            <option value="score" <?php if ($sort==='score') echo 'selected'; ?>>Score</option>
            <option value="distance" <?php if ($sort==='distance') echo 'selected'; ?>>Distance</option>
            <option value="speed" <?php if ($sort==='speed') echo 'selected'; ?>>Top Speed</option>
            <option value="date" <?php if ($sort==='date') echo 'selected'; ?>>Date</option>
        </select>
    </label>
    <button type="submit">Apply</button>
</form>

<table border="1" cellpadding="4">
    <tr>
        <th>#</th><th>Player</th><th>Score</th><th>Distance</th><th>Top Speed</th><th>Date</th>
    </tr>
    <?php $i = 1; foreach ($rows as $r): ?>
    <tr>
        <td><?php echo $i++; ?></td>
        <td><?php echo htmlspecialchars($r['username']); ?></td>
        <td><?php echo (int)$r['score']; ?></td>
        <td><?php echo (int)$r['distance_m']; ?></td>
        <td><?php echo htmlspecialchars($r['top_speed']); ?></td>
        <td><?php echo htmlspecialchars($r['ended_at']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<?php if ($rankRow && $rankRow['my_score'] !== null): ?>
<p>Your approximate rank this season: #<?php echo (int)$rankRow['rank']; ?>
    | Best score used: <?php echo (int)$rankRow['my_score']; ?></p>
<?php else: ?>
<p>You haven't played in this season yet.</p>
<?php endif; ?>

</body>
</html>