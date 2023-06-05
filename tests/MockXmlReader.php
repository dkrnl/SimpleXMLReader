<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare (strict_types=1);

namespace MakinaCorpus\SimpleXMLReader\Tests;

use MakinaCorpus\SimpleXMLReader\SimpleXMLReader;

class MockXmlReader extends SimpleXMLReader
{
    public $output = '';

    public function __construct()
    {
        $this->registerCallback("Price", fn (SimpleXMLReader $reader) => $this->callbackPrice($reader));
        $this->registerCallback("/Data/Balance/Remainder", fn (SimpleXMLReader $reader) => $this->callbackRest($reader));
    }

    protected function callbackPrice(SimpleXMLReader $reader)
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

    protected function callbackRest(SimpleXMLReader $reader)
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
