<?php
// Function to calculate bill amount based on consumption
function calculateBill($consumption)
{
    $meterCharge = 50.00; // KSh 50 meter charge
    $total = 0;

    if ($consumption <= 10) {
        $total = $consumption * 2.50;
    } elseif ($consumption <= 30) {
        $total = (10 * 2.50) + (($consumption - 10) * 3.00);
    } elseif ($consumption <= 50) {
        $total = (10 * 2.50) + (20 * 3.00) + (($consumption - 30) * 3.50);
    } else {
        $total = (10 * 2.50) + (20 * 3.00) + (20 * 3.50) + (($consumption - 50) * 4.00);
    }

    return $total + $meterCharge;
}

// Function to get rate per cubic meter
function getRate($consumption)
{
    if ($consumption <= 10) {
        return 2.50;
    } elseif ($consumption <= 30) {
        return 3.00;
    } elseif ($consumption <= 50) {
        return 3.50;
    } else {
        return 4.00;
    }
}

// Function to format currency
function formatCurrency($amount)
{
    if ($amount === null || $amount === '') {
        return 'KSh 0.00';
    }
    return 'KSh ' . number_format((float)$amount, 2);
}

// Function to format date
function formatDate($date)
{
    return date('M d, Y', strtotime($date));
}

// Function to format datetime
function formatDateTime($date)
{
    return date('M d, Y h:i A', strtotime($date));
}

// Function to get billing month options
function getBillingMonths()
{
    $months = [];
    for ($i = 0; $i < 12; $i++) {
        $month = date('Y-m', strtotime("-$i months"));
        $months[$month] = date('F Y', strtotime($month));
    }
    return $months;
}

// Function to check if bill exists for customer and month
function billExists($customerId, $billingMonth)
{
    $sql = "SELECT id FROM bills WHERE customer_id = '$customerId' AND billing_month = '$billingMonth'";
    $result = getRow($sql);
    return $result !== null;
}

// Function to generate transaction ID
function generateTransactionId()
{
    return 'TXN' . date('YmdHis') . rand(1000, 9999);
}

// Function to get customer by user ID
function getCustomerByUserId($userId)
{
    $sql = "SELECT * FROM customers WHERE user_id = '$userId'";
    return getRow($sql);
}

// Function to get customer by ID
function getCustomerById($customerId)
{
    $sql = "SELECT c.*, u.email, u.username 
            FROM customers c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.id = '$customerId'";
    return getRow($sql);
}

// Function to get all customers
function getAllCustomers()
{
    $sql = "SELECT c.*, u.email, u.username 
            FROM customers c 
            JOIN users u ON c.user_id = u.id 
            ORDER BY c.created_at DESC";
    return getRows($sql);
}

// Function to search customers
function searchCustomers($search)
{
    $sql = "SELECT c.*, u.email, u.username 
            FROM customers c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.first_name LIKE '%$search%' 
            OR c.last_name LIKE '%$search%' 
            OR c.meter_number LIKE '%$search%' 
            OR u.email LIKE '%$search%'
            ORDER BY c.created_at DESC";
    return getRows($sql);
}

// Function to get bills by customer ID
function getBillsByCustomerId($customerId)
{
    $sql = "SELECT * FROM bills WHERE customer_id = '$customerId' ORDER BY billing_month DESC";
    return getRows($sql);
}

// Function to get all bills
function getAllBills()
{
    $sql = "SELECT b.*, c.first_name, c.last_name, c.meter_number 
            FROM bills b 
            JOIN customers c ON b.customer_id = c.id 
            ORDER BY b.created_at DESC";
    return getRows($sql);
}

// Function to search bills
function searchBills($search, $status = '', $month = '')
{
    $sql = "SELECT b.*, c.first_name, c.last_name, c.meter_number 
            FROM bills b 
            JOIN customers c ON b.customer_id = c.id 
            WHERE 1=1";

    if (!empty($search)) {
        $sql .= " AND (c.first_name LIKE '%$search%' 
                   OR c.last_name LIKE '%$search%' 
                   OR c.meter_number LIKE '%$search%')";
    }

    if (!empty($status)) {
        $sql .= " AND b.status = '$status'";
    }

    if (!empty($month)) {
        $sql .= " AND b.billing_month = '$month'";
    }

    $sql .= " ORDER BY b.created_at DESC";

    return getRows($sql);
}

