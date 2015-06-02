<?php
/**
 * Created by PhpStorm.
 * User: mirekratman
 * Date: 22/05/15
 * Time: 15:41
 */

ERROR_REPORTING(E_ALL);
require __DIR__ . "/../src/loader.php";

print_r(ini_get('memory_limit')."\n");
set_time_limit(1200);
date_default_timezone_set('UTC');

//Data files
//$xmlFileName = './data/input.xml';
$xmlFileName = './data/palmknihy.xml';
$xmlFileName = './data/czc.xml';


$csvFileProducts = './data/czc-items.csv';
$csvFilePurchases = './data/czc-purchases.csv';
/*
$csvFileProducts = './data/goout-items.csv';
$csvFilePurchases = './data/goout-purchases.csv';
$csvFileProducts = './data/czc-items.csv';
$csvFilePurchases = './data/czc-purchases.csv';
*/

$csvFileProducts = './data/slevydnes-items.csv';
$csvFilePurchases = './data/slevydnes-purchases.csv';

$db = 'shopexpo-test';
$key = 'DyioS5vct4fyqbjjr7Yno8dUFALYjAZe0JP3yR65aCNdtbjk92F9gxU1yDAVR7QS';

//Instance of Transport class
//$transport = new Recommender\Api\Transport\Transport();
$transport = new Recommender\Api\Transport\Batch();
$transport->setBatchSize(5000);
$transport->setDebug(true);

//Instance of API Client
$classApiClient = new \Recommender\Api\Client('http://rapi-dev.modgen.net', $db, $key, $transport);
$classApiClient->deleteDb();
$classApiClient->setDebug(true);

//Instance of parser
$classParser = new Recommender\Data\Parser();
$classParser->setDebug(true);

//Set start time
$timerStart = time();

//Parsing XML
//$classParser->parseModgenXml($xmlFileName,$classApiClient);

//Parsing CSV
$classParser->parseCsvProducts(
    $csvFileProducts,
    $classApiClient,
    array('id', 'name', 'description', 'price', 'available')
);

$classParser->parseCsvPurchases(
    $csvFilePurchases,
    $classApiClient,
    array('itemId', 'userId', 'timestamp')
);
//print_r( $classParser->getResponse() );

$timerEnd = time();
//Set end time
echo date('H:i:s', $timerEnd - $timerStart)."\n";