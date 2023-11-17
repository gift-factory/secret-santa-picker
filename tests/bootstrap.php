<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

set_error_handler(
    function ($level, $error, $file, $line) {
        if (error_reporting() === 0) {
            return false;
        }

        throw new ErrorException($error, -1, $level, $file, $line);
    },
    E_ALL,
);
