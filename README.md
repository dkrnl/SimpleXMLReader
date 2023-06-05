# Simple XML Reader 

Extends XMLReader PHP class, for simple SAX-reading and XPath queries of huge
XML files without consuming memory.

Forked from https://github.com/dkrnl/SimpleXMLReader for adding PHP 8.2 support,
unit tests, and static analysis tooling.

Example usage:

```php
$reader = new SimpleXMLReader;
$reader->open("big.xml");
$reader->registerCallback("by-node-name", function($reader) {
    $element = $reader->expandSimpleXml(); // copy of the current node as a SimpleXMLElement object
    $attributes = $element->attributes(); // read element attributes
    /* ...your code here... */
    return true;
});
$reader->registerCallback("/by/xpath/query", function($reader) {
    $element = $reader->expandDomDocument(); // copy of the current node as a DOMNode object
    $attributes = $element->attributes(); // read element attributes
    /* ...your code here... */
    return true;
});
$reader->parse();
$reader->close();
```

Original package was licenced under *Public Domain*, this fork is licenced
under the MIT licence. All credits for the original code belong to the original
author, *Dmitry Pyatkov* (aka *dkrnl*) <dkrnl@yandex.ru>.
