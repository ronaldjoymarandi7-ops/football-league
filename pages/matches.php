<?php
$page_title = 'Matches';
require_once '../includes/header.php';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $home = (int)$_POST['home_team_id'];
        $away = (int)$_POST['away_team_id'];
        if ($home === $away) { setFlash('error', 'Home and Away teams cannot be the same!'); header("Location: matches.php"); exit; }
        $date = $conn->real_escape_string($_POST['match_date']);
        $time = $conn->real_escape_string($_POST['match_time']);
        $venue = $conn->real_escape_string(trim($_POST['venue']));
        $round = $conn->real_escape_string(trim($_POST['round']));
        $sql = "INSERT INTO matches (home_team_id, away_team_id, match_date, match_time, venue, round)
                VALUES ($home, $away, '$date', '$time', '$venue', '$round')";
        if ($conn->query($sql)) { setFlash('success', 'Match scheduled!'); }
        else { setFlash('error', $conn->error); }
        header("Location: matches.php"); exit;
    }
    if ($_POST['action'] === 'update_score') {
        $id = (int)$_POST['id'];
        $hs = (int)$_POST['home_score'];
        $as = (int)$_POST['away_score'];
        $status = $conn->real_escape_string($_POST['status']);

        // Fetch match
        $m = $conn->query("SELECT * FROM matches WHERE id=$id")->fetch_assoc();

        // Reverse old result if was completed
        if ($m['status'] === 'Completed' && $m['home_score'] !== null) {
            $oh = $m['home_score']; $oa = $m['away_score'];
            $ht = $m['home_team_id']; $at = $m['away_team_id'];
            // Reverse home
            if ($oh > $oa) $conn->query("UPDATE teams SET wins=wins-1, goals_scored=goals_scored-$oh, goals_conceded=goals_conceded-$oa WHERE id=$ht");
            elseif ($oh < $oa) $conn->query("UPDATE teams SET losses=losses-1, goals_scored=goals_scored-$oh, goals_conceded=goals_conceded-$oa WHERE id=$ht");
            else $conn->query("UPDATE teams SET draws=draws-1, goals_scored=goals_scored-$oh, goals_conceded=goals_conceded-$oa WHERE id=$ht");
            // Reverse away
            if ($oa > $oh) $conn->query("UPDATE teams SET wins=wins-1, goals_scored=goals_scored-$oa, goals_conceded=goals_conceded-$oh WHERE id=$at");
            elseif ($oa < $oh) $conn->query("UPDATE teams SET losses=losses-1, goals_scored=goals_scored-$oa, goals_conceded=goals_conceded-$oh WHERE id=$at");
            else $conn->query("UPDATE teams SET draws=draws-1, goals_scored=goals_scored-$oa, goals_conceded=goals_conceded-$oh WHERE id=$at");
        }

        $conn->query("UPDATE matches SET home_score=$hs, away_score=$as, status='$status' WHERE id=$id");

        // Apply new result
        if ($status === 'Completed') {
            $ht = $m['home_team_id']; $at = $m['away_team_id'];
            if ($hs > $as) {
                $conn->query("UPDATE teams SET wins=wins+1, goals_scored=goals_scored+$hs, goals_conceded=goals_conceded+$as WHERE id=$ht");
                $conn->query("UPDATE teams SET losses=losses+1, goals_scored=goals_scored+$as, goals_conceded=goals_conceded+$hs WHERE id=$at");
            } elseif ($hs < $as) {
                $conn->query("UPDATE teams SET losses=losses+1, goals_scored=goals_scored+$hs, goals_conceded=goals_conceded+$as WHERE id=$ht");
                $conn->query("UPDATE teams SET wins=wins+1, goals_scored=goals_scored+$as, goals_conceded=goals_conceded+$hs WHERE id=$at");
            } else {
                $conn->query("UPDATE teams SET draws=draws+1, goals_scored=goals_scored+$hs, goals_conceded=goals_conceded+$as WHERE id=$ht");
                $conn->query("UPDATE teams SET draws=draws+1, goals_scored=goals_scored+$as, goals_conceded=goals_conceded+$hs WHERE id=$at");
            }
        }
        setFlash('success', 'Match result updated & standings recalculated!');
        header("Location: matches.php"); exit;
    }
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        if ($conn->query("DELETE FROM matches WHERE id=$id")) { setFlash('success', 'Match removed.'); }
        else { setFlash('error', 'Could not delete match.'); }
        header("Location: matches.php"); exit;
    }
}

$filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$where = $filter ? "WHERE m.status='$filter'" : '';

