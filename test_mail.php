<?php
require __DIR__ . '/mail_config.php';
echo sendOTP('yourgmail@gmail.com', '123456') ? 'OK' : 'FAIL (check error_log)';
