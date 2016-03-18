# Simple XML Reader 

Wrapper XMLReader(http://php.net/manual/ru/book.xmlreader.php) class, for simple **SAX**-reading(and simple **XPath**-queries) of **huge**(testing over 1G file) xml.

**Minimum the memory** usage of other xml libraries(SimpleXML, DOMXML).

Usage example 1:
```php
$reader = new SimpleXMLReader;
$reader->open("big.xml");
$reader->registerCallback("by-node-name", function($reader) {
    $element = $reader->expandSimpleXml(); // copy of the current node as a SimpleXMLElement object
    $attributes = $element->attributes(); // read element attributes
    /* ... */
});
$reader->registerCallback("/by/xpath/query", function($reader) {
    $element = $reader->expandDomDocument(); // copy of the current node as a DOMNode object
    $attributes = $element->attributes(); // read element attributes
    /* ... */
});
$reader->parse();
$reader->close();

```
Usage example 2: http://github.com/dkrnl/SimpleXMLReader/blob/master/examples/example1.php

License: Public Domain
