<?php

$apiKey = "cmk_642d3142cb7cef6a67b30792ae599146";

function getMessageStatus($apiKey, $messageId) {
    $url = "https://api.cleomitra.app/v1/messages/" . $messageId;
    
    $headers = [
        "X-API-Key: " . $apiKey,
        "Content-Type: application/json"
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ["error" => "HTTP $httpCode", "data" => json_decode($response, true)];
    }
    
    return ["success" => true, "data" => json_decode($response, true)];
}

function getRecentMessages($apiKey, $limit = 20) {
    $url = "https://api.cleomitra.app/v1/messages?pageSize=$limit";
    
    $headers = [
        "X-API-Key: " . $apiKey,
        "Content-Type: application/json"
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ["error" => "HTTP $httpCode"];
    }
    
    return ["success" => true, "data" => json_decode($response, true)];
}

$messageId = $_GET['id'] ?? '';
$action = $_GET['action'] ?? 'recent';

$result = null;

if ($action === 'check' && $messageId) {
    $result = getMessageStatus($apiKey, $messageId);
} elseif ($action === 'recent') {
    $result = getRecentMessages($apiKey, 30);
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleomitra Message Status</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; }
        .card { border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; background: #fafafa; }
        .status-pending { background: #fff3cd; border-color: #ffc107; }
        .status-sent { background: #cce5ff; border-color: #007bff; }
        .status-delivered { background: #d4edda; border-color: #28a745; }
        .status-read { background: #d1ecf1; border-color: #17a2b8; }
        .status-failed { background: #f8d7da; border-color: #dc3545; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #ffc107; color: #000; }
        .badge-sent { background: #007bff; color: #fff; }
        .badge-delivered { background: #28a745; color: #fff; }
        .badge-read { background: #17a2b8; color: #fff; }
        .badge-failed { background: #dc3545; color: #fff; }
        .meta { font-size: 13px; color: #666; margin-top: 5px; }
        .meta-item { display: inline-block; margin-right: 15px; }
        input[type="text"] { padding: 8px; width: 300px; margin-right: 10px; }
        button { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .template-tag { background: #e9ecef; padding: 2px 6px; border-radius: 3px; font-size: 12px; }
        pre { background: #f8f9fa; padding: 10px; overflow-x: auto; font-size: 12px; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <h1>📱 Cleomitra Message Status</h1>
    
    <div style="margin-bottom: 20px;">
        <form method="GET">
            <input type="hidden" name="action" value="check">
            <input type="text" name="id" placeholder="Message ID দিন" value="<?= htmlspecialchars($messageId) ?>">
            <button type="submit">Status Check</button>
            <a href="?action=recent" style="margin-left: 10px; color: #007bff;">Recent Messages</a>
        </form>
    </div>

    <?php if ($action === 'check' && $messageId): ?>
        <h2>Message Details</h2>
        <?php if (isset($result['error'])): ?>
            <div class="card status-failed">
                <strong>Error:</strong> <?= $result['error'] ?>
                <pre><?= json_encode($result['data'], JSON_PRETTY_PRINT) ?></pre>
            </div>
        <?php elseif (isset($result['data']['message'])): ?>
            <?php $msg = $result['data']['message']; ?>
            <div class="card status-<?= $msg['status'] ?>">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0;">Message ID: <?= $msg['id'] ?></h3>
                    <span class="badge badge-<?= $msg['status'] ?>"><?= strtoupper($msg['status']) ?></span>
                </div>
                
                <div class="meta">
                    <span class="meta-item"><strong>Type:</strong> <?= $msg['type'] ?></span>
                    <span class="meta-item"><strong>Channel:</strong> <?= $msg['channel'] ?></span>
                    <span class="meta-item"><strong>Time:</strong> <?= $msg['timestamp'] ?></span>
                </div>
                
                <?php if (!empty($msg['externalId'])): ?>
                    <div class="meta" style="color: #28a745;">
                        <strong>WhatsApp ID:</strong> <?= $msg['externalId'] ?>
                    </div>
                <?php endif; ?>
                
                <div class="meta">
                    <strong>From:</strong> <?= $msg['from']['displayName'] ?? $msg['from']['externalId'] ?> 
                    (<?= $msg['from']['id'] ?>)
                </div>
                <div class="meta">
                    <strong>To:</strong> <?= $msg['to']['displayName'] ?? $msg['to']['externalId'] ?> 
                    (<?= $msg['to']['id'] ?>)
                </div>
                
                <h4>Content:</h4>
                <pre><?= json_encode($msg['content'], JSON_PRETTY_PRINT) ?></pre>
                
                <?php if (!empty($msg['externalMetaData'])): ?>
                    <h4>External Meta:</h4>
                    <pre><?= json_encode($msg['externalMetaData'], JSON_PRETTY_PRINT) ?></pre>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    
    <?php elseif ($action === 'recent' && isset($result['data']['messages'])): ?>
        <h2>Recent Messages (Last 30)</h2>
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Content</th>
                    <th>WhatsApp ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result['data']['messages'] as $msg): ?>
                    <tr>
                        <td><?= date('H:i:s', strtotime($msg['timestamp'])) ?></td>
                        <td style="font-size: 11px;"><?= substr($msg['id'], 0, 8) ?>...</td>
                        <td>
                            <?= $msg['type'] ?>
                            <?php if ($msg['content']['type'] === 'template'): ?>
                                <span class="template-tag"><?= $msg['content']['templateName'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?= $msg['status'] ?>">
                                <?= strtoupper($msg['status']) ?>
                            </span>
                        </td>
                        <td><?= $msg['from']['displayName'] ?? $msg['from']['externalId'] ?></td>
                        <td><?= $msg['to']['displayName'] ?? $msg['to']['externalId'] ?></td>
                        <td>
                            <?php if ($msg['content']['type'] === 'text'): ?>
                                <?= substr($msg['content']['text'], 0, 50) ?>...
                            <?php elseif ($msg['content']['type'] === 'template'): ?>
                                Template: <?= $msg['content']['templateName'] ?>
                            <?php else: ?>
                                <?= $msg['content']['type'] ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($msg['externalId'])): ?>
                                <a href="?action=check&id=<?= $msg['id'] ?>" style="font-size: 11px;"><?= substr($msg['externalId'], 0, 20) ?>...</a>
                            <?php else: ?>
                                <span style="color: #999;">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>