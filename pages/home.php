<?php
// Simple task management
// Session is already started in index.php
require_once __DIR__ . '/../config/auth.php';

// Ensure user is logged in
requireLogin();

// Initialize tasks array in session if not exists
if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_task'])) {
        $task = trim($_POST['task']);
        if (!empty($task)) {
            $_SESSION['tasks'][] = [
                'id' => uniqid(),
                'text' => htmlspecialchars($task),
                'completed' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
            header('Location: index.php');
            exit;
        }
    }
    
    if (isset($_POST['toggle_task'])) {
        $taskId = $_POST['task_id'];
        foreach ($_SESSION['tasks'] as &$task) {
            if ($task['id'] === $taskId) {
                $task['completed'] = !$task['completed'];
                break;
            }
        }
        header('Location: index.php');
        exit;
    }
    
    if (isset($_POST['delete_task'])) {
        $taskId = $_POST['task_id'];
        $_SESSION['tasks'] = array_filter($_SESSION['tasks'], function($task) use ($taskId) {
            return $task['id'] !== $taskId;
        });
        $_SESSION['tasks'] = array_values($_SESSION['tasks']); // Re-index array
        header('Location: index.php');
        exit;
    }
}
?>

<div class="page-content home-content">
    <h1 class="page-title">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p class="page-subtitle">Manage your tasks</p>

    <div class="task-section">
        <h2>Add New Task</h2>
        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="task" class="form-input" placeholder="Enter a new task..." required>
            </div>
            <button type="submit" name="add_task" class="btn">Add Task</button>
        </form>
    </div>

    <div class="task-section">
        <h2>Your Tasks (<?php echo count($_SESSION['tasks']); ?>)</h2>
        <?php if (empty($_SESSION['tasks'])): ?>
            <p class="no-tasks-message">No tasks yet. Add one above!</p>
        <?php else: ?>
            <ul class="task-list">
                <?php foreach ($_SESSION['tasks'] as $task): ?>
                    <li class="task-item <?php echo $task['completed'] ? 'completed' : ''; ?>">
                        <div>
                            <strong><?php echo $task['text']; ?></strong>
                            <small class="task-date">
                                Created: <?php echo $task['created_at']; ?>
                            </small>
                        </div>
                        <div class="task-actions">
                            <form method="POST" action="" class="inline-form">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" name="toggle_task" class="btn btn-small btn-secondary">
                                    <?php echo $task['completed'] ? 'Mark Incomplete' : 'Mark Complete'; ?>
                                </button>
                            </form>
                            <form method="POST" action="" class="inline-form">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <button type="submit" name="delete_task" class="btn btn-small btn-delete">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

