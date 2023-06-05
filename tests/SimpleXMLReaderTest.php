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

use PHPUnit\Framework\TestCase;

/**
 * Tests are incomplete but at least, they give the opportunity for PHP to
 * raise deprecation notices and warnings on load.
 */
class SimpleXMLReaderTest extends TestCase
{
    public function testRead(): void
    {
        $file = __DIR__ . "/MockXmlReader.xml";
        $reader = new MockXmlReader();
        $reader->open($file);
        $reader->parse();
        $reader->close();

        self::assertSame(
            <<<TXT
            /Data/Balance/Remainder: 5d073611-edf1-11de-8022-00145e1874c3 = 13;
            /Data/Balance/Remainder: 5d073611-edf1-11de-8022-00145e1874c3 = 9;
            /Data/Balance/Remainder: 8d4a52fc-a0b7-11dd-80f6-00145e1874c3 = 1;
            /Data/Balance/Remainder: 8d4a52fc-a0b7-11dd-80f6-00145e1874c3 = 1;
            /Data/Prices/Price: cf30e8cc-2313-11dd-8042-00145e1874c3 = 339.9;
            /Data/Prices/Price: 1134fbf3-af8c-11db-b37e-00145e1874c3 = 5482.9;
            /Data/Prices/Price: 1134fbf6-af8c-11db-b37e-00145e1874c3 = 4569.9;
            /Data/Prices/Price: 6988bfc1-af8c-11db-b37e-00145e1874c3 = 174.9;
            TXT,
            \trim($reader->output)
        );
    }
}
