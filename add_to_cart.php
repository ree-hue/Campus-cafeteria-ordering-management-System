<?php
session_start();
include 'includes/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if($data){
    $item_id = (int)$data['item_id'];
    $quantity = max(1, (int)$data['quantity']);

    
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE item_id=? AND availability_status=1");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    if($item){
        if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        
        if(isset($_SESSION['cart'][$item_id])){
            $_SESSION['cart'][$item_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$item_id] = [
                'item_id' => $item_id,
                'item_name' => $item['item_name'],
                'price' => $item['price'],
                'quantity' => $quantity
            ];
        }

        echo "{$item['item_name']} x{$quantity} added to cart!";
    } else {
        echo "Item not available!";
    }
} else {
    echo "No data received!";
}