// Function to get payments by customer ID
function getPaymentsByCustomerId($customerId)
{
    $sql = "SELECT p.*, b.billing_month, b.amount as bill_amount 
            FROM payments p 
            JOIN bills b ON p.bill_id = b.id 
            WHERE b.customer_id = '$customerId'
            ORDER BY p.payment_date DESC";
    return getRows($sql);
}

// Function to get all payments
function getAllPayments()
{
    $sql = "SELECT p.*, b.billing_month, b.amount as bill_amount, 
            c.first_name, c.last_name, c.meter_number
            FROM payments p 
            JOIN bills b ON p.bill_id = b.id 
            JOIN customers c ON b.customer_id = c.id
            ORDER BY p.payment_date DESC";
    return getRows($sql);
}

// Function to get dashboard statistics
function getDashboardStats()
{
    $stats = [];

    // Total customers
    $sql = "SELECT COUNT(*) as total FROM customers";
    $result = getRow($sql);
    $stats['total_customers'] = $result['total'] ?? 0;

    // Total bills
    $sql = "SELECT COUNT(*) as total FROM bills";
    $result = getRow($sql);
    $stats['total_bills'] = $result['total'] ?? 0;

    // Pending bills
    $sql = "SELECT COUNT(*) as total FROM bills WHERE status = 'pending'";
    $result = getRow($sql);
    $stats['pending_bills'] = $result['total'] ?? 0;

    // Overdue bills
    $sql = "SELECT COUNT(*) as total FROM bills WHERE status = 'overdue'";
    $result = getRow($sql);
    $stats['overdue_bills'] = $result['total'] ?? 0;

    // Paid bills
    $sql = "SELECT COUNT(*) as total FROM bills WHERE status = 'paid'";
    $result = getRow($sql);
    $stats['paid_bills'] = $result['total'] ?? 0;

    // Total revenue
    $sql = "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'";
    $result = getRow($sql);
    $stats['total_revenue'] = $result['total'] ?? 0;

    // This month revenue
    $thisMonth = date('Y-m');
    $sql = "SELECT SUM(amount) as total FROM payments 
            WHERE status = 'completed' 
            AND DATE_FORMAT(payment_date, '%Y-%m') = '$thisMonth'";
    $result = getRow($sql);
    $stats['this_month_revenue'] = $result['total'] ?? 0;

    // Outstanding amount
    $sql = "SELECT SUM(amount) as total FROM bills WHERE status IN ('pending', 'overdue')";
    $result = getRow($sql);
    $stats['outstanding'] = $result['total'] ?? 0;

    return $stats;
}

// Function to get monthly revenue for chart
function getMonthlyRevenue($months = 6)
{
    $data = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $sql = "SELECT SUM(amount) as total FROM payments 
                WHERE status = 'completed' 
                AND DATE_FORMAT(payment_date, '%Y-%m') = '$month'";
        $result = getRow($sql);
        $data[$month] = $result['total'] ?? 0;
    }
    return $data;
}

// Function to get monthly consumption for chart
function getMonthlyConsumption($months = 6)
{
    $data = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $sql = "SELECT SUM(consumption) as total FROM bills 
                WHERE billing_month = '$month'";
        $result = getRow($sql);
        $data[$month] = $result['total'] ?? 0;
    }
    return $data;
}

// Function to get recent payments
function getRecentPayments($limit = 10)
{
    $sql = "SELECT p.*, b.billing_month, c.first_name, c.last_name
            FROM payments p 
            JOIN bills b ON p.bill_id = b.id 
            JOIN customers c ON b.customer_id = c.id
            ORDER BY p.payment_date DESC LIMIT $limit";
    return getRows($sql);
}

// Function to get recent bills
function getRecentBills($limit = 10)
{
    $sql = "SELECT b.*, c.first_name, c.last_name, c.meter_number
            FROM bills b 
            JOIN customers c ON b.customer_id = c.id
            ORDER BY b.created_at DESC LIMIT $limit";
    return getRows($sql);
}

// Function to get recent customers
function getRecentCustomers($limit = 10)
{
    $sql = "SELECT c.*, u.email, u.username 
            FROM customers c 
            JOIN users u ON c.user_id = u.id 
            ORDER BY c.created_at DESC LIMIT $limit";
    return getRows($sql);
}

// Function to get average consumption
function getAverageConsumption()
{
    $sql = "SELECT AVG(consumption) as avg FROM bills";
    $result = getRow($sql);
    return round($result['avg'] ?? 0, 1);
}

