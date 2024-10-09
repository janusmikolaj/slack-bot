<?php
$slackApplication = '';

$log_file = 'slack_request_log.txt';

function logToFile($file, $data) {
    $log = date('Y-m-d H:i:s') . " - " . print_r($data, true) . "\n";
    file_put_contents($file, $log, FILE_APPEND);
}

$data = json_decode(file_get_contents('php://input'), true);
logToFile($log_file, $data);

if (isset($data['type']) && $data['type'] == 'url_verification') {
    echo $data['challenge'];
    exit;
}

if (isset($data['event'])) {
    $event = $data['event'];

    if (isset($event['type']) && $event['type'] == 'app_mention' && isset($event['text'])) {
        $channel = $event['channel'];
        $bot_id = $event['user']; // ID użytkownika, który wspomniał bota
        $response_text = "Cześć <@$bot_id>! Jak mogę Ci pomóc?"; // Tekst odpowiedzi

        sendMessageToSlack($slackApplication, $channel, $response_text);
    }
}

function sendMessageToSlack($token, $channel, $message) {
    $url = "https://slack.com/api/chat.postMessage";

    $data = http_build_query([
        "token" => $token,
        "channel" => $channel,
        "text" => $message,
    ]);

    $options = [
        "http" => [
            "header" => "Content-type: application/x-www-form-urlencoded\r\n",
            "method" => "POST",
            "content" => $data,
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    logToFile('slack_response_log.txt', json_decode($response, true)); // Logowanie odpowiedzi
}
