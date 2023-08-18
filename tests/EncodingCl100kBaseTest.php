<?php

namespace Danny50610\BpeTokeniser\Tests;

use Danny50610\BpeTokeniser\EncodingFactory;
use PHPUnit\Framework\TestCase;

class EncodingCl100kBaseTest extends TestCase
{
    public function test() {
        $enc = EncodingFactory::createByEncodingName('cl100k_base');
        $tokens = $enc->encodeOrdinary('tiktoken is great!');

        $this->assertSame([83, 1609, 5963, 374, 2294, 0], $tokens);
    }
}
