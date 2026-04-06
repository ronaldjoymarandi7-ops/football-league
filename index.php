<?php
$page_title = 'Dashboard';
require_once 'includes/header.php';

// Fetch stats
$teams_count = $conn->query("SELECT COUNT(*) as c FROM teams")->fetch_assoc()['c'];
$players_count = $conn->query("SELECT COUNT(*) as c FROM players")->fetch_assoc()['c'];
$matches_played = $conn->query("SELECT COUNT(*) as c FROM matches WHERE status='Completed'")->fetch_assoc()['c'];
$upcoming = $conn->query("SELECT COUNT(*) as c FROM matches WHERE status='Scheduled'")->fetch_assoc()['c'];

// Top scorers
$scorers = $conn->query("
    SELECT p.name, p.goals, p.assists, t.name as team, t.logo_color
    FROM players p JOIN teams t ON p.team_id = t.id
    WHERE p.goals > 0 ORDER BY p.goals DESC, p.assists DESC LIMIT 5
");

// Recent matches
$recent = $conn->query("
    SELECT m.*, 
           ht.name as home_name, ht.logo_color as home_color,
           at.name as away_name, at.logo_color as away_color
    FROM matches m
    JOIN teams ht ON m.home_team_id = ht.id
    JOIN teams at ON m.away_team_id = at.id
    WHERE m.status = 'Completed'
    ORDER BY m.match_date DESC LIMIT 4
");

// Upcoming matches
$next_matches = $conn->query("
    SELECT m.*, 
           ht.name as home_name, ht.logo_color as home_color,
           at.name as away_name, at.logo_color as away_color
    FROM matches m
    JOIN teams ht ON m.home_team_id = ht.id
    JOIN teams at ON m.away_team_id = at.id
    WHERE m.status = 'Scheduled'
    ORDER BY m.match_date ASC LIMIT 4
");

// Standings (top 5)
$standings = $conn->query("
    SELECT *, (wins*3 + draws) as pts,
           (wins+losses+draws) as mp,
           (goals_scored - goals_conceded) as gd
    FROM teams ORDER BY pts DESC, gd DESC, goals_scored DESC LIMIT 5
");
?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">Dashboard</div>
            <div class="page-subtitle">Welcome to <?= SITE_NAME ?> — Football League Management System</div>
        </div>
        <div style="display:flex;gap:10px;">
            <a href="pages/teams.php?action=add" class="btn btn-secondary">+ Add Team</a>
            <a href="pages/matches.php?action=add" class="btn btn-primary">+ Schedule Match</a>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card green">
            <div class="stat-label">Total Teams</div>
            <div class="stat-value"><?= $teams_count ?></div>
            <div class="stat-icon">🏟️</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Registered Players</div>
            <div class="stat-value"><?= $players_count ?></div>
            <div class="stat-icon">👟</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-label">Matches Played</div>
            <div class="stat-value"><?= $matches_played ?></div>
            <div class="stat-icon">⚽</div>
        </div>
        <div class="stat-card red">
            <div class="stat-label">Upcoming Fixtures</div>
            <div class="stat-value"><?= $upcoming ?></div>
            <div class="stat-icon">📅</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Recent Results -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Recent Results</span>
                <a href="pages/matches.php" class="btn btn-secondary btn-sm">View All</a>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
                <?php while ($m = $recent->fetch_assoc()): ?>
                <div class="match-card">
                    <div class="match-meta">
                        <?= date('D, d M Y', strtotime($m['match_date'])) ?> &nbsp;·&nbsp; <?= $m['round'] ?>
                    </div>
                    <div class="match-teams">
                        <div class="match-team match-team-home">
                            <div class="match-team-name"><?= htmlspecialchars($m['home_name']) ?></div>
                        </div>
                        <div class="match-score">
                            <?= $m['home_score'] ?> &ndash; <?= $m['away_score'] ?>
                        </div>
                        <div class="match-team match-team-away">
                            <div class="match-team-name"><?= htmlspecialchars($m['away_name']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Top Scorers -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Top Scorers</span>
                <a href="pages/players.php" class="btn btn-secondary btn-sm">All Players</a>
            </div>
            <div class="card-body">
                <div class="scorer-list">
                    <?php $rank = 1; while ($s = $scorers->fetch_assoc()): ?>
                    <div class="scorer-item">
                        <div class="scorer-rank <?= $rank === 1 ? 'gold' : '' ?>"><?= $rank ?></div>
                        <div class="team-logo" style="background:<?= $s['logo_color'] ?>">
                            <?= strtoupper(substr($s['team'], 0, 1)) ?>
                        </div>
                        <div class="scorer-info">
                            <div class="scorer-name"><?= htmlspecialchars($s['name']) ?></div>
                            <div class="scorer-team"><?= htmlspecialchars($s['team']) ?></div>
                        </div>
                        <div class="scorer-goals"><?= $s['goals'] ?>⚽</div>
                    </div>
                    <?php $rank++; endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Upcoming Fixtures -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Upcoming Fixtures</span>
                <a href="pages/matches.php" class="btn btn-secondary btn-sm">Schedule</a>
            </div>
            <div class="card-body" style="display:flex;flex-direction:column;gap:10px;">
                <?php while ($m = $next_matches->fetch_assoc()): ?>
                <div class="match-card">
                    <div class="match-meta">
                        <?= date('D, d M Y', strtotime($m['match_date'])) ?>
                        &nbsp;·&nbsp; <?= date('H:i', strtotime($m['match_time'])) ?>
                        &nbsp;·&nbsp; <?= htmlspecialchars($m['venue']) ?>
                    </div>
                    <div class="match-teams">
                        <div class="match-team match-team-home">
                            <div class="match-team-name"><?= htmlspecialchars($m['home_name']) ?></div>
                        </div>
                        <div class="match-score scheduled">vs</div>
                        <div class="match-team match-team-away">
                            <div class="match-team-name"><?= htmlspecialchars($m['away_name']) ?></div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Mini Standings -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Standings</span>
                <a href="pages/standings.php" class="btn btn-secondary btn-sm">Full Table</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Team</th>
                            <th>MP</th>
                            <th>W</th>
                            <th>Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $pos = 1; while ($t = $standings->fetch_assoc()): ?>
                        <tr class="pos-<?= $pos ?>">
                            <td><?= $pos ?></td>
                            <td class="td-name">
                                <div class="team-cell">
                                    <div class="team-logo" style="background:<?= $t['logo_color'] ?>;width:24px;height:24px;font-size:0.7rem;">
                                        <?= strtoupper(substr($t['name'], 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($t['name']) ?>
                                </div>
                            </td>
                            <td><?= $t['mp'] ?></td>
                            <td><?= $t['wins'] ?></td>
                            <td class="standings-pts"><?= $t['pts'] ?></td>
                        </tr>
                        <?php $pos++; endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
