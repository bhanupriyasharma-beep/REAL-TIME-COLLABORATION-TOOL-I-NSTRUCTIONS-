<?php
$clients = [];

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socket, '0.0.0.0', 8080);
socket_listen($socket);
socket_set_nonblock($socket);

echo "WebSocket server started on port 8080...\n";

while (true) {
    $newClient = @socket_accept($socket);
    if ($newClient) {
        $clients[] = $newClient;
    }

    foreach ($clients as $key => $client) {
        $input = @socket_read($client, 1024);
        if ($input === false) continue;

        foreach ($clients as $receiver) {
            if ($receiver !== $client) {
                @socket_write($receiver, $input);
            }
        }
    }

    usleep(10000);
}
