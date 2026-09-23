
<?php

$host     = getenv('PGHOST');
$port     = getenv('PGPORT') ?: '5432';
$dbname   = getenv('PGDATABASE');
$user     = getenv('PGUSER');
$password = getenv('PGPASSWORD');

$connection_string = sprintf(
    "host=%s port=%s dbname=%s user=%s password=%s",
    $host,
    $port,
    $dbname,
    $user,
    $password
);

$db = @pg_connect($connection_string);

$database_error = false;
$leaderboard = [];
$total_students = 0;
$total_completed = 0;
$total_labs = 0;

if (!$db) {
    $database_error = true;
} else {

    /*
     * Pull the leaderboard directly from PostgreSQL.
     * The view already calculates labs_remaining and commentary.
     */
    $query = "
        SELECT
            ranking,
            name,
            labs_complete,
            total_labs,
            labs_remaining,
            commentary
        FROM verventech_leaderboard
        ORDER BY ranking NULLS LAST
    ";

    $result = pg_query($db, $query);

    if ($result) {

        while ($row = pg_fetch_assoc($result)) {
            $leaderboard[] = $row;

            $total_students++;

            if ($row['labs_complete'] !== null) {
                $total_completed += (int) $row['labs_complete'];
            }

            $total_labs += (int) $row['total_labs'];
        }
    }
}

$overall_progress = $total_labs > 0
    ? round(($total_completed / $total_labs) * 100)
    : 0;

?>

<!DOCTYPE html>

<html lang="en">

<head>

