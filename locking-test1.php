<?php

require 'vendor/autoload.php';

use LinkORB\Component\Etcd\Client;

$account_id   = 2982;
$connector_id = 327240;

/*
 * This is a basic test case to see if the locks actually do what they're supposed to.
 *
 * We create a new lock with a 3 second time out
 *
 * We then try to immediately create a new lock, which should fail
 *
 * We wait 5 seconds, to let the original lock expire
 *
 * We then try to create a new lock, which should succeed
 */

$pid      = getmypid();
$hostname = gethostname();
$jobPid   = $hostname . ':' . $pid;

echo "PID: $jobPid\n";

$client = new Client();

$client->setRoot("{$account_id}");

echo "Setting initial lock, timeout of 3 seconds\n";
$result1 = $client->set("/{$connector_id}", $jobPid, 3);
print_r($result1);
echo "\n";

echo "Printing directory tree\n";
print_r($client->listDir('/', true));
echo "\n";

echo "Attemping to set value\n";
$result2 = $client->set("/{$connector_id}", $jobPid."-next", 3, [
    'prevExist' => 'false'
]);
print_r($result2);
echo "\n";

echo "Sleeping...\n";
sleep(5);

// get key value
echo "Checking if key has expired\n";
try {
    echo "Key Value: " . $client->get("/{$connector_id}") . "\n\n";
} catch (Exception $e) {
    echo "Key Not Found: {$connector_id}\n\n";
}

echo "Attemping to set value\n";
$result2 = $client->set("/{$connector_id}", $jobPid."-next2", 3, [
    'prevExist' => 'false'
]);
print_r($result2);
echo "\n";

// get key value
try {
    echo "Key Value: " . $client->get("/{$connector_id}") . "\n\n";
} catch (Exception $e) {
    echo "Key Not Found: {$connector_id}\n\n";
}

// Delete key
try {
    echo "Deleting Key\n";
    print_r($client->rm("/{$connector_id}"));
} catch (Exception $e) {
    echo "Key Not Found: {$connector_id}\n";
}
echo "\n";

echo "Printing directory tree\n";
print_r($client->listDir('/', true));