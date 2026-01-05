<?php
require_once __DIR__ . '/../../db_connection.php';

echo "Starting cleanup...\n";

$result = $bookingsCollection->updateMany(
    ['status' => ['$exists' => true]],
    ['$unset' => ['status' => ""]]
);

echo "✅ Cleaned {$result->getModifiedCount()} documents\n";
echo "Total matched: {$result->getMatchedCount()}\n";
?>