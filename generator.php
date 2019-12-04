<?php
require 'vendor/autoload.php';

use Ramsey\Uuid\Uuid;

const CATEGORIES_COUNT = 5000;
const USERS_COUNT = 500000;
const MESSAGES_COUNT = 1000000;
const MAX_ROWS = 3000;

$db = new PDO("pgsql:host=localhost; dbname=generator", "postgres", "1234");
$categoriesUUID = [];
$usersUUID = [];
$messagesUUID = [];

function generateUuid()
{
    return Uuid::uuid4()->toString();
}

function generateUuidArray($array, $count)
{
    for ($i = 0; $i < $count; $i++) {
        $array['id' . $i] = generateUuid();
    }
    return $array;
}

function generateCategoriesTable(&$categories, $count, $db)
{
    $categories = generateUuidArray($categories, $count);
    $chunks = array_chunk($categories, MAX_ROWS, true);

    $startIndex = 0;
    foreach ($chunks as $chunk) {
        $sql = "INSERT INTO categories(id, name) VALUES";
        $insertQuery = [];

        for ($i = 0; $i < MAX_ROWS; $i++) {
            $index = $startIndex + $i;
            if ($index >= $count) break;
            $insertQuery[] = sprintf("(:id%d, 'Category #%d')", $index, $index);
        }

        $sql .= implode(',', $insertQuery) . ";";
        $db->prepare($sql)->execute($chunk);
        $startIndex += MAX_ROWS;
    }
}

function generateUsersTable(&$users, $count, $db)
{
    $users = generateUuidArray($users, $count);
    $chunks = array_chunk($users, MAX_ROWS, true);

    $startIndex = 0;
    foreach ($chunks as $chunk) {
        $sql = "INSERT INTO users(id, name) VALUES";
        $insertQuery = [];

        for ($i = 0; $i < MAX_ROWS; $i++) {
            $index = $startIndex + $i;
            if ($index >= $count) break;
            $insertQuery[] = sprintf("(:id%d, 'User #%d')", $index, $index);
        }

        $sql .= implode(',', $insertQuery) . ";";
        $db->prepare($sql)->execute($chunk);
        $startIndex += MAX_ROWS;
    }
}

function generateMessagesTable(&$messages, $categories, $users, $count, $db) {
    $messages = generateUuidArray($messages, $count);
    $chunks = array_chunk($messages, MAX_ROWS, true);

    $startIndex = 0;
    foreach ($chunks as $chunk) {
        $sql = "INSERT INTO messages(id, text, category_id, posted_at, author_id) VALUES";
        $insertQuery = [];

        for ($i = 0; $i < MAX_ROWS; $i++) {
            $messageIndex = $startIndex + $i;
            if ($messageIndex >= $count) break;
            $randomCategoryIndex = array_rand($categories);
            $randomUsersIndex = array_rand($users);
            $insertQuery[] = sprintf("(:id%d, 'Text #%d', '%s', '00:00:00', '%s')", $messageIndex, $messageIndex, $categories[$randomCategoryIndex], $users[$randomUsersIndex]);
        }

        $sql .= implode(',', $insertQuery) . ";";
        $db->prepare($sql)->execute($chunk);
        $startIndex += MAX_ROWS;
    }
}

$start = microtime(true);

generateCategoriesTable($categoriesUUID, CATEGORIES_COUNT, $db);
generateUsersTable($usersUUID, USERS_COUNT, $db);
generateMessagesTable($messagesUUID, $categoriesUUID, $usersUUID, MESSAGES_COUNT, $db);

printf("Done for %.2f seconds" . PHP_EOL, microtime(true) - $start);
