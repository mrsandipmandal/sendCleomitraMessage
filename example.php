<?php
/**
 * @author Sandip
 * @version 1.0
 * Handles WhatsApp messaging via Cleomitra API
 */

require_once __DIR__ . '/CleomitraClient.php';

$client = new CleomitraClient([
    'api_key' => 'cmk_642d3142cb7cef6a67b30792ae599146',
    'sender_id' => '827d890e-c1e5-4787-8acb-40bd94c7c3f8',
    'default_language' => 'en_US'
]);

try {
    $result = $client->sendTemplate(
        '7501573836',      // Recipient phone number
        'for_main_bill',   // Template name
        [
            'body_1' => 'Sandip testing.',
            'body_2' => 'Sittong',
            'body_3' => '₹12,500',
            'body_4' => 'https://bluehomes.com/invoice/INV-10293'
        ]
        // language & fromId use class defaults
    );

    $msg = $result['message'];
    echo "✅ Success!\n";
    echo "   Message ID: {$msg['id']}\n";
    echo "   Status: {$msg['status']}\n";
    echo "   WhatsApp ID: " . ($msg['externalId'] ?? 'pending') . "\n";
    $messageId = $msg['id'];
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    $messageId = null;
}

// echo "\n";

// // ===== Example 2: Send Text Message =====
// echo "2. Send Text Message\n";
// echo "--------------------\n";

// try {
//     $result = $client->sendText(
//         '9635429272',
//         'Hello! This is a test text message from CleomitraClient.'
//     );

//     $msg = $result['message'];
//     echo "✅ Success!\n";
//     echo "   Message ID: {$msg['id']}\n";
//     echo "   Status: {$msg['status']}\n";
    
// } catch (Exception $e) {
//     echo "❌ Error: " . $e->getMessage() . "\n";
// }

// echo "\n";

// // ===== Example 3: Check Message Status =====
// if ($messageId) {
//     echo "3. Check Message Status\n";
//     echo "-----------------------\n";
    
//     try {
//         $result = $client->getMessageStatus($messageId);
//         $msg = $result['message'];
        
//         echo "✅ Status retrieved!\n";
//         echo "   Message ID: {$msg['id']}\n";
//         echo "   Status: {$msg['status']}\n";
//         echo "   WhatsApp ID: " . ($msg['externalId'] ?? 'none') . "\n";
//         echo "   Timestamp: {$msg['timestamp']}\n";
        
//     } catch (Exception $e) {
//         echo "❌ Error: " . $e->getMessage() . "\n";
//     }
//     echo "\n";
// }

// // ===== Example 4: Get Recent Messages =====
// echo "4. Get Recent Messages (Last 10)\n";
// echo "--------------------------------\n";

// try {
//     $result = $client->getRecentMessages(10);
//     echo "✅ Found {$result['total']} total messages\n";
    
//     foreach ($result['messages'] as $msg) {
//         $time = date('Y-m-d H:i:s', strtotime($msg['timestamp']));
//         $type = $msg['content']['type'];
//         $template = $type === 'template' ? " ({$msg['content']['templateName']})" : '';
//         $waId = $msg['externalId'] ? " | WA: " . substr($msg['externalId'], 0, 20) . '...' : '';
//         echo "   [{$time}] {$msg['status']} | {$type}{$template} | {$msg['from']['externalId']} → {$msg['to']['externalId']}{$waId}\n";
//     }
    
// } catch (Exception $e) {
//     echo "❌ Error: " . $e->getMessage() . "\n";
// }

// echo "\n";

// // ===== Example 5: Override defaults when needed =====
// echo "5. Override Class Defaults\n";
// echo "---------------------------\n";

// try {
//     // Override language
//     $result = $client->sendTemplate(
//         '9635429272',
//         'for_main_bill',
//         ['body_1' => 'Test', 'body_2' => 'Place', 'body_3' => '100', 'body_4' => 'url'],
//         'en'  // Override language
//     );
//     echo "✅ Sent with custom language: en\n";
    
//     // Override sender (if you have multiple approved senders)
//     // $result = $client->sendTemplate('9635429272', 'template', $params, 'en_US', 'other-sender-id');
    
// } catch (Exception $e) {
//     echo "❌ Error: " . $e->getMessage() . "\n";
// }

// echo "\n=== All Examples Complete ===\n";