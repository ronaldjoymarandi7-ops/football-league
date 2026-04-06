<?php
$page_title = 'Players';
require_once '../includes/header.php';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $team_id = (int)$_POST['team_id'];
        $name = $conn->real_escape_string(trim($_POST['name']));
        $position = $conn->real_escape_string($_POST['position']);
        $jersey = (int)$_POST['jersey_number'];
        $nat = $conn->real_escape_string(trim($_POST['nationality']));
        $age = (int)$_POST['age'];
        $goals = (int)$_POST['goals'];
        $assists = (int)$_POST['assists'];
        $mp = (int)$_POST['matches_played'];
        $sql = "INSERT INTO players (team_id, name, position, jersey_number, nationality, age, goals, assists, matches_played)
                VALUES ($team_id, '$name', '$position', $jersey, '$nat', $age, $goals, $assists, $mp)";
        if ($conn->query($sql)) { setFlash('success', "Player '$name' added!"); }
        else { setFlash('error', $conn->error); }
        header("Location: players.php"); exit;
    }
    if ($_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        $team_id = (int)$_POST['team_id'];
        $name = $conn->real_escape_string(trim($_POST['name']));
        $position = $conn->real_escape_string($_POST['position']);
        $jersey = (int)$_POST['jersey_number'];
        $nat = $conn->real_escape_string(trim($_POST['nationality']));
        $age = (int)$_POST['age'];
        $goals = (int)$_POST['goals'];
        $assists = (int)$_POST['assists'];
        $mp = (int)$_POST['matches_played'];
        $sql = "UPDATE players SET team_id=$team_id, name='$name', position='$position', jersey_number=$jersey,
                nationality='$nat', age=$age, goals=$goals, assists=$assists, matches_played=$mp WHERE id=$id";
        if ($conn->query($sql)) { setFlash('success', "Player updated!"); }
        else { setFlash('error', $conn->error); }
        header("Location: players.php"); exit;
    }
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $p = $conn->query("SELECT name FROM players WHERE id=$id")->fetch_assoc();
        if ($conn->query("DELETE FROM players WHERE id=$id")) { setFlash('success', "Player '{$p['name']}' removed."); }
        else { setFlash('error', 'Could not delete player.'); }
        header("Location: players.php"); exit;
    }
}

// Filters
$filter_team = isset($_GET['team']) ? (int)$_GET['team'] : 0;
$filter_pos = isset($_GET['position']) ? $conn->real_escape_string($_GET['position']) : '';

$where = "WHERE 1=1";
if ($filter_team) $where .= " AND p.team_id = $filter_team";
if ($filter_pos) $where .= " AND p.position = '$filter_pos'";

