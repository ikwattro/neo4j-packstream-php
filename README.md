### Neo4j PackStream PHP

#### Experimental library for connecting to the Neo4j database with the PackStream protocol.

### Usage

Clone the package and install the deps:

```bash
composer require ikwattro/neo4j-packstream-php
```

Require the library and create a Session by providing the neo4j host and port :

```php
<?php

require_once(__DIR__.'/vendor/autoload.php');

use Neo4j\PackStream\Session;

$session = new Session('localhost', 7687);
```

Send a query :

```
$result = $session->runQuery('MATCH (n) RETURN n');
```

--- 

License: MIT
Author: Christophe Willemsen