// Function to get collection rate
function getCollectionRate()
{
    $sql = "SELECT 
                (SELECT SUM(amount) FROM bills WHERE status = 'paid') as paid,
                (SELECT SUM(amount) FROM bills) as total";
    $result = getRow($sql);
    $paid = $result['paid'] ?? 0;
    $total = $result['total'] ?? 1;
    return round(($paid / $total) * 100, 1);
}

// Function to get top customers by revenue
function getTopCustomersByRevenue($limit = 10)
{
    $sql = "SELECT c.id, c.first_name, c.last_name, c.meter_number,
                   SUM(p.amount) as total_paid
            FROM customers c
            JOIN bills b ON c.id = b.customer_id
            JOIN payments p ON b.id = p.bill_id
            WHERE p.status = 'completed'
            GROUP BY c.id
            ORDER BY total_paid DESC
            LIMIT $limit";
    return getRows($sql);
}

// ==========================================
// ADDITIONAL FEATURES FUNCTIONS
// ==========================================

// Function to log activity
function logActivity($userId, $action, $details = '')
{
    global $conn;

    // Check if activity_logs table exists
    $result = $conn->query("SHOW TABLES LIKE 'activity_logs'");
    if ($result->num_rows == 0) {
        return false; // Table doesn't exist, skip logging
    }

    $sql = "INSERT INTO activity_logs (user_id, action, details, ip_address) 
            VALUES ('$userId', '$action', '$details', '" . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "')";
    return query($sql);
}

// Function to get activity logs
function getActivityLogs($limit = 50)
{
    global $conn;

    // Check if activity_logs table exists
    $result = $conn->query("SHOW TABLES LIKE 'activity_logs'");
    if ($result->num_rows == 0) {
        return []; // Table doesn't exist, return empty
    }

    $sql = "SELECT al.*, u.username, u.role 
            FROM activity_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            ORDER BY al.created_at DESC 
            LIMIT $limit";
    return getRows($sql);
}

// Function to get admin activity logs
function getAdminActivityLogs($limit = 100)
{
    global $conn;

    // Check if activity_logs table exists
    $result = $conn->query("SHOW TABLES LIKE 'activity_logs'");
    if ($result->num_rows == 0) {
        return []; // Table doesn't exist, return empty
    }

    $sql = "SELECT al.*, u.username 
            FROM activity_logs al 
            JOIN users u ON al.user_id = u.id 
            WHERE u.role = 'admin'
            ORDER BY al.created_at DESC 
            LIMIT $limit";
    return getRows($sql);
}

// Function to get customer activity logs
function getCustomerActivityLogs($customerId, $limit = 20)
{
    global $conn;

    // Check if activity_logs table exists
    $result = $conn->query("SHOW TABLES LIKE 'activity_logs'");
    if ($result->num_rows == 0) {
        return []; // Table doesn't exist, return empty
    }

    $sql = "SELECT al.* 
            FROM activity_logs al 
            JOIN users u ON al.user_id = u.id
            JOIN customers c ON u.id = c.user_id
            WHERE c.id = '$customerId'
            ORDER BY al.created_at DESC 
            LIMIT $limit";
    return getRows($sql);
}

