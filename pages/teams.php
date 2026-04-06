<?php
$page_title = 'Teams';
require_once '../includes/header.php';

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = $conn->real_escape_string(trim($_POST['name']));
        $city = $conn->real_escape_string(trim($_POST['city']));
        $coach = $conn->real_escape_string(trim($_POST['coach']));
        $stadium = $conn->real_escape_string(trim($_POST['stadium']));
        $founded = (int)$_POST['founded_year'];
        $color = $conn->real_escape_string($_POST['logo_color']);
        $sql = "INSERT INTO teams (name, city, coach, stadium, founded_year, logo_color) VALUES ('$name','$city','$coach','$stadium',$founded,'$color')";
        if ($conn->query($sql)) { setFlash('success', "Team '$name' added successfully!"); }
        else { setFlash('error', 'Error: ' . $conn->error); }
        header("Location: teams.php"); exit;
    }
    if ($_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        $name = $conn->real_escape_string(trim($_POST['name']));
        $city = $conn->real_escape_string(trim($_POST['city']));
        $coach = $conn->real_escape_string(trim($_POST['coach']));
        $stadium = $conn->real_escape_string(trim($_POST['stadium']));
        $founded = (int)$_POST['founded_year'];
        $color = $conn->real_escape_string($_POST['logo_color']);
        $sql = "UPDATE teams SET name='$name', city='$city', coach='$coach', stadium='$stadium', founded_year=$founded, logo_color='$color' WHERE id=$id";
        if ($conn->query($sql)) { setFlash('success', "Team updated!"); }
        else { setFlash('error', 'Error: ' . $conn->error); }
        header("Location: teams.php"); exit;
    }
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['id'];
        $t = $conn->query("SELECT name FROM teams WHERE id=$id")->fetch_assoc();
        if ($conn->query("DELETE FROM teams WHERE id=$id")) { setFlash('success', "Team '{$t['name']}' deleted."); }
        else { setFlash('error', 'Could not delete team.'); }
        header("Location: teams.php"); exit;
    }
}

