<?php

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/layout.php';

$m = $_GET['m'] ?? date('Y-m');
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $m)) {
    $m = date('Y-m');
}

$first = new DateTimeImmutable($m . '-01');
$daysInMonth = (int) $first->format('t');
$offset = (int) $first->format('N') - 1;
$prev = $first->modify('-1 month')->format('Y-m');
$next = $first->modify('+1 month')->format('Y-m');
$today = date('Y-m-d');

try {
    $stmt = $pdo->prepare(
        'SELECT t.title, t.status, t.due_date, p.color
         FROM tasks t
         JOIN projects p ON p.id = t.project_id
         WHERE t.due_date BETWEEN :start AND :end
         ORDER BY (t.status = \'completed\'), t.id'
    );
    $stmt->execute([
        'start' => $first->format('Y-m-01'),
        'end' => $first->format('Y-m-t'),
    ]);
    $rows = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Calendar query failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load the calendar.');
}

$byDay = [];
foreach ($rows as $r) {
    $byDay[$r['due_date']][] = $r;
}

layout_start('Calendar', 'calendar');
?>
        <header class="topbar">
            <div>
                <h1><?= h($first->format('F Y')) ?></h1>
                <p class="subtitle">Tasks appear on the day they are due. Select a date to add one.</p>
            </div>
            <div class="cal-nav">
                <a class="secondary-button" href="calendar.php?m=<?= h($prev) ?>">Previous</a>
                <a class="secondary-button" href="calendar.php">Today</a>
                <a class="secondary-button" href="calendar.php?m=<?= h($next) ?>">Next</a>
            </div>
        </header>

        <div class="calendar">
            <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $d): ?>
                <div class="cal-head"><?= $d ?></div>
            <?php endforeach; ?>

            <?php for ($i = 0; $i < $offset; $i++): ?>
                <div class="cal-cell empty"></div>
            <?php endfor; ?>

            <?php for ($day = 1; $day <= $daysInMonth; $day++):
                $date = $first->format('Y-m-') . str_pad((string) $day, 2, '0', STR_PAD_LEFT);
                $items = $byDay[$date] ?? [];
            ?>
                <div class="cal-cell<?= $date === $today ? ' today' : '' ?>">
                    <a class="cal-date" href="tasks.php?due=<?= h($date) ?>#add" title="Add a task on this day"><?= $day ?></a>
                    <?php foreach (array_slice($items, 0, 3) as $it): ?>
                        <span class="cal-task<?= $it['status'] === 'completed' ? ' done' : '' ?>" style="--c: <?= h($it['color']) ?>" title="<?= h($it['title']) ?>"><?= h($it['title']) ?></span>
                    <?php endforeach; ?>
                    <?php if (count($items) > 3): ?>
                        <a class="cal-more" href="tasks.php?status=all">+<?= count($items) - 3 ?> more</a>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
<?php layout_end(); ?>