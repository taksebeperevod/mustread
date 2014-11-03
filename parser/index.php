<?php

namespace Msnre\Parser;

require ('../vendor/autoload.php');

set_time_limit(0);

//Header('Content-Type: application/json; charset=utf8');
Header('Content-Type: text/html; charset=utf8');

$parser = new Books();
$result = $parser->getBooks();

echo json_encode($result, JSON_UNESCAPED_UNICODE);