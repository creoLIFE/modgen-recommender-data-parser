<?php
/**
 * Created by PhpStorm.
 * User: mirekratman
 * Date: 22/05/15
 * Time: 14:32
 */
namespace Recommender\Data\ContentTypeParsers;

//temporary
require(__dir__ . '/../Extended/XMLReaderIterators.php');

class ModgenXml extends \XMLElementIterator {

    /**
     * @var [XMLElementIterator] - XMLElementIterator instance
     */
    //protected $reader;
    public function __construct(\XMLReader $reader, $groupName) {
        parent::__construct($reader, $groupName);
    }

    /**
     * Get current element
     * @return SimpleXMLElement
     */
    public function current() {
        return simplexml_load_string( $this->reader->readOuterXml() );
    }
}