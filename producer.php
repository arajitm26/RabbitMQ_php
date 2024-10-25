<?php
require 'vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

function sendToQueue($apiDetails) {
    // Connect to RabbitMQ
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    // Declare a queue named 'api_tasks'
    $channel->queue_declare('api_tasks', false, true, false, false);

    // Encode the API details as JSON
    $message = new AMQPMessage(json_encode($apiDetails), ['delivery_mode' => 2]);
    $channel->basic_publish($message, '', 'api_tasks');

    echo " [x] Sent API request to queue\n";

    // Close the connection
    $channel->close();
    $connection->close();
}

// Define API details
$apiDetails = [
    'url' => 'https://api.example.com/data',
    'method' => 'POST',
    'data' => [
        'name' => 'John',
        'email' => 'john@example.com'
    ]
];

// Send the API details to the queue
sendToQueue($apiDetails);
?>
