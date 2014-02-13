<?php

/**
 * Simple XML Reader
 *
 * @license Public Domain
 * @author Dmitry Pyatkov(aka dkrnl) <dkrnl@yandex.ru>
 * @url http://github.com/dkrnl/SimpleXMLReader
 */
class SimpleXMLReader extends XMLReader
{

    /**
     * Callbacks
     *
     * @var array
     */
    protected $callback = array();


    /**
     * Depth
     *
     * @var int
     */
    protected $currentDepth = 0;


    /**
     * Previos depth
     *
     * @var int
     */
    protected $prevDepth = 0;


    /**
     * Stack of the parsed nodes
     *
     * @var array
     */
    protected $nodesParsed = array();


    /**
     * Stack of the node types
     *
     * @var array
     */
    protected $nodesType = array();


    /**
     * Stack of node position
     *
     * @var array
     */
    protected $nodesCounter = array();


    /**
     * Add node callback
     *
     * @param  string   $xpath
     * @param  callback $callback
     * @param  integer  $nodeType
     * @return SimpleXMLReader
     */
    public function registerCallback($xpath, $callback, $nodeType = XMLREADER::ELEMENT)
    {
        if (isset($this->callback[$nodeType][$xpath])) {
            throw new Exception("Already exists callback '$xpath':$nodeType.");
        }
        if (!is_callable($callback)) {
            throw new Exception("Not callable callback '$xpath':$nodeType.");
        }
        $this->callback[$nodeType][$xpath] = $callback;
        return $this;
    }


    /**
     * Remove node callback
     *
     * @param  string  $xpath
     * @param  integer $nodeType
     * @return SimpleXMLReader
     */
    public function unRegisterCallback($xpath, $nodeType = XMLREADER::ELEMENT)
    {
        if (!isset($this->callback[$nodeType][$xpath])) {
            throw new Exception("Unknow parser callback '$xpath':$nodeType.");
        }
        unset($this->callback[$nodeType][$xpath]);
        return $this;
    }

    /**
     * Moves cursor to the next node in the document.
     *
     * @link http://php.net/manual/en/xmlreader.read.php
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function read()
    {
        $read = parent::read();       
        if ($this->depth < $this->prevDepth) {
            if (!isset($this->nodesParsed[$this->depth])) {
                throw new Exception("Invalid xml: missing items in SimpleXMLReader::\$nodesParsed");
            }
            if (!isset($this->nodesCounter[$this->depth])) {
                throw new Exception("Invalid xml: missing items in SimpleXMLReader::\$nodesCounter");
            }
            if (!isset($this->nodesType[$this->depth])) {
                throw new Exception("Invalid xml: missing items in SimpleXMLReader::\$nodesType");
            }
            $this->nodesParsed = array_slice($this->nodesParsed, 0, $this->depth + 1, true);
            $this->nodesCounter = array_slice($this->nodesCounter, 0, $this->depth + 1, true);
            $this->nodesType = array_slice($this->nodesType, 0, $this->depth + 1, true);
        }
        if (isset($this->nodesParsed[$this->depth]) && $this->localName == $this->nodesParsed[$this->depth] && $this->nodeType == $this->nodesType[$this->depth]) {
            $this->nodesCounter[$this->depth] = $this->nodesCounter[$this->depth] + 1;
        } else {
            $this->nodesParsed[$this->depth] = $this->localName;
            $this->nodesType[$this->depth] = $this->nodeType;
            $this->nodesCounter[$this->depth] = 1;
        }
        $this->prevDepth = $this->depth;       
        return $read;
    }

    /**
     * Return current xpath node
     *
     * @param boolean $nodesCounter
     * @return string
     */
     public function currentXpath($nodesCounter = false)
     {
        if (count($this->nodesCounter) != count($this->nodesParsed) && count($this->nodesCounter) != count($this->nodesType)) {
            throw new Exception("Empty reader");
        }
        $result = "";
        foreach ($this->nodesParsed as $depth => $name) {
            switch ($this->nodesType[$depth]) {
                case self::ELEMENT:
                    $result .= "/" . $name;
                    if ($nodesCounter) {
                        $result .= "[" . $this->nodesCounter[$depth] . "]";
                    }
                    break;

                case self::TEXT:
                case self::CDATA:
                    $result .= "/text()";
                    break;

                case self::COMMENT:
                    $result .= "/comment()";
                    break;

                case self::ATTRIBUTE:
                    $result .= "[@{$name}]";
                    break;
            }
        }
        return $result;
    }


    /**
     * Run parser
     *
     * @return void
     */
    public function parse()
    {
        if (empty($this->callback)) {
            throw new Exception("Empty parser callback.");
        }
        $continue = true;
        while ($continue && $this->read()) {
            if (!isset($this->callback[$this->nodeType])) {
                continue;
            }
            if (isset($this->callback[$this->nodeType][$this->name])) {
                $continue = call_user_func($this->callback[$this->nodeType][$this->name], $this);
            } else {
                $xpath = $this->currentXpath(false); // without node counter
                if (isset($this->callback[$this->nodeType][$xpath])) {
                    $continue = call_user_func($this->callback[$this->nodeType][$xpath], $this);
                } else {
                    $xpath = $this->currentXpath(true); // with node counter
                    if (isset($this->callback[$this->nodeType][$xpath])) {
                        $continue = call_user_func($this->callback[$this->nodeType][$xpath], $this);
                    }
                }
            }
        }
    }

    /**
     * Run XPath query on current node
     *
     * @param  string $path
     * @param  string $version
     * @param  string $encoding
     * @param  string $className
     * @return array(SimpleXMLElement)
     */
    public function expandXpath($path, $version = "1.0", $encoding = "UTF-8", $className = null)
    {     
        return $this->expandSimpleXml($version, $encoding, $className)->xpath($path);
    }

    /**
     * Expand current node to string
     *
     * @param  string $version
     * @param  string $encoding
     * @param  string $className
     * @return SimpleXMLElement
     */
    public function expandString($version = "1.0", $encoding = "UTF-8", $className = null)
    {
        return $this->expandSimpleXml($version, $encoding, $className)->asXML();
    }

    /**
     * Expand current node to SimpleXMLElement
     *
     * @param  string $version
     * @param  string $encoding
     * @param  string $className
     * @return SimpleXMLElement
     */
    public function expandSimpleXml($version = "1.0", $encoding = "UTF-8", $className = null)
    {
        $element = $this->expand();
        $document = new DomDocument($version, $encoding);
        if ($element instanceof DOMCharacterData) {
            $nodeName = array_splice($this->nodesParsed, -2, 1);
            $nodeName = (isset($nodeName[0]) && $nodeName[0] ? $nodeName[0] : "root");
            $node = $document->createElement($nodeName);
            $node->appendChild($element);
            $element = $node;
        }
        $node = $document->importNode($element, true);
        $document->appendChild($node);
        return simplexml_import_dom($node, $className);
    }

    /**
     * Expand current node to DomDocument
     *
     * @param  string $version
     * @param  string $encoding
     * @return DomDocument
     */
    public function expandDomDocument($version = "1.0", $encoding = "UTF-8")
    {
        $element = $this->expand();
        $document = new DomDocument($version, $encoding);
        if ($element instanceof DOMCharacterData) {
            $nodeName = array_splice($this->nodesParsed, -2, 1);
            $nodeName = (isset($nodeName[0]) && $nodeName[0] ? $nodeName[0] : "root");
            $node = $document->createElement($nodeName);
            $node->appendChild($element);
            $element = $node;
        }
        $node = $document->importNode($element, true);
        $document->appendChild($node);
        return $document;
    }

}
