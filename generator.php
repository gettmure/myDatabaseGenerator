<?php

const CATEGORIES_COUNT = 100;
const USERS_COUNT = 5000;
const MESSAGES_COUNT = 10000;
const MAX_ROWS = 3000;

$db = new PDO("pgsql:host=localhost; dbname=forum", "postgres", "1234");
$categoriesUUID = [];
$usersUUID = [];
$messagesUUID = [];

function generateUuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

function generateUuidArray($array, $count)
{
    for ($i = 0; $i < $count; $i++) {
        $array['id' . $i] = generateUuid();
    }
    return $array;
}

function executeSql($array, $tableName, $count, $db) 
{
    $chunks = array_chunk($array, MAX_ROWS, true);
    $startIndex = 0;
    $tableName == 'categories' ? $field = 'category_name' : $field = 'name';
    foreach ($chunks as $chunk) {
        $sql = sprintf("INSERT INTO %s(id, %s) VALUES", $tableName, $field);
        $insertQuery = [];
        for ($i = 0; $i < MAX_ROWS; $i++) {
            $index = $startIndex + $i;
            if ($index >= $count) break;
            $insertQuery[] = sprintf("(:id%d, '#%d')", $index, $index);
        }
        $sql .= implode(',', $insertQuery);
        $db->prepare($sql)->execute($chunk);
        $startIndex += MAX_ROWS;
    }
}

function executeMessagesSql($messages, $users, $categories, $count, $db) 
{
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
            $insertQuery[] = sprintf("(:id%d, 'Text #%d', '%s', '2000-12-01 00:00:00', '%s')", $messageIndex, $messageIndex, $categories[$randomCategoryIndex], $users[$randomUsersIndex]);
        }
        $sql .= implode(',', $insertQuery);
        $stmt = $db->prepare($sql);
        $stmt->execute($chunk);
        $startIndex += MAX_ROWS;
    }
}

function generateCategoriesTable(&$categories, $count, $db)
{
    $categories = generateUuidArray($categories, $count);
    executeSql($categories, 'categories', $count, $db);
}

function generateUsersTable(&$users, $count, $db)
{
    $users = generateUuidArray($users, $count);
    executeSql($users, 'users', $count, $db);
}

function generateMessagesTable(&$messages, $categories, $users, $count, $db) {
    $messages = generateUuidArray($messages, $count);
    executeMessagesSql($messages, $users, $categories, $count, $db);
}

$start = microtime(true);

generateCategoriesTable($categoriesUUID, CATEGORIES_COUNT, $db);
generateUsersTable($usersUUID, USERS_COUNT, $db);
generateMessagesTable($messagesUUID, $categoriesUUID, $usersUUID, MESSAGES_COUNT, $db);

printf("Done for %.2f seconds" . PHP_EOL, microtime(true) - $start);
