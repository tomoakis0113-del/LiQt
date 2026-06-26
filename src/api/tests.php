<?php
require_once __DIR__ . '/../vendor/autoload.php';

$ai = new lib\AI();
echo $ai->word_check("こんにちは、元気ですか？") ? 'true' : 'false';