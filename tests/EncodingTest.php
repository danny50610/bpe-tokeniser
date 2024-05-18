<?php

namespace Danny50610\BpeTokeniser\Tests;

use Danny50610\BpeTokeniser\Encoding;
use Danny50610\BpeTokeniser\EncodingFactory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ValueError;

class EncodingTest extends TestCase
{
    public function testDuplicateToeknInMergeableRanks()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Encoder and decoder must be of equal length; maybe you had duplicate token indices in your encoder?');

        $mergeableRanks = [
            'a' => 1,
            'b' => 1,
        ];
        new Encoding('test', $mergeableRanks, '', []);
    }

    public function testTotalToeknMismatch()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('explicitNVocab check failed: total token count mismatch');

        $mergeableRanks = [
            'a' => 0,
            'b' => 1,
        ];
        new Encoding('test', $mergeableRanks, '', ['c' => 2], 10); // 10 is wrong
    }

    public function testMaxToeknMismatch()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('explicitNVocab check failed: Max token(10) !== 3 - 1');

        $mergeableRanks = [
            'a' => 0,
            'b' => 1,
        ];
        new Encoding('test', $mergeableRanks, '', ['c' => 10], 3);
    }

    #[DataProvider('textDataProvider')]
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
                    ["台北 101\n自由\r\n黑箱審查\n", [55038, 49409, 220, 4645, 198, 37026, 68171, 319, 57752, 74396, 15973, 102, 33976, 198]],
                ],
            ],
            'o200k_base' => [
                'o200k_base',
                [
                    ['', []],
                    ['It work!!!', [3206, 1101, 10880]],
                    ['tiktoken is great!', [83, 8251, 2488, 382, 2212, 0]],
                    ['台北 101 高度 508 公尺', [3735, 19020, 220, 7959, 26796, 8913, 220, 35911, 52845, 69835]],
                    ['🫡🍣顏文字', [4103, 104, 94, 102415, 96, 5689, 237, 79831]],
                    ["台北 101\n自由\r\n黑箱審查\n", [3735, 19020, 220, 7959, 198, 126584, 370, 16599, 39505, 4298, 102, 13451, 198]],
                    ['给主人留下些什么吧', [177431]],
                ],
            ],
        ];
    }

    #[DataProvider('specialDataProvider')]
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

    public function testDecodeWithSpecial()
    {
        $enc = EncodingFactory::createByEncodingName('cl100k_base');
        $text = $enc->decode([9906, 4435, 100257, 9906, 294, 13184, 13]);

        $this->assertSame('Hello World<|endoftext|>Hello danny.', $text);
    }

    public function testEncodeOrdinary()
    {
        $enc = EncodingFactory::createByEncodingName('cl100k_base');
        $tokens = $enc->encodeOrdinary('Hello world');

        $this->assertSame([9906, 1917], $tokens);
    }

    public function testEncodeOrdinaryWithDisallowedSpecial()
    {
        $enc = EncodingFactory::createByEncodingName('cl100k_base');
        $tokens1 = $enc->encodeOrdinary('🫡🍣顏文字');
        $tokens2 = $enc->encode('🫡🍣顏文字', disallowedSpecial: []);

        $this->assertSame([9468, 104, 94, 9468, 235, 96, 14167, 237, 88435], $tokens1);
        $this->assertSame($tokens1, $tokens2);
    }

    public function testEncodeSpecialCheck()
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Encountered text corresponding to disallowed special token');

        $enc = EncodingFactory::createByEncodingName('cl100k_base');

        $enc->encode('<|endoftext|>');
    }

    public function testEncodeSpecialWithAllow()
    {
        $enc = EncodingFactory::createByEncodingName('cl100k_base');

        $tokens = $enc->encode('<|endoftext|>Hello<|fim_prefix|>', ['<|fim_prefix|>'], []);

        $this->assertSame([27, 91, 8862, 728, 428, 91, 29, 9906, 100258], $tokens);
    }
}
