<?php
session_start();

// ── CONNEXION ──
$con = mysqli_connect('localhost', 'root', '', 'centredeformations');
if (!$con) die("Échec de connexion : " . mysqli_connect_error());

// ── VÉRIFICATION SESSION ──
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'participant') {
    header("Location: connexion.html");
    exit;
}

// ✅ VERSION FINALE — lit la session
$id_user   = $_SESSION['id_user'];
$id_compte = $_SESSION['id_compte'];

// ── REQUÊTE SQL ──
$sql = "
    SELECT 
        s.titre                 AS titre_session,
        s.date_session,
        s.duree,
        MAX(i.statut)           AS statut_inscription,
        MAX(i.date_inscription) AS date_inscription,
        MAX(i.id_inscription)   AS id_inscription,
        p.statut                AS statut_presence
    FROM inscription i
    JOIN sessions s      ON i.id_session = s.id
    LEFT JOIN presence p ON p.id_session = s.id 
                         AND p.id_user = $id_user
    WHERE i.id_compte = $id_compte
    GROUP BY s.id, s.titre, s.date_session, s.duree, p.statut
    ORDER BY s.date_session DESC
";
$result     = mysqli_query($con, $sql);
$historique = mysqli_fetch_all($result, MYSQLI_ASSOC);

// ── CALCUL STATS ──
$total        = count($historique);
$confirmes    = count(array_filter($historique, fn($r) => $r['statut_inscription'] === 'confirmé'));
$presents     = count(array_filter($historique, fn($r) => $r['statut_presence'] === 'present'));
$attestations = count(array_filter($historique, fn($r) =>
    $r['statut_inscription'] === 'confirmé' && $r['statut_presence'] === 'present'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Historique – EmpowerTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f8;
            min-height: 100vh;
        }

        header {
            background: linear-gradient(135deg, #6a5acd, #8a2be2, #9370db);
            padding: 30px 20px 40px;
            text-align: center;
        }

        header h1 {
            color: white;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 24px;
        }

        nav {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        nav a {
            color: white;
            text-decoration: none;
            padding: 10px 28px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.95rem;
            background: rgba(255,255,255,0.15);
            transition: background 0.3s;
        }

        nav a:hover, nav a.active {
            background: rgba(255,255,255,0.30);
        }

        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 16px;
            background: #e8f5e9;
            color: #2e7d32;
            border: 2px solid #a5d6a7;
        }

        .section-title {
            color: #6a5acd;
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .section-divider {
            height: 3px;
            background: linear-gradient(to right, #6a5acd, #9370db, transparent);
            border-radius: 2px;
            margin-bottom: 28px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 18px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(106,90,205,0.1);
            border-top: 4px solid #6a5acd;
        }

        .stat-card .number {
            font-size: 1.8rem;
            font-weight: 700;
            color: #6a5acd;
        }

        .stat-card .label {
            font-size: 0.8rem;
            color: #999;
            margin-top: 4px;
        }

        .stat-card.green  { border-top-color: #28a745; }
        .stat-card.green  .number { color: #28a745; }
        .stat-card.orange { border-top-color: #fd7e14; }
        .stat-card.orange .number { color: #fd7e14; }
        .stat-card.blue   { border-top-color: #007bff; }
        .stat-card.blue   .number { color: #007bff; }

        .table-wrapper {
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 20px rgba(106,90,205,0.1);
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }

        thead { background: linear-gradient(135deg, #6a5acd, #8a2be2); }

        thead th {
            color: white;
            padding: 16px 18px;
            text-align: left;
            font-weight: 600;
            font-size: 0.88rem;
        }

        tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }

        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f8f6ff; }

        td { padding: 14px 18px; font-size: 0.88rem; color: #444; }

        .badge {
            padding: 4px 14px;
            border-radius: 50px;
            font-size: 0.76rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-confirme { background: #e8f5e9; color: #2e7d32; }
        .badge-encours  { background: #fff8e1; color: #f57f17; }
        .badge-present  { background: #e3f2fd; color: #1565c0; }
        .badge-absent   { background: #fce4ec; color: #c62828; }

        .btn-attestation {
            display: inline-block;
            padding: 6px 16px;
            background: linear-gradient(135deg, #6a5acd, #8a2be2);
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .btn-attestation:hover { opacity: 0.85; }
        .non-dispo { color: #ccc; font-size: 0.85rem; }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state .icon { font-size: 3rem; margin-bottom: 16px; }

        .btn-logout {
            display: inline-block;
            padding: 8px 20px;
            background: rgba(255,255,255,0.2);
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.85rem;
            border: 1px solid rgba(255,255,255,0.4);
            transition: background 0.2s;
        }

        .btn-logout:hover { background: rgba(255,255,255,0.35); }

        footer {
            text-align: center;
            padding: 30px;
            color: #aaa;
            font-size: 0.85rem;
            margin-top: 40px;
        }
    </style>
</head>
<body>

<header>
    <h1>EmpowerTech Training Center</h1>
    <nav>
        <a href="index.html">Home</a>
        <a href="service.html">Services</a>
        <a href="contact.html">Contact</a>
        <a href="historique_participant.php" class="active">Mon Historique</a>
        <a href="logout.php" class="btn-logout">🚪 Déconnexion</a>
    </nav>
</header>

<div class="container">

    <div class="role-badge">👤 Espace Participant</div>
    <h2 class="section-title">Mon Historique de Formations</h2>
    <div class="section-divider"></div>

    <?php if (empty($historique)): ?>
        <div class="table-wrapper">
            <div class="empty-state">
                <div class="icon">📋</div>
                <p>Vous n'avez participé à aucune formation pour l'instant.</p>
            </div>
        </div>
    <?php else: ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="number"><?= $total ?></div>
                <div class="label">Total sessions</div>
            </div>
            <div class="stat-card green">
                <div class="number"><?= $confirmes ?></div>
                <div class="label">Confirmées</div>
            </div>
            <div class="stat-card orange">
                <div class="number"><?= $presents ?></div>
                <div class="label">Présences</div>
            </div>
            <div class="stat-card blue">
                <div class="number"><?= $attestations ?></div>
                <div class="label">Attestations disponibles</div>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Formation / Session</th>
                        <th>Date session</th>
                        <th>Durée</th>
                        <th>Date inscription</th>
                        <th>Statut</th>
                        <th>Présence</th>
                        <th>Attestation</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($historique as $i => $row): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><strong><?= htmlspecialchars($row['titre_session']) ?></strong></td>
                        <td><?= $row['date_session'] ?? '–' ?></td>
                        <td><?= htmlspecialchars($row['duree']) ?> h</td>
                        <td><?= $row['date_inscription'] ?></td>
                        <td>
                            <span class="badge <?= $row['statut_inscription'] === 'confirmé'
                                ? 'badge-confirme' : 'badge-encours' ?>">
                                <?= htmlspecialchars($row['statut_inscription']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['statut_presence']): ?>
                                <span class="badge <?= $row['statut_presence'] === 'present'
                                    ? 'badge-present' : 'badge-absent' ?>">
                                    <?= $row['statut_presence'] === 'present' ? 'Présent' : 'Absent' ?>
                                </span>
                            <?php else: ?>
                                <span class="non-dispo">–</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['statut_inscription'] === 'confirmé'
                                   && $row['statut_presence'] === 'present'): ?>
                                <a href="attestation.php?id=<?= $row['id_inscription'] ?>"
                                   class="btn-attestation">📄 Télécharger</a>
                            <?php else: ?>
                                <span class="non-dispo">Non disponible</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<footer>&copy; 2025 EmpowerTech Training Center. Tous droits réservés.</footer>

<?php mysqli_close($con); ?>
</body>
</html>