$matches = $conn->query("
    SELECT m.*,
           ht.name as home_name, ht.logo_color as home_color,
           at.name as away_name, at.logo_color as away_color
    FROM matches m
    JOIN teams ht ON m.home_team_id = ht.id
    JOIN teams at ON m.away_team_id = at.id
    $where ORDER BY m.match_date DESC, m.match_time DESC
");

$teams_list = $conn->query("SELECT id, name FROM teams ORDER BY name");
$teams_arr = [];
while ($r = $teams_list->fetch_assoc()) $teams_arr[] = $r;
?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Matches</div>
            <div class="page-subtitle">Schedule fixtures and record results</div>
        </div>
        <button class="btn btn-primary" onclick="openModal('addMatchModal')">+ Schedule Match</button>
    </div>

    <!-- Filter Tabs -->
    <div class="search-bar">
        <input type="text" id="matchSearch" class="search-input" placeholder="🔍  Search by team or venue...">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php foreach (['','Scheduled','Live','Completed','Postponed'] as $s): ?>
            <a href="matches.php<?= $s ? '?status='.$s : '' ?>"
               class="btn <?= $filter === $s ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                <?= $s ?: 'All' ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table id="matchesTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Home Team</th>
                        <th>Score</th>
                        <th>Away Team</th>
                        <th>Venue</th>
                        <th>Round</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php $i = 1; while ($m = $matches->fetch_assoc()):
                    $s_class = ['Completed'=>'badge-completed','Scheduled'=>'badge-scheduled','Live'=>'badge-live','Postponed'=>'badge-postponed'][$m['status']] ?? '';
                ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $i++ ?></td>
                    <td>
                        <div style="font-weight:600;color:var(--text)"><?= date('d M Y', strtotime($m['match_date'])) ?></div>
                        <div style="color:var(--text-muted);font-size:0.82rem"><?= date('H:i', strtotime($m['match_time'])) ?></div>
                    </td>
                    <td>
                        <div class="team-cell">
                            <div class="team-logo" style="background:<?= $m['home_color'] ?>;width:26px;height:26px;font-size:0.7rem;">
                                <?= strtoupper(substr($m['home_name'], 0, 1)) ?>
                            </div>
                            <span style="font-weight:600;color:var(--text)"><?= htmlspecialchars($m['home_name']) ?></span>
                        </div>
                    </td>
                    <td>
                        <?php if ($m['status'] === 'Completed'): ?>
                            <span style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;letter-spacing:2px;color:var(--text)"><?= $m['home_score'] ?> – <?= $m['away_score'] ?></span>
                        <?php elseif ($m['status'] === 'Live'): ?>
                            <span style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;color:var(--red)"><?= $m['home_score'] ?> – <?= $m['away_score'] ?></span>
                        <?php else: ?>
                            <span style="color:var(--text-muted)">vs</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="team-cell">
                            <div class="team-logo" style="background:<?= $m['away_color'] ?>;width:26px;height:26px;font-size:0.7rem;">
                                <?= strtoupper(substr($m['away_name'], 0, 1)) ?>
                            </div>
                            <span style="font-weight:600;color:var(--text)"><?= htmlspecialchars($m['away_name']) ?></span>
                        </div>
                    </td>
                    <td style="color:var(--text-dim)"><?= htmlspecialchars($m['venue']) ?></td>
                    <td style="color:var(--text-dim)"><?= htmlspecialchars($m['round']) ?></td>
                    <td><span class="badge <?= $s_class ?>"><?= $m['status'] ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-secondary btn-sm" onclick='openScoreModal(<?= json_encode($m) ?>)'>📊</button>
                            <form method="POST" onsubmit="return confirm('Delete this match?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm btn-icon">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Match Modal -->
<div class="modal-overlay" id="addMatchModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Schedule Match</span>
            <span class="modal-close" onclick="closeModal('addMatchModal')">✕</span>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Home Team *</label>
                        <select name="home_team_id" required>
                            <option value="">Select Team</option>
                            <?php foreach ($teams_arr as $tm): ?>
                            <option value="<?= $tm['id'] ?>"><?= htmlspecialchars($tm['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Away Team *</label>
                        <select name="away_team_id" required>
                            <option value="">Select Team</option>
                            <?php foreach ($teams_arr as $tm): ?>
                            <option value="<?= $tm['id'] ?>"><?= htmlspecialchars($tm['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date *</label>
                        <input type="date" name="match_date" required>
                    </div>
                    <div class="form-group">
                        <label>Time *</label>
                        <input type="time" name="match_time" required value="17:00">
                    </div>
                    <div class="form-group full">
                        <label>Venue *</label>
                        <input type="text" name="venue" required placeholder="Stadium name">
                    </div>
                    <div class="form-group full">
                        <label>Round / Stage</label>
                        <input type="text" name="round" placeholder="e.g. Round 1, Quarter Final">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Schedule</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addMatchModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Score Modal -->
<div class="modal-overlay" id="scoreModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Update Result</span>
            <span class="modal-close" onclick="closeModal('scoreModal')">✕</span>
        </div>
        <div class="modal-body">
            <div id="scoreMatchInfo" style="background:rgba(255,255,255,0.04);border-radius:8px;padding:16px;margin-bottom:20px;text-align:center;font-family:'Bebas Neue',sans-serif;font-size:1.3rem;letter-spacing:2px;color:var(--text)"></div>
            <form method="POST">
                <input type="hidden" name="action" value="update_score">
                <input type="hidden" name="id" id="smId">
                <div class="form-grid">
                    <div class="form-group">
                        <label id="smHomeLabel">Home Score</label>
                        <input type="number" name="home_score" id="smHomeScore" min="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <label id="smAwayLabel">Away Score</label>
                        <input type="number" name="away_score" id="smAwayScore" min="0" value="0" required>
                    </div>
                    <div class="form-group full">
                        <label>Match Status *</label>
                        <select name="status" id="smStatus" required>
                            <option value="Scheduled">Scheduled</option>
                            <option value="Live">Live</option>
                            <option value="Completed">Completed</option>
                            <option value="Postponed">Postponed</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Result</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('scoreModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openScoreModal(m) {
    document.getElementById('smId').value = m.id;
    document.getElementById('smHomeLabel').textContent = m.home_name + ' Score';
    document.getElementById('smAwayLabel').textContent = m.away_name + ' Score';
    document.getElementById('smHomeScore').value = m.home_score ?? 0;
    document.getElementById('smAwayScore').value = m.away_score ?? 0;
    document.getElementById('smStatus').value = m.status;
    document.getElementById('scoreMatchInfo').textContent = m.home_name + '  vs  ' + m.away_name;
    openModal('scoreModal');
}

// Match search across team names
document.getElementById('matchSearch').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#matchesTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