$players = $conn->query("
    SELECT p.*, t.name as team_name, t.logo_color
    FROM players p JOIN teams t ON p.team_id = t.id
    $where ORDER BY p.goals DESC, p.name ASC
");

$teams_list = $conn->query("SELECT id, name, logo_color FROM teams ORDER BY name");
$teams_arr = [];
while ($r = $teams_list->fetch_assoc()) $teams_arr[] = $r;
?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Players</div>
            <div class="page-subtitle">All registered players in the league</div>
        </div>
        <button class="btn btn-primary" onclick="openModal('addPlayerModal')">+ Add Player</button>
    </div>

    <!-- Filters -->
    <div class="search-bar">
        <input type="text" id="playerSearch" class="search-input" placeholder="🔍  Search players...">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
            <select name="team" class="search-input" style="min-width:160px;" onchange="this.form.submit()">
                <option value="">All Teams</option>
                <?php foreach ($teams_arr as $tm): ?>
                <option value="<?= $tm['id'] ?>" <?= $filter_team == $tm['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tm['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <select name="position" class="search-input" style="min-width:140px;" onchange="this.form.submit()">
                <option value="">All Positions</option>
                <?php foreach (['Goalkeeper','Defender','Midfielder','Forward'] as $pos): ?>
                <option value="<?= $pos ?>" <?= $filter_pos == $pos ? 'selected' : '' ?>><?= $pos ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($filter_team || $filter_pos): ?>
            <a href="players.php" class="btn btn-secondary btn-sm">Clear Filters</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table id="playersTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Player</th>
                        <th>Team</th>
                        <th>Position</th>
                        <th>Jersey</th>
                        <th>Age</th>
                        <th>Nationality</th>
                        <th>MP</th>
                        <th>Goals</th>
                        <th>Assists</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $i = 1;
                while ($p = $players->fetch_assoc()):
                    $pos_class = ['Goalkeeper'=>'badge-gk','Defender'=>'badge-def','Midfielder'=>'badge-mid','Forward'=>'badge-fwd'][$p['position']] ?? '';
                ?>
                <tr>
                    <td style="color:var(--text-muted)"><?= $i++ ?></td>
                    <td class="td-name"><?= htmlspecialchars($p['name']) ?></td>
                    <td>
                        <div class="team-cell">
                            <div class="team-logo" style="background:<?= $p['logo_color'] ?>;width:26px;height:26px;font-size:0.7rem;">
                                <?= strtoupper(substr($p['team_name'], 0, 1)) ?>
                            </div>
                            <?= htmlspecialchars($p['team_name']) ?>
                        </div>
                    </td>
                    <td><span class="badge <?= $pos_class ?>"><?= $p['position'] ?></span></td>
                    <td style="font-family:'Bebas Neue',sans-serif;font-size:1.2rem;color:var(--text)"><?= $p['jersey_number'] ?></td>
                    <td><?= $p['age'] ?></td>
                    <td><?= htmlspecialchars($p['nationality']) ?></td>
                    <td><?= $p['matches_played'] ?></td>
                    <td style="color:var(--red);font-weight:700"><?= $p['goals'] ?></td>
                    <td style="color:var(--green)"><?= $p['assists'] ?></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <button class="btn btn-secondary btn-sm btn-icon" onclick="openEditPlayerModal(<?= htmlspecialchars(json_encode($p)) ?>)">✏️</button>
                            <form method="POST" onsubmit="return confirmDelete(this, '<?= addslashes($p['name']) ?>')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
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

<!-- Add Player Modal -->
<div class="modal-overlay" id="addPlayerModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add New Player</span>
            <span class="modal-close" onclick="closeModal('addPlayerModal')">✕</span>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Full Name *</label>
                        <input type="text" name="name" required placeholder="Player name">
                    </div>
                    <div class="form-group">
                        <label>Team *</label>
                        <select name="team_id" required>
                            <option value="">Select Team</option>
                            <?php foreach ($teams_arr as $tm): ?>
                            <option value="<?= $tm['id'] ?>"><?= htmlspecialchars($tm['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Position *</label>
                        <select name="position" required>
                            <?php foreach (['Goalkeeper','Defender','Midfielder','Forward'] as $pos): ?>
                            <option value="<?= $pos ?>"><?= $pos ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jersey Number *</label>
                        <input type="number" name="jersey_number" required min="1" max="99">
                    </div>
                    <div class="form-group">
                        <label>Age *</label>
                        <input type="number" name="age" required min="15" max="45">
                    </div>
                    <div class="form-group full">
                        <label>Nationality *</label>
                        <input type="text" name="nationality" required placeholder="e.g. Indian">
                    </div>
                    <div class="form-group">
                        <label>Goals</label>
                        <input type="number" name="goals" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>Assists</label>
                        <input type="number" name="assists" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label>Matches Played</label>
                        <input type="number" name="matches_played" value="0" min="0">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Player</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addPlayerModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Player Modal -->
<div class="modal-overlay" id="editPlayerModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Player</span>
            <span class="modal-close" onclick="closeModal('editPlayerModal')">✕</span>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="epId">
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Full Name *</label>
                        <input type="text" name="name" id="epName" required>
                    </div>
                    <div class="form-group">
                        <label>Team *</label>
                        <select name="team_id" id="epTeam" required>
                            <?php foreach ($teams_arr as $tm): ?>
                            <option value="<?= $tm['id'] ?>"><?= htmlspecialchars($tm['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Position *</label>
                        <select name="position" id="epPos" required>
                            <?php foreach (['Goalkeeper','Defender','Midfielder','Forward'] as $pos): ?>
                            <option value="<?= $pos ?>"><?= $pos ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jersey Number *</label>
                        <input type="number" name="jersey_number" id="epJersey" min="1" max="99" required>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" id="epAge" min="15" max="45">
                    </div>
                    <div class="form-group full">
                        <label>Nationality</label>
                        <input type="text" name="nationality" id="epNat">
                    </div>
                    <div class="form-group">
                        <label>Goals</label>
                        <input type="number" name="goals" id="epGoals" min="0">
                    </div>
                    <div class="form-group">
                        <label>Assists</label>
                        <input type="number" name="assists" id="epAssists" min="0">
                    </div>
                    <div class="form-group">
                        <label>Matches Played</label>
                        <input type="number" name="matches_played" id="epMp" min="0">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editPlayerModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditPlayerModal(p) {
    document.getElementById('epId').value = p.id;
    document.getElementById('epName').value = p.name;
    document.getElementById('epTeam').value = p.team_id;
    document.getElementById('epPos').value = p.position;
    document.getElementById('epJersey').value = p.jersey_number;
    document.getElementById('epAge').value = p.age;
    document.getElementById('epNat').value = p.nationality;
    document.getElementById('epGoals').value = p.goals;
    document.getElementById('epAssists').value = p.assists;
    document.getElementById('epMp').value = p.matches_played;
    openModal('editPlayerModal');
}
</script>

<?php require_once '../includes/footer.php'; ?>
