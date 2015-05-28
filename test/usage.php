<?php
/**
 * Created by PhpStorm.
 * User: mirekratman
 * Date: 22/05/15
 * Time: 15:41
 */

ERROR_REPORTING(E_ALL);
require __DIR__ . "/../src/loader.php";

$xmlFileName = './data/input.xml';
$csvFileProducts = './data/items.csv';
$csvFileProducts = './data/jobs-cz-items.csv';

$csvFilePurchases = './data/purchases.csv';

$db = 'shopexpo-test';
$key = 'DyioS5vct4fyqbjjr7Yno8dUFALYjAZe0JP3yR65aCNdtbjk92F9gxU1yDAVR7QS';
$classApiClient = new \Recommender\Api\Client('http://rapi-dev.modgen.net', $db, $key, new Recommender\Api\Transport\Batch());
$classApiClient->setDebug(true);
$classParser = new Recommender\Data\Parser();

//$classParser->parseModgenXml($xmlFileName,$classApiClient);
$classParser->parseCsvProducts(
    $csvFileProducts,
    $classApiClient,
    array('id','name','description','price','available')
);
/*
$classParser->parseCsvPurchases(
    $csvFilePurchases,
    $classApiClient,
    array('itemId','userId','timestamp')
);
*/