<?php

declare (strict_types=1);

namespace SimpleXMLReader\Tests;

class MockXmlReader extends \SimpleXMLReader
{
    public $output = '';

    public function __construct()
    {
        $this->registerCallback("Price", fn (\SimpleXMLReader $reader) => $this->callbackPrice($reader));
        $this->registerCallback("/Data/Balance/Remainder", fn (\SimpleXMLReader $reader) => $this->callbackRest($reader));
    }

    protected function callbackPrice(\SimpleXMLReader $reader)
    {
        $xml = $reader->expandSimpleXml();
        $attributes = $xml->attributes();
        $ref = (string) $attributes->{"Item"};
        if ($ref) {
            $price = \floatval((string)$xml);
            $xpath = $this->currentXpath();
            $this->output .= "$xpath: $ref = $price;\n";
        }
        return true;
    }

    protected function callbackRest(\SimpleXMLReader $reader)
    {
        $xml = $reader->expandSimpleXml();
        $attributes = $xml->attributes();
        $ref = (string) $attributes->{"Item"};
        if ($ref) {
            $rest = \floatval((string) $xml);
            $xpath = $this->currentXpath();
            $this->output .= "$xpath: $ref = $rest;\n";
        }
        return true;
    }
}
