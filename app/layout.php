<?php

date_default_timezone_set('Asia/Kolkata');

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function valid_date(string $value): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d !== false && $d->format('Y-m-d') === $value;
}

function layout_start(string $title, string $active): void
{
    $items = [
        'dashboard' => ['Dashboard', 'index.php'],
        'projects'  => ['Projects', 'index.php#projects'],
        'tasks'     => ['My Tasks', 'tasks.php'],
        'calendar'  => ['Calendar', 'calendar.php'],
        'settings'  => ['Settings', '#'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow Pro | <?= h($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/pages.css">
</head>
<body>
    <aside class="sidebar">
        <h2 class="logo">TaskFlow<span>Pro</span></h2>
        <nav>
            <?php foreach ($items as $key => [$label, $href]): ?>
                <a href="<?= h($href) ?>"<?= $key === $active ? ' class="active"' : '' ?>><?= h($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">Project Management</div>
    </aside>
    <main class="main-content">
<?php
}

function layout_end(): void
{
    echo "    </main>\n</body>\n</html>\n";
}