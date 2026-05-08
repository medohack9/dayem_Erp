<?php

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/app/helpers.php';
require ROOT_PATH . '/app/core/App.php';

new App\Core\App();