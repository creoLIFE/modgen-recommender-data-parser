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

    /*
     * @var boolean
     */
    private $debug = false;

    /*
     * @var boolean
     */
    private $skipHeader = false;

    /**
     * @return boolean
     */
    public function isSkipHeader()
    {
        return $this->skipHeader;
    }

    /**
     * @param boolean $skipHeader
     */
    public function setSkipHeader($skipHeader)
    {
        $this->skipHeader = $skipHeader;
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

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
                $apiClient->addPurchase(current($el));
            }
        }
    }

    /**
     * Method will parse Modgen XML data file
     * @param [string] $fileName - path to file to parse
     * @param Client $apiItems - instance of Api Client
     * @param array $structure - Structure definition of CSV
     */
    public function parseCsvProducts($fileName, Client $apiClient, array $structure = array())
    {
        $csv = new ModgenCsv($fileName, $structure, $apiClient, 'addProduct');
        $csv->setSkipHeader( $this->skipHeader );
        $csv->process();
    }

    /**
     * Method will parse Modgen XML data file
     * @param [string] $fileName - path to file to parse
     * @param Client $apiItems - instance of Api Client
     */
    public function parseCsvPurchases($fileName, Client $apiClient, $structure = array())
    {
        $csv = new ModgenCsv($fileName, $structure, $apiClient, 'addPurchase');
        $csv->setSkipHeader( $this->skipHeader );
        $csv->process();
    }


}