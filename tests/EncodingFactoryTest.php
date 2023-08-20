<?php

namespace Danny50610\BpeTokeniser\Tests;

use Danny50610\BpeTokeniser\Encoding;
use Danny50610\BpeTokeniser\EncodingFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EncodingFactoryTest extends TestCase
{
    public function testCreateByEncodingName()
    {
        $enc = EncodingFactory::createByEncodingName('cl100k_base');

        $this->assertSame('cl100k_base', $enc->getName());
    }

    public function testCreateByEncodingNameWithNonExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown encoding: "danny"');

        EncodingFactory::createByEncodingName('danny');
    }

    public function testCreateByModelName()
    {
        $enc = EncodingFactory::createByModelName('gpt-4');

        $this->assertSame('cl100k_base', $enc->getName());
    }

    public function testCreateByModelNameUsePrefix()
    {
        $enc = EncodingFactory::createByModelName('gpt-3.5-turbo-0301');

        $this->assertSame('cl100k_base', $enc->getName());
    }

    public function testCreateByModelNameWithNonExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not automatically map "danny" to a tokeniser. Please use `createByEncodingName` to explicitly get the tokeniser you expect.');

        EncodingFactory::createByModelName('danny');
    }

    public function testRegisterModelToEncoding()
    {
        EncodingFactory::registerModelToEncoding('gpt-9000', 'cl100k_base');

        $enc = EncodingFactory::createByModelName('gpt-9000');
        $this->assertSame('cl100k_base', $enc->getName());
    }

    public function testRegisterModelToEncodingAlreadyExists()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"gpt-4" already exists in map');

        EncodingFactory::registerModelToEncoding('gpt-4', 'cl100k_base');
    }

    public function testRegisterModelPrefixToEncoding()
    {
        EncodingFactory::registerModelPrefixToEncoding('gpt-9000-', 'cl100k_base');

        $enc = EncodingFactory::createByModelName('gpt-9000-danny');
        $this->assertSame('cl100k_base', $enc->getName());
    }

    public function testRegisterModelPrefixToEncodingAlreadyExists()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Prefix "gpt-4-" already exists in map');

        EncodingFactory::registerModelPrefixToEncoding('gpt-4-', 'cl100k_base');
    }

    public function testRegisterEncoding()
    {
        EncodingFactory::registerEncoding('danny', function () {
            $banks = [];
            return new Encoding('danny', $banks, '', []);
        });
        
        $enc = EncodingFactory::createByEncodingName('danny');
        $this->assertSame('danny', $enc->getName());
    }

    public function testRegisterEncodingAlreadyExists()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"cl100k_base" already exists');

        EncodingFactory::registerEncoding('cl100k_base', function () {
            $banks = [];
            return new Encoding('cl100k_base', $banks, '', []);
        });
    }
}
