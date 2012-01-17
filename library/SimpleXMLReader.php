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
     * Add node callback
     *
     * @param  string   $name
     * @param  callback $callback
     * @param  integer  $nodeType
     * @return SimpleXMLReader
     */
    public function registerCallback($name, $callback, $nodeType = XMLREADER::ELEMENT)
    {
        if (isset($this->callback[$nodeType][$name])) {
            throw new Exception("Already exists callback $name($nodeType).");
        }
        if (!is_callable($callback)) {
            throw new Exception("Already exists parser callback $name($nodeType).");
        }
        $this->callback[$nodeType][$name] = $callback;
        return $this;
    }

    /**
     * Remove node callback
     *
     * @param  string  $name
     * @param  integer $nodeType
     * @return SimpleXMLReader
     */
    public function unRegisterCallback($name, $nodeType = XMLREADER::ELEMENT)
    {
        if (!isset($this->callback[$nodeType][$name])) {
            throw new Exception("Unknow parser callback $name($nodeType).");
        }
        unset($this->callback[$nodeType][$name]);
        return $this;
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
            if (isset($this->callback[$this->nodeType][$this->name])) {
                $continue = call_user_func($this->callback[$this->nodeType][$this->name], $this);
            }
        }
    }

    /**
     * Run XPath query on current node
     *
     * @param  string $path
     * @param  string $version
     * @param  string $encoding
     * @return array(SimpleXMLElement)
     */
    public function expandXpath($path, $version = "1.0", $encoding = "UTF-8")
    {
        return $this->expandSimpleXml($version, $encoding)->xpath($path);
    }

    /**
     * Expand current node to string
     *
     * @param  string $version
     * @param  string $encoding
     * @return SimpleXMLElement
     */
    public function expandString($version = "1.0", $encoding = "UTF-8")
    {
        return $this->expandSimpleXml($version, $encoding)->asXML();
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
        $node = $document->importNode($element, true);
        $document->appendChild($node);
        return $document;
    }

}