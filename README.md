# bpe-tokeniser

[![PHP Test](https://github.com/danny50610/bpe-tokeniser/actions/workflows/php.yml/badge.svg)](https://github.com/danny50610/php-cid/actions)
[![codecov](https://codecov.io/gh/danny50610/bpe-tokeniser/graph/badge.svg?token=CGORRQ1P6W)](https://codecov.io/gh/danny50610/bpe-tokeniser)
[![Latest Stable Version](http://poser.pugx.org/danny50610/bpe-tokeniser/v)](https://packagist.org/packages/danny50610/bpe-tokeniser)
[![Total Downloads](http://poser.pugx.org/danny50610/bpe-tokeniser/downloads)](https://packagist.org/packages/danny50610/bpe-tokeniser)
[![License](http://poser.pugx.org/danny50610/bpe-tokeniser/license)](https://packagist.org/packages/danny50610/bpe-tokeniser)

PHP port for [openai/tiktoken](https://github.com/openai/tiktoken) (most)

## Installation

```sh
composer require danny50610/bpe-tokeniser
```

## Example

### GPT-4 / GPT-3.5-Turbo (cl100k_base)
```php
use Danny50610\BpeTokeniser\EncodingFactory;

$enc = EncodingFactory::createByEncodingName('cl100k_base');

var_dump($enc->decode($enc->encode("hello world")));
// output: string(11) "hello world"
```

```php
use Danny50610\BpeTokeniser\EncodingFactory;

$enc = EncodingFactory::createByModelName('gpt-3.5-turbo');

var_dump($enc->decode($enc->encode("hello world")));
// output: string(11) "hello world"
```