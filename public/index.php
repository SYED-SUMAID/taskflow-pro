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

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <aside class="sidebar">
        <h2 class="logo">TaskFlow<span>Pro</span></h2>

        <nav>
            <a href="index.php" class="active">Dashboard</a>
            <a href="#projects">Projects</a>
            <a href="#tasks">My Tasks</a>
            <a href="#">Calendar</a>
            <a href="#">Settings</a>
        </nav>

        <div class="sidebar-footer">
            Project Management
        </div>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <p class="eyebrow">WORKSPACE</p>
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
                + New Project
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
                <p>Total Projects</p>
                <h2><?= $projectCount ?></h2>
                <span>Projects in your workspace</span>
            </article>

            <article class="stat-card">
                <p>Total Tasks</p>
                <h2><?= $taskCount ?></h2>
                <span>Tasks across all projects</span>
            </article>

            <article class="stat-card">
                <p>Completed Tasks</p>
                <h2><?= $completedCount ?></h2>
                <span>Successfully completed</span>
            </article>

            <article class="stat-card">
                <p>Pending Tasks</p>
                <h2><?= $pendingCount ?></h2>
                <span>Tasks left to complete</span>
            </article>

        </section>

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
                        Create Project
                    </button>
                </div>

            </form>
        </section>

        <section class="content-card projects-card" id="projects">
            <div class="section-heading">
                <h2>Your Projects</h2>
                <p>Your projects are saved in PostgreSQL.</p>
            </div>

            <?php if (count($projects) === 0): ?>

                <div class="empty-state">
                    <h3>No projects yet</h3>
                    <p>
                        Select New Project to create your first project.
                    </p>
                </div>

            <?php else: ?>

                <div class="projects-list">

                    <?php foreach ($projects as $project): ?>

                        <article class="project-item">
                            <div
                                class="project-color"
                                style="background-color: <?= escapeHtml($project['color']) ?>;"
                            ></div>

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
                                #<?= (int) $project['id'] ?>
                            </span>
                        </article>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>
        </section>

        <section id="tasks" class="content-card tasks-placeholder">
            <div class="section-heading">
                <h2>My Tasks</h2>
                <p>Task creation and management will be added next.</p>
            </div>
        </section>

    </main>

    <script src="assets/js/app.js"></script>

</body>
</html>
