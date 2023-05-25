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
     * @var callable[]
     */
    protected array $callback = [];

    /**
     * Depth
     */
    protected int $currentDepth = 0;

    /**
     * Previos depth
     */
    protected int $prevDepth = 0;

    /**
     * Stack of the parsed nodes
     */
    protected array $nodesParsed = [];

    /**
     * Stack of the node types
     */
    protected array $nodesType = [];

    /**
     * Stack of node position
     */
    protected array $nodesCounter = [];

    /**
     * Do not remove redundant white space.
     */
    public bool $preserveWhiteSpace = true;

    /**
     * Add node callback
     */
    public function registerCallback(string $xpath, callable $callback, int $nodeType = XMLReader::ELEMENT): static
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
     */
    public function unRegisterCallback(string $xpath, int $nodeType = XMLReader::ELEMENT): static
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
    public function read(): bool
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
     */
     public function currentXpath(bool $nodesCounter = false): string
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
     */
    public function parse(): void
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
     * @return array<SimpleXMLElement>|false|null
     */
    public function expandXpath(string $path, string $version = "1.0", string $encoding = "UTF-8", ?string $className = null): array|false|null
    {
        return $this->expandSimpleXml($version, $encoding, $className)->xpath($path);
    }

    /**
     * Expand current node to string
     */
    public function expandString(string $version = "1.0", string $encoding = "UTF-8", ?string $className = null): string|false
    {
        return $this->expandSimpleXml($version, $encoding, $className)->asXML();
    }

    /**
     * Expand current node to SimpleXMLElement
     */
    public function expandSimpleXml(string $version = "1.0", string $encoding = "UTF-8", ?string $className = null): \SimpleXMLElement
    {
        $element = $this->expand();
        $document = new DomDocument($version, $encoding);
        $document->preserveWhiteSpace = $this->preserveWhiteSpace;
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
     */
    public function expandDomDocument(string $version = "1.0", string $encoding = "UTF-8"): \DOMDocument
    {
        $element = $this->expand();
        $document = new DomDocument($version, $encoding);
        $document->preserveWhiteSpace = $this->preserveWhiteSpace;
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
