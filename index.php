<?php

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'ru';
require_once $lang . ".html";