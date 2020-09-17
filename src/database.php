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

function generateBooksUsersSql($rows, $sql)
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

function executeSql($rows, $tableName, $db)
{
  $sql = "";
  switch ($tableName) {
    case 'books':
      $sql = "INSERT INTO books(id, name, description, year, image_url) VALUES ";
      [$insertingValues, $sql] = generateBooksUsersSql($rows, $sql);
      pushDataIntoDatabase($db, $sql, $insertingValues);
      break;
    case 'users':
      $sql = "INSERT INTO users(id, username, password, firstname, lastname) VALUES ";
      [$insertingValues, $sql] = generateBooksUsersSql($rows, $sql);
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

function insertValuesIntoDatabase($users, $books, $booksWithAuthors)
{
  $db = getDatabaseConnection();
  executeSql($books, 'books', $db);
  executeSql($users, 'users', $db);
  executeSql($booksWithAuthors, 'book_user', $db);
}
