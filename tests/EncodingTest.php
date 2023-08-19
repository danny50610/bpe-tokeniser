<?php

namespace Danny50610\BpeTokeniser\Tests;

use Danny50610\BpeTokeniser\EncodingFactory;
use PHPUnit\Framework\TestCase;

class EncodingTest extends TestCase
{
    /**
     * @dataProvider textDataProvider
     */
    public function testEncodeAndDecode($encodingName, $testCaseList)
    {
        $enc = EncodingFactory::createByEncodingName($encodingName);

        foreach ($testCaseList as $testCase) {
            [$text, $tokens] = $testCase;

            $outputTokens = $enc->encode($text);
            $this->assertSame($tokens, $outputTokens);

            $outputText = $enc->decode($tokens);
            $this->assertSame($text, $outputText);
        }
    }

    public static function textDataProvider()
    {
        return [
            [
                'cl100k_base',
                [
                    ['tiktoken is great!', [83, 1609, 5963, 374, 2294, 0]],
                    ['台北 101 高度 508 公尺', [55038, 49409, 220, 4645, 18630, 41519, 27479, 220, 19869, 35469, 105, 16175, 118]],
                    ['🫡🍣顏文字', [9468, 104, 94, 9468, 235, 96, 14167, 237, 88435]],
                ],
            ]
        ];
    }

    // TODO: test: encodeOrdinary === encode($text, disallowedSpecial=[])

    /**
     * @dataProvider specialDataProvider
     */
    public function testEncodeWithSpecial($encodingName, $testCaseList)
    {
        $enc = EncodingFactory::createByEncodingName($encodingName);

        foreach ($testCaseList as $testCase) {
            [$text, $tokens] = $testCase;

            $outputTokens = $enc->encode($text, allowedSpecial: 'all');
            $this->assertSame($tokens, $outputTokens);
        }
    }

    public static function specialDataProvider()
    {
        return [
            [
                'cl100k_base',
                [
                    ['<|endoftext|>', [100257]],
                    ['Hello World<|endoftext|>Hello danny.', [9906, 4435, 100257, 9906, 294, 13184, 13]],
                    ['中文 <|endoftext|> 博大精深 aaa <|endofprompt|> bbbb', [16325, 17161, 220, 100257, 67621, 248, 27384, 90397, 122, 85315, 109, 84565, 220, 100276, 293, 54251]],
                ],
            ],
        ];
    }
}
