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

        $dom = new \DOMDocument();
        $dom->recover = TRUE;
        $dom->load($fileName, LIBXML_NOERROR);

        $itemList = $dom->getElementsByTagName('items');
        foreach($itemList as $items) {
            foreach( $items->childNodes as $i ){
                if ($i->hasAttributes()) {
                    $attributes = array('id'=>'', 'name'=>'', 'description'=>'', 'price'=>0, 'available'=>true);
                    foreach ($i->attributes as $attr) {
                        //$attributes[$attr->nodeName] = Encoding::toUTF8($attr->nodeValue);
                        $attributes[$attr->nodeName] = $attr->nodeValue;
                    }
                    //print_r($attributes)."\n";
                    $apiClient->addProduct($attributes, 'id');
                }
            }
        }
        $apiClient->process();

        $itemList = $dom->getElementsByTagName('purchases');
        foreach($itemList as $items) {
            foreach( $items->childNodes as $i ){
                if ($i->hasAttributes()) {
                    $send = true;
                    $attributes = array('itemId'=>'', 'userId'=>'', 'timestamp'=>0);
                    foreach ($i->attributes as $attr) {
                        $val = $attr->nodeValue;
                        if( $attr->nodeName == 'userId' || $attr->nodeName == 'userid' ){
                            $val = preg_replace("/[^A-Za-z0-9 ]/", '_', $attr->nodeValue);
                            if( empty($attr->nodeValue) ){
                                $send = false;
                            }
                        }
                        //$attributes[$attr->nodeName] = Encoding::toUTF8($val);
                        $attributes[$attr->nodeName] = $val;
                    }
                    if( $send ) {
                        //print_r($attributes)."\n";
                        $apiClient->addPurchase($attributes);
                    }
                }
            }
        }
        $apiClient->process();

        //$itemList = $items->childNodes->length;



        //$dom->save($fileName);

        /*
        $xml = file_get_contents($fileName);
        //$xml = preg_replace('/=[\"\']?([\w]+)[\"\']?/','"$1',$xml);
        //$xml = $str = htmlentities($xml,ENT_QUOTES,'UTF-8');
        //$xml = preg_replace('~"true />~','"true" />',$xml);
        //$xml = utf8_encode(self::cleanupXML($xml));
        $xml = Encoding::fixUTF8($xml);
        //$xml = self::cleanupXMLExtended($xml);
        file_put_contents($fileName, $xml);
        */

        /*
        exec( 'tidy -xml -o '.$fileName.' -utf8 -f '. $fileName);

        $reader = new \XMLReader();

        $reader->open($fileName, null, LIBXML_NOERROR);
        $items = new ModgenXml($reader, 'items');
        foreach ($items as $item) {
            foreach ($item as $el) {
                //$apiClient->addProduct(current($el), 'id');
            }
        }
        $apiClient->process();

        $reader->open($fileName);
        $purchases = new ModgenXml($reader, 'purchases');
        foreach ($purchases as $item) {
            foreach ($item as $el) {
                $ce = current($el);
                $ce['userId'] = preg_replace("/[^A-Za-z0-9 ]/", '_', $ce['userId']);
                $apiClient->addPurchase($ce);
            }
        }
        $apiClient->process();
        */

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