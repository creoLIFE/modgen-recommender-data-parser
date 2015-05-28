<?php
/**
 * Created by PhpStorm.
 * User: mirekratman
 * Date: 21/05/15
 * Time: 23:16
 */

/**
 * Class responsible of parsing data files for Modgen Recommendation
 * @package Recommender
 * @copyright Modgen s.r.o.
 * @author Mirek Ratman
 * @version 1.0
 * @since 2015-05-21
 * @license Modgen s.r.o.
 */

namespace Recommender\Data;

use Recommender\Data\ContentTypeParsers\ModgenXml;
use Recommender\Api\Client;
use Recommender\Data\ContentTypeParsers\ModgenCsv;

class Parser
{

    /**
     * Method will parse Modgen XML data file
     * @param [string] $fileName - path to file to parse
     * @param Client $apiItems - instance of Api Client
     */
    public function parseModgenXml($fileName, Client $apiClient)
    {
        $reader = new \XMLReader();

        $reader->open($fileName);
        $items = new ModgenXml($reader, 'items');
        foreach ($items as $item) {
            foreach ($item as $el) {
                $apiClient->addProduct(current($el), 'id');
            }
        }

        $reader->open($fileName);
        $purchases = new ModgenXml($reader, 'purchases');
        foreach ($purchases as $item) {
            foreach ($item as $el) {
                $apiClient->addPurchases(current($el));
            }
        }
    }

    /**
     * Method will parse Modgen XML data file
     * @param [string] $fileName - path to file to parse
     * @param Client $apiItems - instance of Api Client
     * @param array $structure - Structure definition of CSV
     */
    public function parseCsvProducts($fileName, Client $apiClient, $structure = array(), $getStructureFromHeader = false)
    {
        $csv = new ModgenCsv($fileName, $structure);
        foreach ($csv->getCsv() as $key => $data) {
            //print_r($data);
            //print_r('-----<br><br>');
            $apiClient->addProduct($data, 'id');
        };
    }

    /**
     * Method will parse Modgen XML data file
     * @param [string] $fileName - path to file to parse
     * @param Client $apiItems - instance of Api Client
     */
    public function parseCsvPurchases($fileName, Client $apiClient, $structure = array(), $getStructureFromHeader = false)
    {
        $csv = new ModgenCsv($fileName, $structure);
        foreach ($csv->getCsv() as $key => $data) {
            //print_r($data);
            //print_r('-----<br><br>');
            $apiClient->addPurchases($data, 'id');
        };
    }


}