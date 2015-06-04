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
use ForceUTF8\Encoding;

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

        libxml_use_internal_errors(true);

        $xml = file_get_contents($fileName);
        preg_match('/encoding="([^"]*)"/', substr($xml, 0, 100), $matches);
        if (isset($matches[1]) && $matches[1] == 'windows-1250') {
            //$xml = preg_replace('/<\?xml[^>]+\/>/im', '', $xml);
            $xml = iconv('windows-1250', "windows-1250//ignore", $xml);
            //$xml = iconv('utf-8', "utf-8", $xml);
        }
        $xml = self::cleanupXML($xml);

        $dom = new \DOMDocument();
        $dom->recover = TRUE;
        $dom->loadXml($xml);

        $itemList = $dom->getElementsByTagName('items');
        foreach ($itemList as $items) {
            $productsCount = $items->childNodes->length;

            foreach ($items->childNodes as $i) {
                if ($i->hasAttributes()) {
                    $attributes = array('id' => '', 'name' => '', 'description' => '', 'price' => 0, 'available' => true);
                    foreach ($i->attributes as $attr) {
                        //$attributes[$attr->nodeName] = Encoding::toUTF8($attr->nodeValue);
                        $attributes[$attr->nodeName] = $attr->nodeValue;
                        //$attributes[$attr->nodeName] = utf8_decode($attr->nodeValue);
                    }
                    //print_r($attributes)."\n";
                    $apiClient->addProduct($attributes, 'id');
                }
            }
            if ($productsCount > 0) {
                $apiClient->process();
            }
        }

        $itemList = $dom->getElementsByTagName('purchases');
        foreach ($itemList as $items) {
            $purchasesCount = $items->childNodes->length;

            foreach ($items->childNodes as $i) {
                if ($i->hasAttributes()) {
                    $send = true;
                    $attributes = array('itemId' => '', 'userId' => '', 'timestamp' => 0);
                    foreach ($i->attributes as $attr) {
                        $val = $attr->nodeValue;
                        if ($attr->nodeName == 'userId' || $attr->nodeName == 'userid') {
                            $val = preg_replace("/[^A-Za-z0-9 ]/", '_', $attr->nodeValue);
                            if (empty($attr->nodeValue)) {
                                $send = false;
                            }
                        }
                        //$attributes[$attr->nodeName] = Encoding::toUTF8($val);
                        $attributes[$attr->nodeName] = $val;
                    }
                    if ($send) {
                        //print_r($attributes)."\n";
                        $apiClient->addPurchase($attributes);
                    }
                }
            }
            if ($purchasesCount > 0) {
                $apiClient->process();
            }
        }

        libxml_clear_errors();
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
        $csv->setSkipHeader($this->skipHeader);
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
        $csv->setSkipHeader($this->skipHeader);
        $csv->process();
    }

    private function cleanupXML($xml)
    {
        return str_replace('><', ">\n<", $xml);
    }

    private function cleanupXMLExtended($xml)
    {
        $xmlOut = '';
        $inTag = false;
        $xmlLen = strlen($xml);
        for ($i = 0; $i < $xmlLen; ++$i) {
            $char = $xml[$i];
            // $nextChar = $xml[$i+1];
            switch ($char) {
                case '<':
                    if (!$inTag) {
                        // Seek forward for the next tag boundry
                        for ($j = $i + 1; $j < $xmlLen; ++$j) {
                            $nextChar = $xml[$j];
                            switch ($nextChar) {
                                case '<':  // Means a < in text
                                    $char = htmlentities($char);
                                    break 2;
                                case '>':  // Means we are in a tag
                                    $inTag = true;
                                    break 2;
                            }
                        }
                    } else {
                        $char = htmlentities($char);
                    }
                    break;
                case '>':
                    if (!$inTag) {  // No need to seek ahead here
                        $char = htmlentities($char);
                    } else {
                        $inTag = false;
                    }
                    break;
                default:
                    if (!$inTag) {
                        $char = htmlentities($char);
                    }
                    break;
            }
            $xmlOut .= $char;
        }
        return $xmlOut;
    }
}