<?php

require(__DIR__ . '/env.php');
require(__DIR__ . '/users.php');
require(__DIR__ . '/books.php');
require(__DIR__ . '/database.php');

include(__DIR__ . '/shared/constants.php');

function makeAuthorsForBooks($books, $authors)
{
	$booksWithAuthors = [];
	foreach ($books as $book) {
		$booksWithAuthors[$book[0]] = [];
		$authorsCount = random_int(MIN_AUTHOURS_COUNT, MAX_AUTHOURS_COUNT);
		for ($i = 0; $i < $authorsCount; $i++) {
			$authorIndex = random_int(MIN_AUTHOURS_INDEX, MAX_AUTHOURS_INDEX);
			if (!in_array($authors[$authorIndex][0], $booksWithAuthors[$book[0]])) {
				$booksWithAuthors[$book[0]][] = $authors[$authorIndex][0];
			}
		}
	}
	return $booksWithAuthors;
}

function main()
{
	$users = generateUsers(USERS_COUNT);
	$books = generateBooks(BOOKS_COUNT);
	$booksWithAuthors = makeAuthorsForBooks($books, $users);
	insertValuesIntoDatabase($users, $books, $booksWithAuthors);
}

echo 'Processing...' . PHP_EOL;

main();

echo 'Done!' . PHP_EOL;
