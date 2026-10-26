<?php

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/layout.php';

$error = '';
$filters = ['open' => 'Open', 'completed' => 'Completed', 'all' => 'All'];
$status = $_GET['status'] ?? 'open';
if (!isset($filters[$status])) {
    $status = 'open';
}

try {
    $projects = $pdo->query('SELECT id, name, color FROM projects ORDER BY name')->fetchAll();
} catch (PDOException $e) {
    error_log('Task page projects query failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load tasks.');
}
$projectIds = array_map('intval', array_column($projects, 'id'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $back = $_POST['status'] ?? 'open';
    if (!isset($filters[$back])) {
        $back = 'open';
    }

    try {
        if ($action === 'add') {
            $title = trim($_POST['title'] ?? '');
            $projectId = (int) ($_POST['project_id'] ?? 0);
            $priority = $_POST['priority'] ?? 'medium';
            $due = trim($_POST['due_date'] ?? '');

            if ($title === '') {
                $error = 'Enter a task title.';
            } elseif (mb_strlen($title) > 255) {
                $error = 'Task title must not exceed 255 characters.';
            } elseif (!in_array($projectId, $projectIds, true)) {
                $error = 'Choose a project for this task.';
            } elseif (!in_array($priority, ['low', 'medium', 'high'], true)) {
                $error = 'Choose a valid priority.';
            } elseif ($due !== '' && !valid_date($due)) {
                $error = 'Enter a valid due date.';
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO tasks (project_id, title, priority, due_date)
                     VALUES (:project_id, :title, :priority, :due_date)'
                );
                $stmt->execute([
                    'project_id' => $projectId,
                    'title' => $title,
                    'priority' => $priority,
                    'due_date' => $due !== '' ? $due : null,
                ]);
                header('Location: tasks.php?added=1&status=' . $back);
                exit;
            }
        } elseif ($action === 'toggle') {
            $stmt = $pdo->prepare(
                "UPDATE tasks
                 SET status = CASE WHEN status = 'completed' THEN 'todo' ELSE 'completed' END,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id"
            );
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            header('Location: tasks.php?status=' . $back);
            exit;
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
            $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
            header('Location: tasks.php?deleted=1&status=' . $back);
            exit;
        }
    } catch (PDOException $e) {
        error_log('Task action failed: ' . $e->getMessage());
        $error = 'Something went wrong. Try again.';
    }
}

$where = '';
if ($status === 'open') {
    $where = "WHERE t.status != 'completed'";
} elseif ($status === 'completed') {
    $where = "WHERE t.status = 'completed'";
}

try {
    $tasks = $pdo->query(
        "SELECT t.id, t.title, t.status, t.priority, t.due_date, p.name AS project_name, p.color
         FROM tasks t
         JOIN projects p ON p.id = t.project_id
         $where
         ORDER BY (t.status = 'completed'), t.due_date ASC NULLS LAST, t.id DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Task list query failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to load tasks.');
}

$today = date('Y-m-d');
$defaultDue = isset($_GET['due']) && valid_date($_GET['due']) ? $_GET['due'] : '';

$notice = '';
if (isset($_GET['added'])) {
    $notice = 'Task added.';
} elseif (isset($_GET['deleted'])) {
    $notice = 'Task deleted.';
}

layout_start('My Tasks', 'tasks');
?>
        <header class="topbar">
            <div>
                <h1>My tasks</h1>
                <p class="subtitle">Add tasks to your projects and tick them off.</p>
            </div>
        </header>

        <?php if ($error !== ''): ?>
            <div class="notice error-notice"><?= h($error) ?></div>
        <?php endif; ?>
        <?php if ($notice !== ''): ?>
            <div class="notice success-notice"><?= h($notice) ?></div>
        <?php endif; ?>

        <section class="panel" id="add">
            <?php if (count($projects) === 0): ?>
                <p class="muted">Create a project on the dashboard first, then add tasks to it.</p>
            <?php else: ?>
                <form method="POST" action="tasks.php" class="task-form">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="status" value="<?= h($status) ?>">

                    <input type="text" name="title" maxlength="255" placeholder="Record the weekly report" aria-label="Task title" required>

                    <select name="project_id" aria-label="Project" required>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= (int) $p['id'] ?>"><?= h($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="priority" aria-label="Priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>

                    <input type="date" name="due_date" value="<?= h($defaultDue) ?>" aria-label="Due date">

                    <button type="submit" class="primary-button">Add task</button>
                </form>
            <?php endif; ?>
        </section>

        <nav class="filters" aria-label="Task filter">
            <?php foreach ($filters as $key => $label): ?>
                <a href="tasks.php?status=<?= h($key) ?>"<?= $key === $status ? ' class="on"' : '' ?>><?= h($label) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if (count($tasks) === 0): ?>
            <div class="empty-state">
                <h3>No tasks here</h3>
                <p>Add a task above and it will show up in this list.</p>
            </div>
        <?php else: ?>
            <div class="task-list">
                <?php foreach ($tasks as $t):
                    $done = $t['status'] === 'completed';
                    $overdue = !$done && $t['due_date'] !== null && $t['due_date'] < $today;
                ?>
                    <div class="task-row<?= $done ? ' done' : '' ?>">
                        <form method="POST" action="tasks.php">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                            <input type="hidden" name="status" value="<?= h($status) ?>">
                            <button type="submit" class="check" aria-label="<?= $done ? 'Mark as not done' : 'Mark as done' ?>"><?= $done ? '&#10003;' : '' ?></button>
                        </form>

                        <div class="task-main">
                            <span class="task-title"><?= h($t['title']) ?></span>
                            <span class="task-project"><i style="background: <?= h($t['color']) ?>"></i><?= h($t['project_name']) ?></span>
                        </div>

                        <span class="badge p-<?= h($t['priority']) ?>"><?= h(ucfirst($t['priority'])) ?></span>

                        <span class="task-due<?= $overdue ? ' overdue' : '' ?>">
                            <?= $t['due_date'] ? h(date('j M', strtotime($t['due_date']))) : 'No date' ?>
                        </span>

                        <form method="POST" action="tasks.php" onsubmit="return confirm('Delete this task?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
                            <input type="hidden" name="status" value="<?= h($status) ?>">
                            <button type="submit" class="del" aria-label="Delete task">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
<?php layout_end(); ?>