```
<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Verventech // Lab Command Center</title>

<style>

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        min-height: 100vh;
        font-family:
            Inter,
            system-ui,
            -apple-system,
            BlinkMacSystemFont,
            "Segoe UI",
            sans-serif;

        color: #f5f7ff;

        background:
            radial-gradient(
                circle at 15% 20%,
                rgba(111, 66, 193, 0.25),
                transparent 30%
            ),
            radial-gradient(
                circle at 85% 80%,
                rgba(0, 212, 255, 0.18),
                transparent 30%
            ),
            #070912;

        overflow-x: hidden;
    }

    body::before {
        content: "";
        position: fixed;
        inset: 0;

        background-image:
            linear-gradient(
                rgba(255,255,255,0.025) 1px,
                transparent 1px
            ),
            linear-gradient(
                90deg,
                rgba(255,255,255,0.025) 1px,
                transparent 1px
            );

        background-size: 45px 45px;

        pointer-events: none;

        mask-image: linear-gradient(
            to bottom,
            black,
            transparent
        );
    }

    .orb {
        position: fixed;
        width: 250px;
        height: 250px;

        border-radius: 50%;

        filter: blur(80px);

        opacity: 0.25;

        pointer-events: none;

        animation: drift 12s ease-in-out infinite alternate;
    }

    .orb.one {
        background: #7c3aed;
        top: 10%;
        left: -100px;
    }

    .orb.two {
        background: #06b6d4;
        bottom: -80px;
        right: -80px;
        animation-delay: -5s;
    }

    @keyframes drift {
        from {
            transform: translate(0, 0) scale(1);
        }

        to {
            transform: translate(80px, -50px) scale(1.25);
        }
    }

    .container {
        position: relative;
        width: min(1150px, 92%);
        margin: auto;
        padding: 55px 0 80px;
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 30px;

        margin-bottom: 45px;

        animation: reveal 0.8s ease both;
    }

    .eyebrow {
        color: #8b5cf6;
        font-size: 0.75rem;
        letter-spacing: 0.25em;
        text-transform: uppercase;
        font-weight: 800;
    }

    h1 {
        margin-top: 10px;

        font-size: clamp(2.3rem, 6vw, 5rem);
        line-height: 0.95;

        letter-spacing: -0.06em;

        background:
            linear-gradient(
                90deg,
                #ffffff,
                #a78bfa,
                #67e8f9,
                #ffffff
            );

        background-size: 300% auto;

        color: transparent;
        background-clip: text;
        -webkit-background-clip: text;

        animation: shine 6s linear infinite;
    }

    @keyframes shine {
        to {
            background-position: 300% center;
        }
    }

    .subtitle {
        margin-top: 16px;
        max-width: 650px;

        color: #8d94aa;
        line-height: 1.7;
    }

    .status {
        display: flex;
        align-items: center;
        gap: 10px;

        padding: 10px 15px;

        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 999px;

        background: rgba(255,255,255,0.035);
        backdrop-filter: blur(15px);

        color: #a7f3d0;

        white-space: nowrap;
    }

    .status-dot {
        width: 9px;
        height: 9px;

        background: #34d399;
        border-radius: 50%;

        box-shadow:
            0 0 0 0 rgba(52,211,153,0.7);

        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        70% {
            box-shadow:
                0 0 0 10px rgba(52,211,153,0);
        }

        100% {
            box-shadow:
                0 0 0 0 rgba(52,211,153,0);
        }
    }

    .stats {
        display: grid;
        grid-template-columns:
            repeat(3, 1fr);

        gap: 18px;

        margin-bottom: 35px;
    }

    .stat {
        position: relative;

        padding: 25px;

        border:
            1px solid rgba(255,255,255,0.08);

        border-radius: 20px;

        background:
            linear-gradient(
                145deg,
                rgba(255,255,255,0.07),
                rgba(255,255,255,0.025)
            );

        backdrop-filter: blur(18px);

        overflow: hidden;

        animation: reveal 0.8s ease both;
    }

    .stat::after {
        content: "";

        position: absolute;

        width: 100px;
        height: 100px;

        background: rgba(139,92,246,0.12);

        border-radius: 50%;

        right: -40px;
        top: -40px;

        filter: blur(10px);
    }

    .stat-label {
        color: #858da4;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.12em;
    }

    .stat-value {
        margin-top: 10px;

        font-size: 2.2rem;
        font-weight: 800;
    }

    .progress-section {
        margin-bottom: 35px;

        padding: 28px;

        border-radius: 22px;

        background: rgba(255,255,255,0.035);

        border:
            1px solid rgba(255,255,255,0.08);
    }

    .progress-header {
        display: flex;
        justify-content: space-between;

        margin-bottom: 14px;
    }

    .progress-header span:first-child {
        color: #a3aac0;
    }

    .progress-header span:last-child {
        font-weight: 800;
    }

    .progress-track {
        height: 12px;

        border-radius: 999px;

        background: rgba(255,255,255,0.07);

        overflow: hidden;
    }

    .progress-fill {
        height: 100%;

        width: 0;

        border-radius: inherit;

        background:
            linear-gradient(
                90deg,
                #7c3aed,
                #06b6d4
            );

        box-shadow:
            0 0 25px rgba(124,58,237,0.5);

        animation:
            loadProgress 1.8s
            cubic-bezier(.22,1,.36,1)
            forwards;
    }

    @keyframes loadProgress {
        to {
            width: <?= $overall_progress ?>%;
        }
    }

    .leaderboard-title {
        margin-bottom: 18px;

        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .leaderboard-title h2 {
        font-size: 1.5rem;
    }

    .leaderboard-title span {
        color: #666d82;
        font-size: 0.85rem;
    }

    .leaderboard {
        display: grid;
        gap: 14px;
    }

    .player {
        position: relative;

        display: grid;

        grid-template-columns:
            70px
            minmax(150px, 1fr)
            170px
            180px;

        align-items: center;

        gap: 20px;

        padding: 18px 22px;

        border:
            1px solid rgba(255,255,255,0.07);

        border-radius: 18px;

        background:
            rgba(255,255,255,0.035);

        backdrop-filter: blur(15px);

        transition:
            transform 0.3s ease,
            border-color 0.3s ease,
            background 0.3s ease;

        animation: slideIn 0.7s ease both;
    }

    .player:hover {
        transform: translateX(8px);

        border-color:
            rgba(139,92,246,0.45);

        background:
            rgba(139,92,246,0.08);
    }

    .rank {
        font-size: 1.5rem;
        font-weight: 900;

        color: #a78bfa;
    }

    .name {
        font-size: 1.05rem;
        font-weight: 750;
    }

    .labs {
        color: #8e96aa;
        font-size: 0.85rem;
    }

    .mini-track {
        margin-top: 8px;

        height: 6px;

        background: rgba(255,255,255,0.06);

        border-radius: 999px;

        overflow: hidden;
    }

    .mini-fill {
        height: 100%;

        width: 0;

        background:
            linear-gradient(
                90deg,
                #8b5cf6,
                #22d3ee
            );

        animation:
            miniLoad 1.4s
            cubic-bezier(.22,1,.36,1)
            forwards;
    }

    @keyframes miniLoad {
        to {
            width: var(--progress);
        }
    }

    .commentary {
        color: #9ca3b8;

        font-size: 0.82rem;

        text-align: right;
    }

    .error {
        padding: 25px;

        border:
            1px solid rgba(248,113,113,0.25);

        border-radius: 18px;

        background:
            rgba(127,29,29,0.15);

        color: #fca5a5;
    }

    footer {
        margin-top: 55px;

        text-align: center;

        color: #555c70;

        font-size: 0.75rem;

        letter-spacing: 0.08em;
    }

    @keyframes reveal {
        from {
            opacity: 0;
            transform: translateY(25px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @media (max-width: 800px) {

        .header {
            align-items: flex-start;
            flex-direction: column;
        }

        .stats {
            grid-template-columns: 1fr;
        }

        .player {
            grid-template-columns:
                55px
                1fr;

            gap: 10px;
        }

        .labs,
        .commentary {
            grid-column: 2;
            text-align: left;
        }

    }

</style>
```

