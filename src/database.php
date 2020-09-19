<?php

function pushDataIntoDatabase($db, $sql, $values)
{
  $db->beginTransaction();
  $stmt = $db->prepare($sql);
  try {
    $stmt->execute($values);
  } catch (PDOException $e) {
    echo $e->getMessage();
  }
  $db->commit();
}

function generateBooksSql($rows, $sql)
{
  $values = [];
  $placeholders = array_fill(0, count($rows[0]), '?');
  foreach ($rows as $index => $row) {
    $sql .= '(' . implode(',', $placeholders);
    $index == count($rows) - 1
      ? $sql .= ');'
      : $sql .= '),';
    foreach ($row as $value) {
      $values[] = $value;
    }
  }
  return [$values, $sql];
}

function generateBooksWithAuthorsSql($rows, $sql)
{
  $values = [];
  foreach ($rows as $bookId => $book) {
    foreach ($book as $author) {
      $sql .= "('$bookId', ?)";
      $values[] = $author;
      if ($author == end($book) && $book == end($rows)) {
        $sql .= ';';
      } else {
        $sql .= ',';
      }
    }
  }
  return [$values, $sql];
}

function generateAuthorsForBooks($books, $db)
{
  $booksWithAuthors = [];
  $sth = $db->prepare("SELECT id FROM users");
  $sth->execute();
  $authors = $sth->fetchAll(PDO::FETCH_COLUMN, 0);
  foreach ($books as $book) {
    $bookId = $book[0];
    $booksWithAuthors[$bookId] = [];
    $authorsCount = random_int(MIN_AUTHOURS_COUNT, MAX_AUTHOURS_COUNT);
    for ($i = 0; $i < $authorsCount; $i++) {
      $authorIndex = random_int(0, count($authors) - 1);
      if (!in_array($authors[$authorIndex], $booksWithAuthors[$bookId])) {
        $booksWithAuthors[$bookId][] = $authors[$authorIndex];
      }
    }
  }
  return $booksWithAuthors;
}

function executeSql($rows, $tableName, $db)
{
  switch ($tableName) {
    case 'books':
      $sql = "INSERT INTO books(id, name, description, year, image_url) VALUES ";
      [$insertingValues, $sql] = generateBooksSql($rows, $sql);
      pushDataIntoDatabase($db, $sql, $insertingValues);
      break;
    case 'book_user':
      $sql = "INSERT INTO book_user(book_id, user_id) VALUES ";
      [$insertingValues, $sql] = generateBooksWithAuthorsSql($rows, $sql);
      pushDataIntoDatabase($db, $sql, $insertingValues);
      break;
  }
}

function getDatabaseConnection()
{
  $HOST = $_ENV['DB_HOST'];
  $DB_NAME = $_ENV['DB_NAME'];
  $DB_USER = $_ENV['DB_USER'];
  $DB_PASSWORD = $_ENV['DB_PASSWORD'];
  return new PDO("pgsql:host=$HOST; dbname=$DB_NAME", "$DB_USER", "$DB_PASSWORD");
}

function insertValuesIntoDatabase($books)
{
  $db = getDatabaseConnection();
  $booksWithAuthors = generateAuthorsForBooks($books, $db);
  executeSql($books, 'books', $db);
  executeSql($booksWithAuthors, 'book_user', $db);
}
