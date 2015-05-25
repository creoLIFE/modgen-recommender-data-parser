<?php
/**
 * Created by PhpStorm.
 * User: mirekratman
 * Date: 22/05/15
 * Time: 15:41
 */

ERROR_REPORTING(E_ALL);
require __DIR__ . "/../src/loader.php";

$fileName = './data/input.xml';

$db = '';
$key = '';
$classApiClient = new \Recommender\Api\Client($db, $key);
$classApiClient->setDebug(true);
$classParser = new Recommender\Data\Parser($fileName);

$classParser->parseModgenXml($fileName,$classApiClient);