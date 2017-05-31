# PDO Querybuilder
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