// Function to change password
function changePassword($userId, $currentPassword, $newPassword)
{
    $sql = "SELECT password FROM users WHERE id = '$userId'";
    $user = getRow($sql);

    if (!$user) {
        return ['success' => false, 'message' => 'User not found'];
    }

    if (!verifyPassword($currentPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }

    $newHash = hashPassword($newPassword);
    $sql = "UPDATE users SET password = '$newHash' WHERE id = '$userId'";

    if (query($sql)) {
        return ['success' => true, 'message' => 'Password changed successfully'];
    }

    return ['success' => false, 'message' => 'Failed to change password'];
}

// Function to update admin profile
function updateAdminProfile($userId, $username, $email)
{
    $sql = "UPDATE users SET username = '$username', email = '$email' WHERE id = '$userId'";
    return query($sql);
}

// Function to get admin by user ID
function getAdminByUserId($userId)
{
    $sql = "SELECT * FROM users WHERE id = '$userId' AND role = 'admin'";
    return getRow($sql);
}

// Function to get customer usage statistics
function getCustomerUsageStats($customerId)
{
    $stats = [];

    // Total consumption
    $sql = "SELECT SUM(consumption) as total FROM bills WHERE customer_id = '$customerId'";
    $result = getRow($sql);
    $stats['total_consumption'] = $result['total'] ?? 0;

    // Average monthly consumption
    $sql = "SELECT AVG(consumption) as avg FROM bills WHERE customer_id = '$customerId'";
    $result = getRow($sql);
    $stats['avg_consumption'] = round($result['avg'] ?? 0, 1);

    // Total spent
    $sql = "SELECT SUM(b.amount) as total FROM bills b WHERE b.customer_id = '$customerId'";
    $result = getRow($sql);
    $stats['total_spent'] = $result['total'] ?? 0;

    // Total paid
    $sql = "SELECT SUM(p.amount) as total FROM payments p 
            JOIN bills b ON p.bill_id = b.id 
            WHERE b.customer_id = '$customerId' AND p.status = 'completed'";
    $result = getRow($sql);
    $stats['total_paid'] = $result['total'] ?? 0;

    // Outstanding
    $stats['outstanding'] = $stats['total_spent'] - $stats['total_paid'];

    // Bill count
    $sql = "SELECT COUNT(*) as count FROM bills WHERE customer_id = '$customerId'";
    $result = getRow($sql);
    $stats['bill_count'] = $result['count'] ?? 0;

    return $stats;
}

// Function to get monthly consumption for customer chart
function getCustomerMonthlyConsumption($customerId, $months = 12)
{
    $data = [];
    for ($i = $months - 1; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $sql = "SELECT consumption, amount FROM bills 
                WHERE customer_id = '$customerId' AND billing_month = '$month'";
        $result = getRow($sql);
        $data[$month] = [
            'consumption' => $result['consumption'] ?? 0,
            'amount' => $result['amount'] ?? 0
        ];
    }
    return $data;
}

// Function to get payment by ID
function getPaymentById($paymentId)
{
    $sql = "SELECT p.*, b.billing_month, b.amount as bill_amount, b.consumption,
            c.first_name, c.last_name, c.meter_number, c.address
            FROM payments p 
            JOIN bills b ON p.bill_id = b.id 
            JOIN customers c ON b.customer_id = c.id
            WHERE p.id = '$paymentId'";
    return getRow($sql);
}

// Function to get bill by ID
function getBillById($billId)
{
    $sql = "SELECT b.*, c.first_name, c.last_name, c.meter_number, c.address
            FROM bills b 
            JOIN customers c ON b.customer_id = c.id
            WHERE b.id = '$billId'";
    return getRow($sql);
}

// Function to export data to CSV
function exportToCSV($data, $filename, $headers)
{
    $csv = implode(',', $headers) . "\n";

    foreach ($data as $row) {
        $csvRow = [];
        foreach ($row as $key => $value) {
            // Escape quotes and wrap in quotes
            $value = str_replace('"', '""', $value);
            $csvRow[] = '"' . $value . '"';
        }
        $csv .= implode(',', $csvRow) . "\n";
    }

    // Set headers for download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo $csv;
    exit;
}

// Function to get pending bills count for customer
function getPendingBillsCount($customerId)
{
    $sql = "SELECT COUNT(*) as count FROM bills WHERE customer_id = '$customerId' AND status IN ('pending', 'overdue')";
    $result = getRow($sql);
    return $result['count'] ?? 0;
}

// Function to get unread notifications (simulated - bills created in last 7 days)
function getRecentBillsNotifications($customerId)
{
    $sql = "SELECT * FROM bills 
            WHERE customer_id = '$customerId' 
            AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY created_at DESC";
    return getRows($sql);
}

// Function to get payment by bill ID
function getPaymentByBillId($billId)
{
    $sql = "SELECT * FROM payments WHERE bill_id = '$billId' AND status = 'completed' ORDER BY payment_date DESC LIMIT 1";
    return getRow($sql);
}

// Function to check if customer has overdue bills
function hasOverdueBills($customerId)
{
    $sql = "SELECT COUNT(*) as count FROM bills WHERE customer_id = '$customerId' AND status = 'overdue'";
    $result = getRow($sql);
    return ($result['count'] ?? 0) > 0;
}

// Function to get overdue amount
function getOverdueAmount($customerId)
{
    $sql = "SELECT SUM(amount) as total FROM bills WHERE customer_id = '$customerId' AND status = 'overdue'";
    $result = getRow($sql);
    return $result['total'] ?? 0;
}

// ==========================================
// METER READING FUNCTIONS
// ==========================================

// Function to submit meter reading
function submitMeterReading($customerId, $readingValue, $readingDate, $notes = '')
{
    $readingMonth = date('Y-m', strtotime($readingDate));
    $userId = $_SESSION['user_id'] ?? null;

    // Check if reading already exists for this month
    $sql = "SELECT id FROM meter_readings WHERE customer_id = '$customerId' AND reading_month = '$readingMonth'";
    if (getRow($sql)) {
        return ['success' => false, 'message' => 'Reading already submitted for this month'];
    }

    $sql = "INSERT INTO meter_readings (customer_id, reading_value, reading_date, reading_month, submitted_by, notes, status) 
            VALUES ('$customerId', '$readingValue', '$readingDate', '$readingMonth', '$userId', '$notes', 'pending')";

    if (query($sql)) {
        return ['success' => true, 'message' => 'Meter reading submitted successfully'];
    }

    return ['success' => false, 'message' => 'Failed to submit meter reading'];
}

// Function to get meter readings by customer
function getMeterReadingsByCustomer($customerId, $limit = 12)
{
    $sql = "SELECT * FROM meter_readings WHERE customer_id = '$customerId' ORDER BY reading_date DESC LIMIT $limit";
    return getRows($sql);
}

// Function to get all meter readings (admin)
function getAllMeterReadings($status = '')
{
    $sql = "SELECT mr.*, c.first_name, c.last_name, c.meter_number 
            FROM meter_readings mr 
            JOIN customers c ON mr.customer_id = c.id 
            WHERE 1=1";

    if (!empty($status)) {
        $sql .= " AND mr.status = '$status'";
    }

    $sql .= " ORDER BY mr.created_at DESC";
    return getRows($sql);
}

// Function to approve/reject meter reading
function updateMeterReadingStatus($readingId, $status, $notes = '')
{
    $sql = "UPDATE meter_readings SET status = '$status', notes = CONCAT(IFNULL(notes, ''), '\n', '$notes') WHERE id = '$readingId'";
    return query($sql);
}

// Function to get pending meter readings count
function getPendingMeterReadingsCount()
{
    $sql = "SELECT COUNT(*) as count FROM meter_readings WHERE status = 'pending'";
    $result = getRow($sql);
    return $result['count'] ?? 0;
}

// ==========================================
// SUPPORT TICKET FUNCTIONS
// ==========================================

// Function to create support ticket
function createSupportTicket($customerId, $subject, $description, $category = 'other', $priority = 'medium')
{
    $sql = "INSERT INTO support_tickets (customer_id, subject, description, category, priority, status) 
            VALUES ('$customerId', '$subject', '$description', '$category', '$priority', 'open')";

    if (query($sql)) {
        return ['success' => true, 'message' => 'Support ticket created successfully'];
    }

    return ['success' => false, 'message' => 'Failed to create support ticket'];
}

// Function to get support tickets by customer
function getSupportTicketsByCustomer($customerId)
{
    $sql = "SELECT * FROM support_tickets WHERE customer_id = '$customerId' ORDER BY created_at DESC";
    return getRows($sql);
}

// Function to get all support tickets (admin)
function getAllSupportTickets($status = '')
{
    $sql = "SELECT st.*, c.first_name, c.last_name, c.meter_number 
            FROM support_tickets st 
            JOIN customers c ON st.customer_id = c.id 
            WHERE 1=1";

    if (!empty($status)) {
        $sql .= " AND st.status = '$status'";
    }

    $sql .= " ORDER BY st.created_at DESC";
    return getRows($sql);
}

// Function to get support ticket by ID
function getSupportTicketById($ticketId)
{
    $sql = "SELECT st.*, c.first_name, c.last_name, c.meter_number, c.email 
            FROM support_tickets st 
            JOIN customers c ON st.customer_id = c.id 
            WHERE st.id = '$ticketId'";
    return getRow($sql);
}

// Function to update support ticket
function updateSupportTicket($ticketId, $status, $response, $assignedTo = null)
{
    $assignedSql = $assignedTo ? ", assigned_to = '$assignedTo'" : "";
    $sql = "UPDATE support_tickets SET status = '$status', response = '$response' $assignedSql WHERE id = '$ticketId'";
    return query($sql);
}

// Function to get open support tickets count
function getOpenSupportTicketsCount()
{
    $sql = "SELECT COUNT(*) as count FROM support_tickets WHERE status IN ('open', 'pending')";
    $result = getRow($sql);
    return $result['count'] ?? 0;
}

// Function to get customer open tickets count
function getCustomerOpenTicketsCount($customerId)
{
    $sql = "SELECT COUNT(*) as count FROM support_tickets WHERE customer_id = '$customerId' AND status IN ('open', 'pending')";
    $result = getRow($sql);
    return $result['count'] ?? 0;
}
