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
            $this->assertSame($tokens, $outputTokens, 'Encode error: ' . $text);

            $outputText = $enc->decode($tokens);
            $this->assertSame($text, $outputText, 'Decode error: ' . $text);
        }
    }

    public static function textDataProvider()
    {
        return [
            'gpt2' => [
                'gpt2',
                [
                    ['tiktoken is great!', [83, 1134, 30001, 318, 1049, 0]],
                    ['台北 101 高度 508 公尺', [20998, 108, 44293, 245, 8949, 16268, 45865, 41753, 99, 2026, 23, 10263, 227, 105, 22887, 118]],
                    ['🫡🍣顏文字', [8582, 104, 94, 8582, 235, 96, 165, 94, 237, 23877, 229, 27764, 245]],
                ],
            ],
            'r50k_base' => [
                'r50k_base',
                [
                    ['tiktoken is great!', [83, 1134, 30001, 318, 1049, 0]],
                    ['台北 101 高度 508 公尺', [20998, 108, 44293, 245, 8949, 16268, 45865, 41753, 99, 2026, 23, 10263, 227, 105, 22887, 118]],
                    ['🫡🍣顏文字', [8582, 104, 94, 8582, 235, 96, 165, 94, 237, 23877, 229, 27764, 245]],
                ],
            ],
            'p50k_base' => [
                'p50k_base',
                [
                    ['tiktoken is great!', [83, 1134, 30001, 318, 1049, 0]],
                    ['台北 101 高度 508 公尺', [20998, 108, 44293, 245, 8949, 16268, 45865, 41753, 99, 2026, 23, 10263, 227, 105, 22887, 118]],
                    ['🫡🍣顏文字', [8582, 104, 94, 8582, 235, 96, 165, 94, 237, 23877, 229, 27764, 245]],
                ],
            ],
            'p50k_edit' => [
                'p50k_edit',
                [
                    ['tiktoken is great!', [83, 1134, 30001, 318, 1049, 0]],
                    ['台北 101 高度 508 公尺', [20998, 108, 44293, 245, 8949, 16268, 45865, 41753, 99, 2026, 23, 10263, 227, 105, 22887, 118]],
                    ['🫡🍣顏文字', [8582, 104, 94, 8582, 235, 96, 165, 94, 237, 23877, 229, 27764, 245]],
                ],
            ],
            'cl100k_base' => [
                'cl100k_base',
                [
                    ['It work!!!', [2181, 990, 12340]],
                    ['tiktoken is great!', [83, 1609, 5963, 374, 2294, 0]],
                    ['台北 101 高度 508 公尺', [55038, 49409, 220, 4645, 18630, 41519, 27479, 220, 19869, 35469, 105, 16175, 118]],
                    ['🫡🍣顏文字', [9468, 104, 94, 9468, 235, 96, 14167, 237, 88435]],
                ],
            ]
        ];
    }

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

    // TODO: test: encodeOrdinary === encode($text, disallowedSpecial: [])
}
