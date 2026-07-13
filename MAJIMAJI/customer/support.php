<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Require customer login
requireCustomer();

$pageTitle = 'Support';
$customer = getCustomerByUserId(getUserId());

if (!$customer) {
    setMessage('Customer profile not found', 'error');
    redirect('../index.php');
}

// Handle new ticket form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_ticket'])) {
    $subject = sanitize($_POST['subject'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = sanitize($_POST['category'] ?? 'other');
    $priority = sanitize($_POST['priority'] ?? 'medium');

    $errors = [];

    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }

    if (empty($description)) {
        $errors[] = 'Description is required';
    }

    if (empty($errors)) {
        $result = createSupportTicket($customer['id'], $subject, $description, $category, $priority);

        if ($result['success']) {
            setMessage($result['message'], 'success');
            redirect('support.php');
        } else {
            $errors[] = $result['message'];
        }
    }
}

// Get customer's tickets
$tickets = getSupportTicketsByCustomer($customer['id']);
$openTickets = getCustomerOpenTicketsCount($customer['id']);
?>

<?php include '../includes/header.php'; ?>

<!-- Top Header -->
<header class="top-header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="page-title">Support</h1>
    </div>
    <div class="header-right">
        <div class="theme-toggle" id="themeToggle">
            <i class="fas fa-sun sun"></i>
            <i class="fas fa-moon moon"></i>
        </div>
    </div>
</header>

<!-- Main Content -->
<main class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1>Support Center</h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="document.getElementById('newTicketModal').style.display='block'">
                <i class="fas fa-plus"></i> New Ticket
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" style="margin-bottom: 1.5rem;">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Total Tickets</div>
                <div class="stat-value"><?php echo count($tickets); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-info">
                <div class="stat-label">Open Tickets</div>
                <div class="stat-value"><?php echo $openTickets; ?></div>
            </div>
        </div>
    </div>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            <i class="fas fa-exclamation-circle"></i>
            <div>
                <?php foreach ($errors as $error): ?>
                    <div><?php echo $error; ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tickets List -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> My Tickets</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-ticket-alt"></i>
                                    <h3>No support tickets</h3>
                                    <p>Create a new ticket if you need help</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td>#<?php echo $ticket['id']; ?></td>
                                <td><?php echo htmlspecialchars($ticket['subject']); ?></td>
                                <td><span class="badge badge-info"><?php echo ucfirst($ticket['category']); ?></span></td>
                                <td>
                                    <span class="badge badge-<?php
                                                                echo $ticket['priority'] === 'high' ? 'danger' : ($ticket['priority'] === 'medium' ? 'warning' : 'info');
                                                                ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php
                                                                echo $ticket['status'] === 'resolved' ? 'paid' : ($ticket['status'] === 'closed' ? 'info' : ($ticket['status'] === 'pending' ? 'warning' : 'pending'));
                                                                ?>">
                                        <?php echo ucfirst($ticket['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($ticket['created_at']); ?></td>
                                <td>
                                    <a href="view-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-sm btn-outline">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Contact Info -->
    <div class="card" style="margin-top: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-phone-alt"></i> Contact Information</h3>
        </div>
        <div style="padding: 1rem;">
            <p><strong>Phone:</strong> 1-800-AQUABILL</p>
            <p><strong>Email:</strong> support@aquabill.com</p>
            <p><strong>Hours:</strong> Mon-Fri 8am-5pm</p>
        </div>
    </div>
</main>

<!-- New Ticket Modal -->
<div id="newTicketModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="background: var(--bg-card); max-width: 500px; margin: 100px auto; padding: 2rem; border-radius: var(--radius-lg);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Create Support Ticket</h2>
            <button onclick="document.getElementById('newTicketModal').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST">
            <input type="hidden" name="create_ticket" value="1">

            <div class="form-group">
                <label class="form-label required">Subject</label>
                <input type="text" name="subject" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label required">Category</label>
                <select name="category" class="form-control">
                    <option value="billing">Billing</option>
                    <option value="meter">Meter</option>
                    <option value="payment">Payment</option>
                    <option value="account">Account</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label required">Priority</label>
                <select name="priority" class="form-control">
                    <option value="low">Low</option>
                    <option value="medium" selected>Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label required">Description</label>
                <textarea name="description" class="form-control" rows="5" required></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('newTicketModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/sidebar.php'; ?>
<?php include '../includes/footer.php'; ?>

<style>
    .badge-info {
        background: var(--info-light);
        color: var(--info);
    }
</style>

<script>
    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('click', function() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });

    // Menu Toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    menuToggle.addEventListener('click', function() {
        sidebar.classList.add('active');
    });

    const closeSidebar = document.getElementById('closeSidebar');
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
    }
</script>