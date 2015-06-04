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
     * @var array - Modgen API Client responses
     */
    private $response = array();

    /*
     * @var string - CSV line
     */
    private $line = '';

    /*
    * @var boolean
    */
    private $debug = false;

    /*
    * @var mixed
    */
    private $apiClient;

    /*
    * @var mixed
    */
    private $apiMethod;

    /*
    * @var boolean
    */
    private $skipHeader = false;

    /*
 * @var string
 */
    private $inputEncoding = 'utf-8';

    /*
     * @var string
     */
    private $outputEncoding = 'utf-8';

    /**
     * @return string
     */
    public function getInputEncoding()
    {
        return $this->inputEncoding;
    }

    /**
     * @param string $inputEncoding
     */
    public function setInputEncoding($inputEncoding)
    {
        $this->inputEncoding = $inputEncoding;
    }

    /**
     * @return string
     */
    public function getOutputEncoding()
    {
        return $this->outputEncoding;
    }

    /**
     * @param string $outputEncoding
     */
    public function setOutputEncoding($outputEncoding)
    {
        $this->outputEncoding = $outputEncoding;
    }

    /**
     * @return mixed
     */
    public function getSkipHeader()
    {
        return $this->skipHeader;
    }

    /**
     * @param mixed $skipHeader
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
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @param array $response
     */
    public function addResponse($id, $response)
    {
        $this->response[] = array(
            'id' => $id,
            'response' => $response
        );
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
     * @var string $line
     * @return boolean
     */
    private function detectFirstField($line)
    {
        $firstEl = current(explode($this->getDelimeter(), substr($line, 0, 11)));
        $firstEl = trim($firstEl, '"');
        $firstEl = trim($firstEl, '\'');
        //$firstEl = preg_replace("/[^0-9]/", "", $firstEl);

        /*
        preg_match('/[^a-zA-Z0-9]+/', $firstEl,$matches);
        print_r('--->'. !preg_match('/[^a-zA-Z0-9]+/', $firstEl) . "\n");

        print_r($matches);
        print_r("\n");
        */
        return !preg_match('/[^a-zA-Z0-9]+/', $firstEl) && !empty($firstEl) ? true : false;
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

        $this->apiClient = $apiClient;
        $this->apiMethod = $clientMethod;

        foreach (file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            self::addLine($line, $structure);
        }
    }

    /**
     * Method will parse stream to CSV
     */
    public function addLine($line, array $structure = array())
    {
        if ($this->getSkipHeader() ){
            $this->setSkipHeader(false);
            return;
        }

        switch(strtolower($this->getInputEncoding())){
            case '0':
                break;
            case 'utf-8':
                $line = utf8_encode($line);
                break;
            default:
                $line = iconv($this->getInputEncoding(), $this->getOutputEncoding(), $line);
                break;
        }

        if ($this->detectFirstField($line) ) {
            if (!self::isLineEmpty()) {
                $parsedLine = self::parseCsvLine($structure);
                //print_r($parsedLine['id']);
                //if( strpos($this->getLine(), '27504')){
                    //echo "27504";
                //};
                //if( $parsedLine['id'] >= '4680' && $parsedLine['id'] <= '5680') {
                    switch ($this->apiMethod) {
                        case 'addPurchase':
                            $this->apiClient->addPurchase($parsedLine);
                            break;
                        case 'addProduct':
                            $this->apiClient->addProduct($parsedLine, 'id');
                            break;
                    }
                //r}

                //Discontinued
                //$this->addToCsv(self::parseCsvLine($this->getLine(),$structure));
            }
            //$this->setLine($line);
            $this->setLine($line);

        } else {
            $this->line .= $line;
        }
    }


    /**
     * @return JSON
     */
    public function process()
    {
        $result = $this->apiClient->process();

        if ($this->isDebug()) {
            print_r(date('H:m:s', time()));
            print_r($result);
            print_r('<br>');
        }

        return $result;
    }

    /**
     * Method will parse CSV line
     * @return array
     */
    private function parseCsvLine(array $structure = array())
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