<?php
// Process order API
error_reporting(0); // Disable error reporting to prevent invalid JSON
header('Content-Type: application/json');
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    $customerName = isset($input['customerName']) ? trim($input['customerName']) : '';
    $customerPhone = isset($input['customerPhone']) ? trim($input['customerPhone']) : '';
    $menuItemId = isset($input['menuItemId']) ? intval($input['menuItemId']) : 0;
    
    // Validate inputs
    if (empty($customerName) || empty($customerPhone) || $menuItemId <= 0) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    try {
        // Get menu item details
        $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ? AND is_available = 1");
        $stmt->execute([$menuItemId]);
        $menuItem = $stmt->fetch();
        
        if (!$menuItem) {
            echo json_encode(['success' => false, 'message' => 'Menu item not found']);
            exit;
        }
        
        // Generate unique order number (shorter format: #PH + 4 random digits)
        $orderNumber = '#PH' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Check if order number exists (very unlikely, but just in case)
        $stmt = $pdo->prepare("SELECT id FROM customer_orders WHERE order_number = ?");
        $stmt->execute([$orderNumber]);
        if ($stmt->fetch()) {
            // Generate new one
            $orderNumber = '#PH' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        // Insert order into database
        $stmt = $pdo->prepare("
            INSERT INTO customer_orders 
            (order_number, customer_name, customer_phone, menu_item_id, item_name, item_price, order_status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        
        $stmt->execute([
            $orderNumber,
            $customerName,
            $customerPhone,
            $menuItemId,
            $menuItem['name'],
            $menuItem['price']
        ]);
        
        // Return success with order number
        echo json_encode([
            'success' => true,
            'orderNumber' => $orderNumber,
            'message' => 'Order placed successfully!'
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
