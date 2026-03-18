<?php
define('MAIL_HOST',       $_ENV['MAIL_HOST']       ?? 'smtp.gmail.com');
define('MAIL_PORT',  (int)($_ENV['MAIL_PORT']       ?? 587));
define('MAIL_USERNAME',   $_ENV['MAIL_USERNAME']    ?? '');
define('MAIL_PASSWORD',   $_ENV['MAIL_PASSWORD']    ?? '');
define('MAIL_FROM_EMAIL', $_ENV['MAIL_FROM_EMAIL']  ?? '');
define('MAIL_FROM_NAME',  $_ENV['MAIL_FROM_NAME']   ?? 'SparkIIT');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION']  ?? 'tls');
