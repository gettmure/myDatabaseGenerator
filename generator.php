<?php
$start = microtime(true);

function randomUUID()
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

function generateUUID(&$array)
{
    for ($i = 0; $i < sizeof($array); $i++) {
        $array[$i] = randomUUID();
    }
}

function generateCategoriesTable()
{
    global $categoriesUUID, $pdo;
    generateUUID($categoriesUUID);
    $sql = "INSERT INTO categories(id, name) VALUES";
    for ($i = 0; $i < sizeof($categoriesUUID); $i++) {
        $sql = $sql . "('$categoriesUUID[$i]', '$i'),";
    }
    $sql[strlen($sql) - 1] = ";";
    $pdo->query($sql);
}

function generateUsersTable()
{
    global $usersUUID, $pdo;
    generateUUID($usersUUID);
    $i = 0;
    while ($i < sizeof($usersUUID)) {
        $sql = "INSERT INTO users(id, name) VALUES";
        for ($j = 0; $j < 3125; $j++) {
            $userIndex = $i + $j;
            $sql = $sql . "('$usersUUID[$userIndex]', '$userIndex'),";
        }
        $sql[strlen($sql) - 1] = ";";
        $pdo->query($sql);
        $i += 3125;
    }
}

function generateMessagesTable()
{
    global $messagesUUID, $categoriesUUID, $usersUUID, $pdo;
    generateUUID($messagesUUID);
    $i = 0;
    while ($i < sizeof($messagesUUID)) {
        $sql = "INSERT INTO messages(id, text, category_id, posted_at, author_id) VALUES";
        for ($j = 0; $j < 3125; $j++) {
            $messageIndex = $i + $j;
            $categoryIndex = array_rand($categoriesUUID);
            $userIndex = array_rand($usersUUID);
            $sql = $sql . "('$messagesUUID[$messageIndex]', '$messageIndex', '$categoriesUUID[$categoryIndex]', '00:00:00', '$usersUUID[$userIndex]'),";
        }
        $sql[strlen($sql) - 1] = ";";
        $pdo->query($sql);
        $i += 3125;
    }
}

$categoriesUUID = array_fill(0, 5000, NULL);
$usersUUID = array_fill(0, 500000, NULL);
$messagesUUID = array_fill(0, 1000000, NULL);

$pdo = new PDO("pgsql:host=localhost; dbname=forum_generator", "postgres", "1234");

generateCategoriesTable();
generateUsersTable();
generateMessagesTable();
printf("Done for %.2f seconds" . PHP_EOL, microtime(true) - $start);