</head>

<body>

```
<div class="orb one"></div>
<div class="orb two"></div>

<main class="container">

    <header class="header">

        <div>

            <div class="eyebrow">
                Verventech // Laboratory Network
            </div>

            <h1>
                LAB<br>
                COMMAND CENTER
            </h1>

            <p class="subtitle">
                A live PostgreSQL-powered scoreboard tracking
                every completed lab, every remaining challenge,
                and every contender climbing the Verventech ranks.
            </p>

        </div>

        <div class="status">

            <span class="status-dot"></span>

            <?= $database_error
                ? 'DATABASE OFFLINE'
                : 'DATABASE ONLINE'
            ?>

        </div>

    </header>


    <?php if ($database_error): ?>

        <div class="error">

            <strong>Database connection failed.</strong>

            <p style="margin-top:8px;">
                Check PostgreSQL, Docker Compose, and the
                PG* environment variables.
            </p>

        </div>

    <?php else: ?>


        <section class="stats">

            <div class="stat">

                <div class="stat-label">
                    Active Operatives
                </div>

                <div class="stat-value">
                    <?= $total_students ?>
                </div>

            </div>


            <div class="stat">

                <div class="stat-label">
                    Labs Completed
                </div>

                <div class="stat-value">
                    <?= $total_completed ?>
                </div>

            </div>


            <div class="stat">

                <div class="stat-label">
                    Network Progress
                </div>

                <div class="stat-value">
                    <?= $overall_progress ?>%
                </div>

            </div>

        </section>


        <section class="progress-section">

            <div class="progress-header">

                <span>
                    Overall laboratory progression
                </span>

                <span>
                    <?= $total_completed ?>
                    /
                    <?= $total_labs ?>
                </span>

            </div>

            <div class="progress-track">

                <div class="progress-fill"></div>

            </div>

        </section>


        <section>

            <div class="leaderboard-title">

                <h2>
                    Verventech Leaderboard
                </h2>

                <span>
                    LIVE DATABASE FEED
                </span>

            </div>


            <div class="leaderboard">

                <?php foreach ($leaderboard as $index => $player): ?>

                    <?php

                    $completed = $player['labs_complete'];

                    $total = (int) $player['total_labs'];

                    $progress = ($completed !== null && $total > 0)
                        ? round(($completed / $total) * 100)
                        : 0;

                    $rank = $player['ranking'];

                    ?>

                    <article
                        class="player"
                        style="animation-delay:
                            <?= $index * 0.12 ?>s;"
                    >

                        <div class="rank">

                            <?php
                            if ($rank == 1) {
                                echo '01';
                            } elseif ($rank == 2) {
                                echo '02';
                            } elseif ($rank == 3) {
                                echo '03';
                            } elseif ($rank == 4) {
                                echo '04';
                            } else {
                                echo '--';
                            }
                            ?>

                        </div>


                        <div>

                            <div class="name">
                                <?= htmlspecialchars($player['name']) ?>
                            </div>

                            <div class="mini-track">

                                <div
                                    class="mini-fill"
                                    style="
                                        --progress:
                                        <?= $progress ?>%;
                                        animation-delay:
                                        <?= 0.4 + ($index * 0.12) ?>s;
                                    "
                                ></div>

                            </div>

                        </div>


                        <div class="labs">

                            <?php if ($completed === null): ?>

                                Labs:
                                <strong>UNKNOWN</strong>

                            <?php else: ?>

                                Labs:
                                <strong>
                                    <?= $completed ?>
                                </strong>
                                /
                                <?= $total ?>

                                <br>

                                Remaining:
                                <strong>
                                    <?= $player['labs_remaining'] ?>
                                </strong>

                            <?php endif; ?>

                        </div>


                        <div class="commentary">

                            <?= htmlspecialchars(
                                $player['commentary']
                            ) ?>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        </section>

    <?php endif; ?>


    <footer>

        Verventech Laboratory Network
        //
        PostgreSQL
        //
        PHP
        //
        Dockerized

    </footer>

</main>
```

</body>

</html>

