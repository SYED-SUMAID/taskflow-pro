<?php

require_once __DIR__ . '/../app/config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $error = 'Project name is required.';
    } elseif (strlen($name) > 120) {
        $error = 'Project name must not exceed 120 characters.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO projects (name, description)
                 VALUES (:name, :description)'
            );

            $stmt->execute([
                'name' => $name,
                'description' => $description !== '' ? $description : null,
            ]);

            header('Location: index.php?created=1');
            exit;

        } catch (PDOException $e) {
            error_log('Project creation failed: ' . $e->getMessage());
            $error = 'Unable to create the project. Please try again.';
        }
    }
}

if (isset($_GET['created'])) {
    $success = 'Project created successfully.';
}

try {
    $projectCount = (int) $pdo
        ->query('SELECT COUNT(*) FROM projects')
        ->fetchColumn();

    $taskCount = (int) $pdo
        ->query('SELECT COUNT(*) FROM tasks')
        ->fetchColumn();

    $completedCount = (int) $pdo
        ->query("SELECT COUNT(*) FROM tasks WHERE status = 'completed'")
        ->fetchColumn();

    $pendingCount = (int) $pdo
        ->query("SELECT COUNT(*) FROM tasks WHERE status != 'completed'")
        ->fetchColumn();

    $projects = $pdo->query(
        'SELECT id, name, description, color, created_at
         FROM projects
         ORDER BY id DESC'
    )->fetchAll();

    $upcoming = $pdo->query(
        "SELECT t.title, t.due_date, p.name AS project_name, p.color
         FROM tasks t
         JOIN projects p ON p.id = t.project_id
         WHERE t.status != 'completed'
         ORDER BY t.due_date ASC NULLS LAST, t.id DESC
         LIMIT 5"
    )->fetchAll();

} catch (PDOException $e) {
    error_log('Dashboard query failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load the dashboard.');
}

function escapeHtml($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>TaskFlow Pro | Dashboard</title>

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
            <a href="index.php" class="active">Dashboard</a>
            <a href="index.php#projects">Projects</a>
            <a href="tasks.php">My Tasks</a>
            <a href="calendar.php">Calendar</a>
            <a href="#">Settings</a>
        </nav>

        <div class="sidebar-footer">
            Project Management
        </div>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Dashboard</h1>
                <p class="subtitle">
                    Manage your projects and stay productive.
                </p>
            </div>

            <button
                class="primary-button"
                id="newProjectBtn"
                type="button"
            >
                New project
            </button>
        </header>

        <?php if ($error !== ''): ?>
            <div class="notice error-notice">
                <?= escapeHtml($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="notice success-notice">
                <?= escapeHtml($success) ?>
            </div>
        <?php endif; ?>

        <section class="stats-grid">

            <article class="stat-card">
                <p>Projects</p>
                <h2><?= $projectCount ?></h2>
                <span>In your workspace</span>
            </article>

            <article class="stat-card">
                <p>Tasks</p>
                <h2><?= $taskCount ?></h2>
                <span>Across all projects</span>
            </article>

            <article class="stat-card">
                <p>Completed</p>
                <h2><?= $completedCount ?></h2>
                <span>Done and dusted</span>
            </article>

            <article class="stat-card">
                <p>Pending</p>
                <h2><?= $pendingCount ?></h2>
                <span>Still to do</span>
            </article>

        </section>

        <?php if ($taskCount > 0): $pct = (int) round($completedCount / $taskCount * 100); ?>
            <div class="progress">
                <span><?= $pct ?>% complete</span>
                <div class="progress-track"><div class="progress-fill" style="width: <?= $pct ?>%"></div></div>
            </div>
        <?php endif; ?>

        <section class="content-card project-form-card" id="projectForm">
            <div class="section-heading">
                <h2>Create a project</h2>
                <p>Give your new project a name and description.</p>
            </div>

            <form method="POST" action="index.php" class="project-form">

                <label for="projectName">Project name</label>
                <input
                    type="text"
                    id="projectName"
                    name="name"
                    maxlength="120"
                    placeholder="e.g. Website Redesign"
                    required
                >

                <label for="projectDescription">Description</label>
                <textarea
                    id="projectDescription"
                    name="description"
                    rows="3"
                    placeholder="What is this project about?"
                ></textarea>

                <div class="form-actions">
                    <button
                        type="button"
                        class="secondary-button"
                        id="cancelProjectBtn"
                    >
                        Cancel
                    </button>

                    <button type="submit" class="primary-button">
                        Create project
                    </button>
                </div>

            </form>
        </section>

        <section class="content-card projects-card" id="projects">
            <div class="section-heading">
                <h2>Projects</h2>
                <p>Everything you are working on.</p>
            </div>

            <?php if (count($projects) === 0): ?>

                <div class="empty-state">
                    <h3>No projects yet</h3>
                    <p>
                        Select New project to add your first one.
                    </p>
                </div>

            <?php else: ?>

                <div class="projects-list">

                    <?php foreach ($projects as $project): ?>

                        <article class="project-item" style="--project-color: <?= escapeHtml($project['color']) ?>;">
                            <div
                                class="project-color"
                                style="background-color: <?= escapeHtml($project['color']) ?>;"
                            ><?= escapeHtml(mb_strtoupper(mb_substr($project['name'], 0, 1))) ?></div>

                            <div class="project-details">
                                <h3><?= escapeHtml($project['name']) ?></h3>

                                <p>
                                    <?php if (!empty($project['description'])): ?>
                                        <?= escapeHtml($project['description']) ?>
                                    <?php else: ?>
                                        No description provided.
                                    <?php endif; ?>
                                </p>
                            </div>

                            <span class="project-id">
                                #<?= (int) $project['id'] ?> · Created <?= escapeHtml(date('j M Y', strtotime($project['created_at']))) ?>
                            </span>
                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>
        </section>

        <section id="tasks" class="content-card tasks-placeholder">
            <div class="section-heading">
                <h2>Upcoming tasks</h2>
                <p><a href="tasks.php" style="color: var(--accent);">Manage all tasks</a></p>
            </div>

            <?php if (count($upcoming) === 0): ?>
                <div class="empty-state">
                    <h3>No open tasks</h3>
                    <p>Add your first task on the My Tasks page.</p>
                </div>
            <?php else: ?>
                <div class="task-list">
                    <?php foreach ($upcoming as $t): ?>
                        <div class="task-row" style="grid-template-columns: 1fr 90px;">
                            <div class="task-main">
                                <span class="task-title"><?= escapeHtml($t['title']) ?></span>
                                <span class="task-project"><i style="background: <?= escapeHtml($t['color']) ?>"></i><?= escapeHtml($t['project_name']) ?></span>
                            </div>
                            <span class="task-due"><?= $t['due_date'] ? escapeHtml(date('j M', strtotime($t['due_date']))) : 'No date' ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

    <script src="assets/js/app.js"></script>

</body>
</html>