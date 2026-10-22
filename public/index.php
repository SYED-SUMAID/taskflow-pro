```php
<?php

require_once __DIR__ . '/../app/config/database.php';

try {
    $projectCount = $pdo->query(
        "SELECT COUNT(*) FROM projects"
    )->fetchColumn();

    $taskCount = $pdo->query(
        "SELECT COUNT(*) FROM tasks"
    )->fetchColumn();

    $completedCount = $pdo->query(
        "SELECT COUNT(*) FROM tasks WHERE status = 'completed'"
    )->fetchColumn();

    $pendingCount = $pdo->query(
        "SELECT COUNT(*) FROM tasks WHERE status != 'completed'"
    )->fetchColumn();

} catch (PDOException $e) {
    error_log("Dashboard query failed: " . $e->getMessage());
    http_response_code(500);
    exit("Unable to load the dashboard.");
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
            <a href="#" class="active">Dashboard</a>
            <a href="#">Projects</a>
            <a href="#">My Tasks</a>
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
                <p class="subtitle">Manage your projects and stay productive.</p>
            </div>

            <button class="primary-button" id="newProjectBtn">
                + New Project
            </button>
        </header>

        <section class="stats-grid">

            <article class="stat-card">
                <p>Total Projects</p>
                <h2><?= (int) $projectCount ?></h2>
                <span>Projects in your workspace</span>
            </article>

            <article class="stat-card">
                <p>Total Tasks</p>
                <h2><?= (int) $taskCount ?></h2>
                <span>Tasks across all projects</span>
            </article>

            <article class="stat-card">
                <p>Completed Tasks</p>
                <h2><?= (int) $completedCount ?></h2>
                <span>Successfully completed</span>
            </article>

            <article class="stat-card">
                <p>Pending Tasks</p>
                <h2><?= (int) $pendingCount ?></h2>
                <span>Tasks left to complete</span>
            </article>

        </section>

        <section class="content-card">
            <div class="section-heading">
                <div>
                    <h2>Welcome to TaskFlow Pro</h2>
                    <p>Your workspace