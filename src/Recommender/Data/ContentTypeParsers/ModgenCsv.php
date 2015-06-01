<?php
/**
 * Created by PhpStorm.
 * User: mirekratman
 * Date: 22/05/15
 * Time: 14:32
 */
namespace Recommender\Data\ContentTypeParsers;

use Recommender\Api\Client;

class ModgenCsv
{
    /*
     * @var string - field delimeter
     */
    private $delimeter = ';';

    /*
     * @var string - Modgen CSV
     */
    private $csv = array();

    /*
     * @var string - CSV line
     */
    private $line = '';

    /**
     * @return string
     */
    public function getDelimeter()
    {
        return $this->delimeter;
    }

    /**
     * @param string $delimeter
     */
    public function setDelimeter($delimeter)
    {
        $this->delimeter = $delimeter;
    }

    /**
     * @return array
     */
    public function getCsv()
    {
        return $this->csv;
    }

    /**
     * @param array $csv
     */
    public function setCsv($csv)
    {
        $this->csv = $csv;
    }

    /**
     * @param array $csv
     */
    public function addToCsv($csv)
    {
        $this->csv[] = $csv;
    }

    /**
     * @return string
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return boolean
     */
    public function isLineEmpty()
    {
        return empty($this->line);
    }

    /**
     * @param string $line
     */
    public function setLine($line)
    {
        $this->line = $line;
    }

    /**
     * Method will parse stream to CSV
     */
    public function addLine($line, array $structure = array(), Client $apiClient, $clientMethod)
    {
        $firstEl = current(explode($this->getDelimeter(), substr($line, 0, 11)));
        $firstEl = trim($firstEl, '"');
        $firstEl = trim($firstEl, '\'');

        if ((int)$firstEl) {
            if (!self::isLineEmpty()) {
                switch ($clientMethod) {
                    case 'addPurchase':
                        $apiClient->addPurchase(self::parseCsvLine($this->getLine(), $structure));
                        break;
                    case 'addProduct':
                        $apiClient->addProduct(self::parseCsvLine($this->getLine(), $structure), 'id');
                        break;
                }

                //$this->addToCsv(self::parseCsvLine($this->getLine(),$structure));
            }
            $this->setLine($line);

        } else {
            $this->line .= $line;
        }
    }

    /**
     * @var string $fileName - file to read
     * @var array $structure - file structure to apply
     */
    public function __construct($fileName, array $structure = array(), Client $apiClient, $clientMethod)
    {
        if (!ini_get("auto_detect_line_endings")) {
            ini_set("auto_detect_line_endings", '1');
        }

        foreach (file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            self::addLine($line, $structure, $apiClient, $clientMethod);
        }
    }

    /**
     * Method will parse CSV line
     * @param string $line
     * @return array
     */
    private function parseCsvLine($line, array $structure = array())
    {
        $out = array();
        $elements = str_getcsv($this->getLine(), $this->getDelimeter());

        foreach ($elements as $key => $l) {
            $l = str_replace(
                array(
                    '"Null"',
                    '"NULL"',
                    '"null"',
                    'Null',
                    'NULL',
                    'null',
                    '"None"',
                    '"NONE"',
                    '"none"',
                    'None',
                    'NONE',
                    'none'
                ),
                '',
                $l
            );
            $l = trim($l, '"');

            if (isset($structure[$key])) {
                $out[$structure[$key]] = $l;
            } else {
                $out[] = $l;
            }
        }
        return $out;
    }


}