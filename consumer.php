<?php
require 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

function callApi($apiDetails) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $apiDetails['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Set HTTP method
    if ($apiDetails['method'] === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($apiDetails['data']));
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $apiDetails['method']);
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    // Execute API call
    $response = curl_exec($ch);
    curl_close($ch);

    echo " [x] API Response: ", $response, "\n";
}

// Connect to RabbitMQ
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

// Declare a queue named 'api_tasks'
$channel->queue_declare('api_tasks', false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function($msg) {
    echo " [x] Received ", $msg->body, "\n";

    // Decode the message and call the API
    $apiDetails = json_decode($msg->body, true);
    callApi($apiDetails);
};

// Set up consumer to listen to the queue
$channel->basic_consume('api_tasks', '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

// Close the connection
$channel->close();
$connection->close();
?>
