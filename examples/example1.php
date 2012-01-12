<?php

require_onde dirname(__FILE__). "/../SimpleXMLReader.php";

class ExampleXmlReader1 extends SimpleXMLReader
{

    public function __construct()
    {
        $this->registerCallback("Цена", array($this, "callbackPrice"));
        $this->registerCallback("Остаток", array($this, "callbackRest"));
    }

    protected function callbackPrice($reader)
    {
        $xml = $reader->expandSimpleXml();
        $attributes = $xml->attributes();
        $ref = (string) $attributes->{"Номенклатура"};
        if ($ref) {
            $price = floatval((string)$xml));
            echo "Цена: $ref = $price;\n";
        }
        return true;
    }

    protected function callbackRest($reader)
    {
        $xml = $reader->expandSimpleXml();
        $attributes = $xml->attributes();
        $ref = (string) $attributes->{"Номенклатура"};
        if ($ref) {
            $rest = floatval((string) $xml));
            echo "Остаток: $ref = $rest;\n";
        }
        return true;
    }

}

$reader = new ExampleXmlReader1;
$reader->open($file);
$reader->parse();
$reader->close();