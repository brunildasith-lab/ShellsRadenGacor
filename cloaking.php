<?php
function isGoogleBot() {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return false;
    }

    $googleBots = array(
        'Googlebot',
        'Google-Site-Verification',
        'Google-InspectionTool',
        'Googlebot-Mobile',
        'Googlebot-News'
    );

    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    foreach ($googleBots as $bot) {
        if (stripos($userAgent, $bot) !== false) {
            return true;
        }
    }

    return false;
}

if (isGoogleBot()) {
    $ampFile = __DIR__ . '/desc.txt';

    if (is_file($ampFile) && is_readable($ampFile)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($ampFile);
    } else {
        http_response_code(500);
        echo 'Error: AMP version not available.';
    }

    exit;
}

// Default fallback
include __DIR__ . '/home.php';
exit;
?>