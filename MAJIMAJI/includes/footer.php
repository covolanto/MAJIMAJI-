<?php
// Get message if exists
$message = getMessage();
?>
<!-- Message Notification -->
<?php if ($message): ?>
    <div class="notification-container">
        <div class="notification <?php echo $message['type']; ?>" id="notification">
            <div class="notification-content">
                <i class="fas <?php echo $message['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <span><?php echo $message['message']; ?></span>
            </div>
            <button class="notification-close" onclick="closeNotification()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content End -->
</div>

<!-- Footer -->
<footer class="main-footer">
    <p>&copy; <?php echo date('Y'); ?> AquaBill Water Billing System. All rights reserved.</p>
</footer>
</div>

<!-- JavaScript -->
<script src="../js/main.js"></script>
</body>

</html>