// Fetch all teams
$teams = $conn->query("
    SELECT t.*, (wins*3+draws) as pts, (wins+losses+draws) as mp,
           (SELECT COUNT(*) FROM players WHERE team_id = t.id) as player_count
    FROM teams t ORDER BY pts DESC, name ASC
");
?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Teams</div>
            <div class="page-subtitle">Manage all clubs in the league</div>
        </div>
        <button class="btn btn-primary" onclick="openModal('addTeamModal')">+ Add Team</button>
    </div>

    <!-- Search -->
    <div class="search-bar">
        <input type="text" id="teamSearch" class="search-input" placeholder="🔍  Search teams...">
    </div>

    <!-- Teams Grid -->
    <div class="teams-grid" id="teamsTable">
        <?php while ($t = $teams->fetch_assoc()): ?>
        <div class="team-card" data-name="<?= htmlspecialchars($t['name']) ?>">
            <div class="team-card-top" style="background:linear-gradient(135deg, <?= $t['logo_color'] ?>22, <?= $t['logo_color'] ?>44);">
                <div class="team-logo" style="background:<?= $t['logo_color'] ?>;width:56px;height:56px;font-size:1.4rem;">
                    <?= strtoupper(substr($t['name'], 0, 2)) ?>
                </div>
            </div>
            <div class="team-card-body">
                <div class="team-card-name"><?= htmlspecialchars($t['name']) ?></div>
                <div class="team-card-meta">
                    📍 <?= htmlspecialchars($t['city']) ?> &nbsp;·&nbsp;
                    🧑‍💼 <?= htmlspecialchars($t['coach']) ?>
                </div>
                <div class="team-card-meta">
                    🏟️ <?= htmlspecialchars($t['stadium']) ?> &nbsp;·&nbsp;
                    📅 Est. <?= $t['founded_year'] ?>
                </div>
                <div class="team-stats-row">
                    <div class="team-stat">
                        <div class="team-stat-val" style="color:var(--green)"><?= $t['pts'] ?></div>
                        <div class="team-stat-lbl">PTS</div>
                    </div>
                    <div class="team-stat">
                        <div class="team-stat-val"><?= $t['wins'] ?></div>
                        <div class="team-stat-lbl">W</div>
                    </div>
                    <div class="team-stat">
                        <div class="team-stat-val"><?= $t['draws'] ?></div>
                        <div class="team-stat-lbl">D</div>
                    </div>
                    <div class="team-stat">
                        <div class="team-stat-val"><?= $t['losses'] ?></div>
                        <div class="team-stat-lbl">L</div>
                    </div>
                    <div class="team-stat">
                        <div class="team-stat-val" style="color:var(--blue)"><?= $t['player_count'] ?></div>
                        <div class="team-stat-lbl">PLY</div>
                    </div>
                </div>
            </div>
            <div class="team-card-actions">
                <button class="btn btn-secondary btn-sm"
                    onclick="openEditModal(<?= $t['id'] ?>, '<?= addslashes($t['name']) ?>', '<?= addslashes($t['city']) ?>', '<?= addslashes($t['coach']) ?>', '<?= addslashes($t['stadium']) ?>', <?= $t['founded_year'] ?>, '<?= $t['logo_color'] ?>')">
                    ✏️ Edit
                </button>
                <form method="POST" style="display:inline;" onsubmit="return confirmDelete(this, '<?= addslashes($t['name']) ?>')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $t['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">🗑 Delete</button>
                </form>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Add Team Modal -->
<div class="modal-overlay" id="addTeamModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Add New Team</span>
            <span class="modal-close" onclick="closeModal('addTeamModal')">✕</span>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Team Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Thunder FC">
                    </div>
                    <div class="form-group">
                        <label>City *</label>
                        <input type="text" name="city" required placeholder="e.g. Mumbai">
                    </div>
                    <div class="form-group">
                        <label>Head Coach *</label>
                        <input type="text" name="coach" required placeholder="e.g. Ravi Sharma">
                    </div>
                    <div class="form-group">
                        <label>Stadium *</label>
                        <input type="text" name="stadium" required placeholder="e.g. Thunder Arena">
                    </div>
                    <div class="form-group">
                        <label>Founded Year *</label>
                        <input type="number" name="founded_year" required min="1850" max="2024" placeholder="2010">
                    </div>
                    <div class="form-group">
                        <label>Logo Color</label>
                        <input type="color" name="logo_color" value="#e74c3c">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Add Team</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addTeamModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Modal -->
<div class="modal-overlay" id="editTeamModal">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">Edit Team</span>
            <span class="modal-close" onclick="closeModal('editTeamModal')">✕</span>
        </div>
        <div class="modal-body">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Team Name *</label>
                        <input type="text" name="name" id="editName" required>
                    </div>
                    <div class="form-group">
                        <label>City *</label>
                        <input type="text" name="city" id="editCity" required>
                    </div>
                    <div class="form-group">
                        <label>Head Coach *</label>
                        <input type="text" name="coach" id="editCoach" required>
                    </div>
                    <div class="form-group">
                        <label>Stadium *</label>
                        <input type="text" name="stadium" id="editStadium" required>
                    </div>
                    <div class="form-group">
                        <label>Founded Year *</label>
                        <input type="number" name="founded_year" id="editYear" min="1850" max="2024" required>
                    </div>
                    <div class="form-group">
                        <label>Logo Color</label>
                        <input type="color" name="logo_color" id="editColor">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editTeamModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, name, city, coach, stadium, year, color) {
    document.getElementById('editId').value = id;
    document.getElementById('editName').value = name;
    document.getElementById('editCity').value = city;
    document.getElementById('editCoach').value = coach;
    document.getElementById('editStadium').value = stadium;
    document.getElementById('editYear').value = year;
    document.getElementById('editColor').value = color;
    openModal('editTeamModal');
}

// Team card search
document.getElementById('teamSearch').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.team-card').forEach(card => {
        card.style.display = card.dataset.name.toLowerCase().includes(val) ? '' : 'none';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
