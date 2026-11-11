<?php
session_start();

// ── CONNEXION MYSQLI (comme le reste du projet) ──
$con = mysqli_connect('localhost', 'root', '', 'centredeformations');
if (!$con) {
    die("Échec de connexion : " . mysqli_connect_error());
}

// ⚠️ TEMPORAIRE — pour tester sans session
// Quand le login sera prêt, remplace par :
// $id_user   = $_SESSION['id_user'];
// $role      = $_SESSION['role'];
// $id_compte = $_SESSION['id_compte'];

$role      = 'admin';
$id_user   = 45;
$id_compte = 13;
// ════════════════════════════════════════
// VUE PARTICIPANT
// ════════════════════════════════════════
if ($role === 'participant') {

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

// ════════════════════════════════════════
// VUE FORMATEUR
// ════════════════════════════════════════
} elseif ($role === 'formateur') {

    $sql = "
        SELECT 
            s.titre                          AS titre_session,
            s.date_session,
            s.duree,
            s.id                             AS id_session,
            COUNT(DISTINCT i.id_inscription) AS nb_participants
        FROM sessions s
        LEFT JOIN inscription i ON i.id_session = s.id
        WHERE s.id_formateur = $id_user
        GROUP BY s.id, s.titre, s.date_session, s.duree
        ORDER BY s.date_session DESC
    ";
    $result     = mysqli_query($con, $sql);
    $historique = mysqli_fetch_all($result, MYSQLI_ASSOC);

// ════════════════════════════════════════
// VUE ADMIN
// ════════════════════════════════════════
} elseif ($role === 'admin') {

    $filtre_session = isset($_GET['id_session']) ? intval($_GET['id_session']) : null;

    // Toutes les sessions pour le filtre
    $res_sessions = mysqli_query($con, "
        SELECT id, titre, date_session FROM sessions ORDER BY date_session DESC
    ");
    $all_sessions = mysqli_fetch_all($res_sessions, MYSQLI_ASSOC);

    // Requête principale admin
    $where = $filtre_session ? "WHERE s.id = $filtre_session" : "";

    $sql = "
        SELECT 
            s.titre                          AS titre_session,
            s.date_session,
            s.duree,
            s.id                             AS id_session,
            COUNT(DISTINCT i.id_inscription) AS nb_inscrits,
            COUNT(DISTINCT CASE WHEN i.statut = 'confirmé' 
                THEN i.id_inscription END)   AS nb_confirmes,
            COUNT(DISTINCT CASE WHEN p.statut = 'present'  
                THEN p.id_presence END)      AS nb_presents,
            COUNT(DISTINCT pay.id_paiement)  AS nb_payes
        FROM sessions s
        LEFT JOIN inscription i ON i.id_session = s.id
        LEFT JOIN presence p    ON p.id_session = s.id
        LEFT JOIN paiement pay  ON pay.id_inscription = i.id_inscription
        $where
        GROUP BY s.id, s.titre, s.date_session, s.duree
        ORDER BY s.date_session DESC
    ";
    $result     = mysqli_query($con, $sql);
    $historique = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique – EmpowerTech</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f8;
            min-height: 100vh;
        }

        /* ── HEADER ── */
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
            letter-spacing: 1px;
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

        /* ── CONTENU ── */
        .container {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        /* ── BADGE ROLE ── */
        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .role-participant { background: #e8f5e9; color: #2e7d32; border: 2px solid #a5d6a7; }
        .role-formateur   { background: #e3f2fd; color: #1565c0; border: 2px solid #90caf9; }
        .role-admin       { background: #fce4ec; color: #880e4f; border: 2px solid #f48fb1; }

        /* ── TITRE ── */
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

        /* ── FILTRE ADMIN ── */
        .filter-box {
            background: white;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 2px 12px rgba(106,90,205,0.1);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .filter-box label {
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
        }

        .filter-box select {
            flex: 1;
            min-width: 220px;
            padding: 9px 16px;
            border: 2px solid #e0d8ff;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            color: #444;
            outline: none;
            cursor: pointer;
        }

        .filter-box select:focus { border-color: #6a5acd; }

        .btn-filter {
            padding: 9px 24px;
            background: linear-gradient(135deg, #6a5acd, #8a2be2);
            color: white;
            border: none;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn-filter:hover { opacity: 0.85; }

        .btn-reset {
            padding: 9px 20px;
            background: #f0f2f8;
            color: #666;
            border: 2px solid #ddd;
            border-radius: 50px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-reset:hover { background: #e0e0e0; }

        /* ── STATS ── */
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
        .stat-card.pink   { border-top-color: #e91e63; }
        .stat-card.pink   .number { color: #e91e63; }

        /* ── TABLEAU ── */
        .table-wrapper {
            background: white;
            border-radius: 20px;
            box-shadow: 0 2px 20px rgba(106,90,205,0.1);
            overflow: hidden;
        }

        table { width: 100%; border-collapse: collapse; }

        thead {
            background: linear-gradient(135deg, #6a5acd, #8a2be2);
        }

        thead th {
            color: white;
            padding: 16px 18px;
            text-align: left;
            font-weight: 600;
            font-size: 0.88rem;
            letter-spacing: 0.5px;
        }

        tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.2s;
        }

        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f8f6ff; }

        td {
            padding: 14px 18px;
            font-size: 0.88rem;
            color: #444;
        }

        /* ── BADGES ── */
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
        .badge-info     { background: #f3e5f5; color: #6a1b9a; }

        /* ── BOUTONS ── */
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

        .btn-details {
            display: inline-block;
            padding: 6px 16px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .btn-details:hover { opacity: 0.85; }

        .non-dispo { color: #ccc; font-size: 0.85rem; }

        /* ── VIDE ── */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state .icon { font-size: 3rem; margin-bottom: 16px; }
        .empty-state p { font-size: 1rem; }

        /* ── FOOTER ── */
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

<!-- HEADER -->
<header>
    <h1>EmpowerTech Training Center</h1>
    <nav>
        <a href="index.html">Home</a>
        <a href="service.html">Services</a>
        <a href="contact.html">Contact</a>
        <a href="historique.php" class="active">Historique</a>
    </nav>
</header>

<div class="container">

    <!-- BADGE ROLE -->
    <?php if ($role === 'participant'): ?>
        <div class="role-badge role-participant">👤 Espace Participant</div>
    <?php elseif ($role === 'formateur'): ?>
        <div class="role-badge role-formateur">🎓 Espace Formateur</div>
    <?php else: ?>
        <div class="role-badge role-admin">🔑 Espace Administrateur</div>
    <?php endif; ?>

    <!-- TITRE -->
    <h2 class="section-title">
        <?php
            if ($role === 'participant')    echo 'Mon Historique de Formations';
            elseif ($role === 'formateur') echo 'Mes Sessions Dispensées';
            else                           echo 'Historique Global des Sessions';
        ?>
    </h2>
    <div class="section-divider"></div>

    <!-- ════════════════════════════════ -->
    <!-- VUE PARTICIPANT                 -->
    <!-- ════════════════════════════════ -->
    <?php if ($role === 'participant'): ?>

        <?php if (empty($historique)): ?>
            <div class="table-wrapper">
                <div class="empty-state">
                    <div class="icon">📋</div>
                    <p>Vous n'avez participé à aucune formation pour l'instant.</p>
                </div>
            </div>
        <?php else:
            $total        = count($historique);
            $confirmes    = count(array_filter($historique,
                fn($r) => $r['statut_inscription'] === 'confirmé'));
            $presents     = count(array_filter($historique,
                fn($r) => $r['statut_presence'] === 'present'));
            $attestations = count(array_filter($historique,
                fn($r) => $r['statut_inscription'] === 'confirmé'
                       && $r['statut_presence'] === 'present'));
        ?>
            <!-- STATS PARTICIPANT -->
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

            <!-- TABLEAU PARTICIPANT -->
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
                                       class="btn-attestation">
                                        📄 Télécharger
                                    </a>
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

    <!-- ════════════════════════════════ -->
    <!-- VUE FORMATEUR                   -->
    <!-- ════════════════════════════════ -->
    <?php elseif ($role === 'formateur'): ?>

        <?php if (empty($historique)): ?>
            <div class="table-wrapper">
                <div class="empty-state">
                    <div class="icon">📋</div>
                    <p>Vous n'avez dispensé aucune session pour l'instant.</p>
                </div>
            </div>
        <?php else:
            $total_sessions     = count($historique);
            $total_participants = array_sum(array_column($historique, 'nb_participants'));
        ?>
            <!-- STATS FORMATEUR -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?= $total_sessions ?></div>
                    <div class="label">Sessions dispensées</div>
                </div>
                <div class="stat-card green">
                    <div class="number"><?= $total_participants ?></div>
                    <div class="label">Total participants formés</div>
                </div>
            </div>

            <!-- TABLEAU FORMATEUR -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Titre de la session</th>
                            <th>Date session</th>
                            <th>Durée</th>
                            <th>Participants</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($historique as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($row['titre_session']) ?></strong></td>
                            <td><?= $row['date_session'] ?? '–' ?></td>
                            <td><?= htmlspecialchars($row['duree']) ?> h</td>
                            <td>
                                <span class="badge badge-info">
                                    👥 <?= $row['nb_participants'] ?>
                                </span>
                            </td>
                            <td>
                                <a href="liste_participants.php?id_session=<?= $row['id_session'] ?>"
                                   class="btn-details">
                                    👁 Voir participants
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    <!-- ════════════════════════════════ -->
    <!-- VUE ADMIN                       -->
    <!-- ════════════════════════════════ -->
    <?php elseif ($role === 'admin'): ?>

        <!-- FILTRE SESSION -->
        <div class="filter-box">
            <label>Filtrer par session :</label>
            <form method="GET" style="display:flex;gap:12px;flex:1;flex-wrap:wrap;align-items:center">
                <select name="id_session">
                    <option value="">-- Toutes les sessions --</option>
                    <?php foreach ($all_sessions as $s): ?>
                        <option value="<?= $s['id'] ?>"
                            <?= ($filtre_session == $s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['titre']) ?> — <?= $s['date_session'] ?? '–' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-filter">🔍 Filtrer</button>
                <a href="historique.php" class="btn-reset">✖ Réinitialiser</a>
            </form>
        </div>

        <?php if (empty($historique)): ?>
            <div class="table-wrapper">
                <div class="empty-state">
                    <div class="icon">📋</div>
                    <p>Aucune session trouvée.</p>
                </div>
            </div>
        <?php else:
            $total_sessions  = count($historique);
            $total_inscrits  = array_sum(array_column($historique, 'nb_inscrits'));
            $total_confirmes = array_sum(array_column($historique, 'nb_confirmes'));
            $total_presents  = array_sum(array_column($historique, 'nb_presents'));
            $total_payes     = array_sum(array_column($historique, 'nb_payes'));
        ?>
            <!-- STATS ADMIN -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number"><?= $total_sessions ?></div>
                    <div class="label">Sessions totales</div>
                </div>
                <div class="stat-card blue">
                    <div class="number"><?= $total_inscrits ?></div>
                    <div class="label">Total inscrits</div>
                </div>
                <div class="stat-card green">
                    <div class="number"><?= $total_confirmes ?></div>
                    <div class="label">Confirmés</div>
                </div>
                <div class="stat-card orange">
                    <div class="number"><?= $total_presents ?></div>
                    <div class="label">Présents</div>
                </div>
                <div class="stat-card pink">
                    <div class="number"><?= $total_payes ?></div>
                    <div class="label">Paiements</div>
                </div>
            </div>

            <!-- TABLEAU ADMIN -->
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Session</th>
                            <th>Date</th>
                            <th>Durée</th>
                            <th>Inscrits</th>
                            <th>Confirmés</th>
                            <th>Présents</th>
                            <th>Paiements</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($historique as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><strong><?= htmlspecialchars($row['titre_session']) ?></strong></td>
                            <td><?= $row['date_session'] ?? '–' ?></td>
                            <td><?= htmlspecialchars($row['duree']) ?> h</td>
                            <td><span class="badge badge-info">👥 <?= $row['nb_inscrits'] ?></span></td>
                            <td><span class="badge badge-confirme">✔ <?= $row['nb_confirmes'] ?></span></td>
                            <td><span class="badge badge-present">📍 <?= $row['nb_presents'] ?></span></td>
                            <td><span class="badge badge-encours">💳 <?= $row['nb_payes'] ?></span></td>
                            <td>
                                <a href="liste_participants.php?id_session=<?= $row['id_session'] ?>"
                                   class="btn-details">
                                    👁 Détails
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<footer>
    &copy; 2025 EmpowerTech Training Center. Tous droits réservés.
</footer>

<?php mysqli_close($con); ?>
</body>
</html>