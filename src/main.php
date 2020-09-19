<?php

require(__DIR__ . '/env.php');
require(__DIR__ . '/books.php');
require(__DIR__ . '/database.php');

include(__DIR__ . '/shared/constants.php');

function main()
{
	$books = generateBooks(BOOKS_COUNT);
	insertValuesIntoDatabase($books);
}

echo 'Processing...' . PHP_EOL;

main();

echo 'Done!' . PHP_EOL;
