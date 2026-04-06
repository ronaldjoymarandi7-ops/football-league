<?php
$page_title = 'Standings';
require_once '../includes/header.php';

$standings = $conn->query("
    SELECT *,
           (wins*3 + draws) as pts,
           (wins + losses + draws) as mp,
           (goals_scored - goals_conceded) as gd
    FROM teams
    ORDER BY pts DESC, gd DESC, goals_scored DESC, name ASC
");

// Top scorer
$top_scorers = $conn->query("
    SELECT p.*, t.name as team_name, t.logo_color
    FROM players p JOIN teams t ON p.team_id = t.id
    ORDER BY p.goals DESC LIMIT 10
");

// Top assists
$top_assists = $conn->query("
    SELECT p.*, t.name as team_name, t.logo_color
    FROM players p JOIN teams t ON p.team_id = t.id
    ORDER BY p.assists DESC LIMIT 5
");
?>

<div class="page">
    <div class="page-header">
        <div>
            <div class="page-title">League Standings</div>
            <div class="page-subtitle">Current points table & statistics</div>
        </div>
    </div>

    <div class="dashboard-grid three">
        <!-- Points Table -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Points Table</span>
                <span style="color:var(--text-muted);font-size:0.8rem">W=3pts D=1pt L=0pts</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Pos</th>
                            <th>Team</th>
                            <th>MP</th>
                            <th>W</th>
                            <th>D</th>
                            <th>L</th>
                            <th>GF</th>
                            <th>GA</th>
                            <th>GD</th>
                            <th>Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $pos = 1; while ($t = $standings->fetch_assoc()): ?>
                    <tr class="pos-<?= $pos ?>">
                        <td>
                            <?php if ($pos === 1): ?>
                                <span style="color:var(--yellow);font-weight:700">🥇</span>
                            <?php elseif ($pos === 2): ?>
                                <span style="color:#aaa">🥈</span>
                            <?php elseif ($pos === 3): ?>
                                <span style="color:#cd7f32">🥉</span>
                            <?php else: ?>
                                <span style="color:var(--text-muted)"><?= $pos ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="team-cell">
                                <div class="team-logo" style="background:<?= $t['logo_color'] ?>;width:28px;height:28px;font-size:0.75rem;">
                                    <?= strtoupper(substr($t['name'], 0, 2)) ?>
                                </div>
                                <div>
                                    <div class="td-name"><?= htmlspecialchars($t['name']) ?></div>
                                    <div style="font-size:0.78rem;color:var(--text-muted)"><?= htmlspecialchars($t['city']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?= $t['mp'] ?></td>
                        <td style="color:var(--green)"><?= $t['wins'] ?></td>
                        <td><?= $t['draws'] ?></td>
                        <td style="color:var(--red)"><?= $t['losses'] ?></td>
                        <td><?= $t['goals_scored'] ?></td>
                        <td><?= $t['goals_conceded'] ?></td>
                        <td style="color:<?= $t['gd'] >= 0 ? 'var(--green)' : 'var(--red)' ?>">
                            <?= $t['gd'] > 0 ? '+' : '' ?><?= $t['gd'] ?>
                        </td>
                        <td class="standings-pts"><?= $t['pts'] ?></td>
                    </tr>
                    <?php $pos++; endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Scorers -->
        <div>
            <div class="card" style="margin-bottom:1.25rem;">
                <div class="card-header">
                    <span class="card-title">Top Scorers</span>
                    <span>⚽</span>
                </div>
                <div class="card-body">
                    <div class="scorer-list">
                    <?php $rank = 1; while ($s = $top_scorers->fetch_assoc()): ?>
                    <div class="scorer-item">
                        <div class="scorer-rank <?= $rank === 1 ? 'gold' : '' ?>"><?= $rank ?></div>
                        <div class="team-logo" style="background:<?= $s['logo_color'] ?>">
                            <?= strtoupper(substr($s['team_name'], 0, 1)) ?>
                        </div>
                        <div class="scorer-info">
                            <div class="scorer-name"><?= htmlspecialchars($s['name']) ?></div>
                            <div class="scorer-team"><?= htmlspecialchars($s['team_name']) ?></div>
                        </div>
                        <div class="scorer-goals"><?= $s['goals'] ?></div>
                    </div>
                    <?php $rank++; endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Top Assists -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Top Assists</span>
                    <span>🎯</span>
                </div>
                <div class="card-body">
                    <div class="scorer-list">
                    <?php $rank = 1; while ($s = $top_assists->fetch_assoc()): ?>
                    <div class="scorer-item">
                        <div class="scorer-rank <?= $rank === 1 ? 'gold' : '' ?>"><?= $rank ?></div>
                        <div class="team-logo" style="background:<?= $s['logo_color'] ?>">
                            <?= strtoupper(substr($s['team_name'], 0, 1)) ?>
                        </div>
                        <div class="scorer-info">
                            <div class="scorer-name"><?= htmlspecialchars($s['name']) ?></div>
                            <div class="scorer-team"><?= htmlspecialchars($s['team_name']) ?></div>
                        </div>
                        <div style="font-family:'Bebas Neue',sans-serif;font-size:1.5rem;color:var(--blue)"><?= $s['assists'] ?></div>
                    </div>
                    <?php $rank++; endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
