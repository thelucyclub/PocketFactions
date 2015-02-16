<?php

$file = fopen("defaultDatabase.pfdb", "wb");

$magicPrefix = "\xffLoMPFDB\x0d\x0a\x1a\x0a";
$version = 1;
$magicSuffix = "\xde\xad\xc0\xdeLoMPFDB\xff";

fwrite($file, $magicPrefix);
fwrite($file, chr($version));
fwrite($file, str_repeat("\x00", 4));
fwrite($file, str_repeat("\x00", 4));
fwrite($file, str_repeat("\x00", 8));
fwrite($file, $magicSuffix);
fclose($file);
