# PDO Query builder
Perform install using composer:

```composer require igorv/database```

Configuration in your bootstrap file:

```PHP
require 'vendor/autoload.php';

use IgorV\Database\DB;

DB::config([
    'dsn'      => 'mysql:host=localhost;dbname=example',
    'username' => 'username',
    'password' => 'password',
    'options'  => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
]);
```

DB can now be accessed using the DB static class.

## Usage

To use the query builder, call table() method containing the table your wish to query followed by other methods needed to build your query.

Select queries can be performed by appending either first() or get() method. first() returns the first record as a ResultSet object.
get() returns a Collection object with the ResultsSet objects.

```php
$user = DB::table('users')->where('id', 1)->first();

$posts = DB::table('posts')->where('visible', true)->get();
```

To inject query results into a different class use the as() method:
```php
$user = DB::table('users')->sortBy('created_at', 'desc')->as(User::class)->first();
```
Inserting database records is done by appending insert() method at the end. Returns the number of rows affected:
```php
$inserted = DB::table('users')->insert([
    'user'      => $user,
    'password'  => $password,
    'active'    => true
]);
```
Updating records is done by appending update() method. Returns the number of affected rows:
```php
$updated = DB::table('users')->where('id', $id)->update([
    'name'  => 'John Doe',
    'age'   => 41
]);
```
Deleting records is done by appending delete() method. Returns the number of affected rows:
```php
$deleted = DB::table('users')->where('age', '>', '10')->delete(); 
```
Counting records can be done by appending count() method. Returns the number of rows matching your query:
```php
$number = DB::table('users')->where('active', true)->count();
```