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
$xmlFileName = './data/acorde.xml';

$xmlFileName = './data/palmknihy.xml';
$xmlFileName = './data/czc.xml';

//!!!
$xmlFileName = './data/shopexpo/viki/zbozi_a_nakupy.xml';

//passed in 1:04
$xmlFileName = './data/shopexpo/emerx.cz/modgen.xml';



$csvFileProducts = './data/czc-items.csv';
$csvFilePurchases = './data/czc-purchases.csv';

$csvFileProducts = './data/goout-items.csv';
$csvFilePurchases = './data/goout-purchases.csv';
//$csvFileProducts = './data/czc-items.csv';
//$csvFilePurchases = './data/czc-purchases.csv';
$csvFileProducts = './data/slevydnes-items.csv';
$csvFilePurchases = './data/slevydnes-purchases.csv';


//$csvFileProducts = './data/shopexpo/setos/iSpace_Items.csv';
//$csvFilePurchases = './data/shopexpo/setos/iSpace_Purchases.csv';
$csvFileProducts = './data/shopexpo/sporilek.cz/product.csv';
$csvFilePurchases = './data/shopexpo/sporilek.cz/order_items.csv';


$db = 'shopexpo-test';
$key = 'DyioS5vct4fyqbjjr7Yno8dUFALYjAZe0JP3yR65aCNdtbjk92F9gxU1yDAVR7QS';

//Instance of Transport class
//$transport = new Recommender\Api\Transport\Transport();
$transport = new Recommender\Api\Transport\Batch();
$transport->setBatchSize(10000);
$transport->setDebug(true);
//$transport->setBatchFileStorePath(__DIR__.'/store/');


//Instance of API Client
$classApiClient = new \Recommender\Api\Client('http://rapi-dev.modgen.net', $db, $key, $transport);
$classApiClient->deleteDb();
$classApiClient->setDebug(true);

//Instance of parser
$classParser = new Recommender\Data\Parser();
$classParser->setDebug(false);

//Set start time
$timerStart = time();




//Parsing XML
//$classParser->parseModgenXml($xmlFileName,$classApiClient);

//Parsing CSV
$classParser->setSkipHeader(true);
$classParser->parseCsvProducts(
    $csvFileProducts,
    $classApiClient,
    array('id', 'name', 'description', 'price', 'available')
);

$classParser->setSkipHeader(true);
$classParser->parseCsvPurchases(
    $csvFilePurchases,
    $classApiClient,
    array('itemId', 'userId', 'timestamp')
);


//print_r( $classParser->getResponse() );

$timerEnd = time();
//Set end time
echo date('H:i:s', $timerEnd - $timerStart)."\n";