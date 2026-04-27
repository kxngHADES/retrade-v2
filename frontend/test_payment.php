<?php
require_once 'e:/Final year projects/Projects/ITECA/retrade-v2/frontend/lib/services/payment_gateways_services.php';
try { 
    $pg = new \Lib\services\PaymentGatewaysServices(); 
    $id = $pg->createPaymentSession('test@example.com', 200.0); 
    echo 'Success: ' . $id; 
} catch (Exception $e) { 
    echo 'Error: ' . $e->getMessage(); 
}
