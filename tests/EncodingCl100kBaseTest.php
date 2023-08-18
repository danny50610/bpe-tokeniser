<?php

namespace Danny50610\BpeTokeniser\Tests;

use Danny50610\BpeTokeniser\EncodingFactory;
use PHPUnit\Framework\TestCase;

class EncodingCl100kBaseTest extends TestCase
{
    /**
     * @dataProvider textDataProvider
     */
    public function testEncodeOrdinary($text, $expectedTokens) {
        $enc = EncodingFactory::createByEncodingName('cl100k_base');
        $tokens = $enc->encodeOrdinary($text);

        $this->assertSame($expectedTokens, $tokens);
    }

    public static function textDataProvider()
    {
        return [
            ['tiktoken is great!', [83, 1609, 5963, 374, 2294, 0]],
            ['台北 101 高度 508 公尺', [55038, 49409, 220, 4645, 18630, 41519, 27479, 220, 19869, 35469, 105, 16175, 118]],
            // TODO: 表情符號
        ];
    }

    // TODO: Encode -> Decode chain
}
