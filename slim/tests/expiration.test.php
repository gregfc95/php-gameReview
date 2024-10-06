<?php
$expirationTimestamp = date('Y-m-d H:i:s', time() + 3600);
echo "Expiration timestamp: $expirationTimestamp";

//it returns datetime with UTC timezone