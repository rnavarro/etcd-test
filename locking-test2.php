<?php

require 'vendor/autoload.php';

use LinkORB\Component\Etcd\Client;

$account_id   = 2982;
$connector_id = 327240;

/*
 * This is a test case to see if we can "refresh" the lock for long running, but active processes.
 *
 * The use case for this is a long running initial product download or sync
 *
 * We create a new lock with a 3 second time out
 *
 * We then try to immediately create a new lock, which should fail
 *
 * We then refresh the original lock, increasing the timeout by another 3 seconds
 *
 * We then try to create a new lock, which should fail
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

echo "Refreshing lock, timeout of 3 seconds\n";
$result1 = $client->set("/{$connector_id}", $jobPid, 3, [
    'prevValue' => $jobPid
]);
print_r($result1);
echo "\n";

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