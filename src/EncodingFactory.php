<?php

namespace Danny50610\BpeTokeniser;

use Exception;
use SplFileObject;

class EncodingFactory
{
    protected const ENDOFTEXT = "<|endoftext|>";
    protected const FIM_PREFIX = "<|fim_prefix|>";
    protected const FIM_MIDDLE = "<|fim_middle|>";
    protected const FIM_SUFFIX = "<|fim_suffix|>";
    protected const ENDOFPROMPT = "<|endofprompt|>";

    protected static $modelToEncoding = [
        # chat
        "gpt-4" => "cl100k_base",
        "gpt-3.5-turbo" => "cl100k_base",
        "gpt-35-turbo" => "cl100k_base",  # Azure deployment name
        # text
        "text-davinci-003" => "p50k_base",
        "text-davinci-002" => "p50k_base",
        "text-davinci-001" => "r50k_base",
        "text-curie-001" => "r50k_base",
        "text-babbage-001" => "r50k_base",
        "text-ada-001" => "r50k_base",
        "davinci" => "r50k_base",
        "curie" => "r50k_base",
        "babbage" => "r50k_base",
        "ada" => "r50k_base",
        # code
        "code-davinci-002" => "p50k_base",
        "code-davinci-001" => "p50k_base",
        "code-cushman-002" => "p50k_base",
        "code-cushman-001" => "p50k_base",
        "davinci-codex" => "p50k_base",
        "cushman-codex" => "p50k_base",
        # edit
        "text-davinci-edit-001" => "p50k_edit",
        "code-davinci-edit-001" => "p50k_edit",
        # embeddings
        "text-embedding-ada-002" => "cl100k_base",
        # old embeddings
        "text-similarity-davinci-001" => "r50k_base",
        "text-similarity-curie-001" => "r50k_base",
        "text-similarity-babbage-001" => "r50k_base",
        "text-similarity-ada-001" => "r50k_base",
        "text-search-davinci-doc-001" => "r50k_base",
        "text-search-curie-doc-001" => "r50k_base",
        "text-search-babbage-doc-001" => "r50k_base",
        "text-search-ada-doc-001" => "r50k_base",
        "code-search-babbage-code-001" => "r50k_base",
        "code-search-ada-code-001" => "r50k_base",
        # open source
        "gpt2" => "gpt2",
    ];

    protected static $modelPrefixToEncoding = [
        # chat
        "gpt-4-" => "cl100k_base",          # e.g., gpt-4-0314, etc., plus gpt-4-32k
        "gpt-3.5-turbo-" => "cl100k_base",  # e.g, gpt-3.5-turbo-0301, -0401, etc.
        "gpt-35-turbo" => "cl100k_base",    # Azure deployment name
    ];

    protected static $encodingInstance = [];

    protected static $encodingConstructors = null;

    public static function createByModelName(string $modelName): Encoding
    {
        $encodingName = null;
        if (array_key_exists($modelName, static::$modelToEncoding)) {
            $encodingName = static::$modelToEncoding[$modelName];
        } else {
            // Check if the model matches a known prefix
            // Prefix matching avoids needing library updates for every model version release
            // Note that this can match on non-existent models (e.g., gpt-3.5-turbo-FAKE)
            foreach (static::$modelPrefixToEncoding as $prefix => $targetEncodingName) {
                if (str_starts_with($modelName, $prefix)) {
                    $encodingName = $targetEncodingName;
                    break;
                }
            }
        }

        if (is_null($encodingName)) {
            throw new Exception(
                "Could not automatically map {$modelName} to a tokeniser." +
                "Please use `createByEncodingName` to explicitly get the tokeniser you expect."
            );
        }

        return static::createByEncodingName($encodingName);
    }

    public static function createByEncodingName(string $encodingName): Encoding
    {
        if (array_key_exists($encodingName, static::$encodingInstance)) {
            return static::$encodingInstance[$encodingName];
        }

        static::initConstructor();

        if (!array_key_exists($encodingName, static::$encodingConstructors)) {
            throw new Exception("Unknown encoding {$encodingName}");
        }

        $constructor = static::$encodingConstructors[$encodingName];
        $encoding = $constructor();

        static::$encodingInstance[$encodingName] = $encoding;

        return $encoding;
    }

    protected static function initConstructor()
    {
        if (!is_null(static::$encodingConstructors)) {
            return;
        }

        static::$encodingConstructors = [
            'gpt2' => function () {
                $mergeableRanks = static::loadTiktokenBpe(__DIR__ . '/../assets/gpt2.tiktoken');
                $pattenRegex = "/'s|'t|'re|'ve|'m|'ll|'d| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+/";
                $specialTokens = [
                    self::ENDOFTEXT => 50256,
                ];

                return new Encoding($mergeableRanks, $pattenRegex, $specialTokens, explicitNVocab: 50257);
            },
            'r50k_base' => function () {
                $mergeableRanks = static::loadTiktokenBpe(__DIR__ . '/../assets/r50k_base.tiktoken');
                $pattenRegex = "/'s|'t|'re|'ve|'m|'ll|'d| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+/";
                $specialTokens = [
                    self::ENDOFTEXT => 50256,
                ];

                return new Encoding($mergeableRanks, $pattenRegex, $specialTokens, explicitNVocab: 50257);
            },
            'p50k_base' => function () {
                $mergeableRanks = static::loadTiktokenBpe(__DIR__ . '/../assets/p50k_base.tiktoken');
                $pattenRegex = "/'s|'t|'re|'ve|'m|'ll|'d| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+/";
                $specialTokens = [
                    self::ENDOFTEXT => 50256,
                ];

                return new Encoding($mergeableRanks, $pattenRegex, $specialTokens, explicitNVocab: 50281);
            },
            'p50k_edit' => function () {
                $mergeableRanks = static::loadTiktokenBpe(__DIR__ . '/../assets/p50k_base.tiktoken');
                $pattenRegex = "/'s|'t|'re|'ve|'m|'ll|'d| ?\p{L}+| ?\p{N}+| ?[^\s\p{L}\p{N}]+|\s+(?!\S)|\s+/";
                $specialTokens = [
                    self::ENDOFTEXT => 50256,
                    self::FIM_PREFIX => 50281,
                    self::FIM_MIDDLE => 50282,
                    self::FIM_SUFFIX => 50283,
                ];

                return new Encoding($mergeableRanks, $pattenRegex, $specialTokens);
            },
            'cl100k_base' => function () {
                $mergeableRanks = static::loadTiktokenBpe(__DIR__ . '/../assets/cl100k_base.tiktoken');
                $pattenRegex = "/(?i:'s|'t|'re|'ve|'m|'ll|'d)|[^\r\n\p{L}\p{N}]?\p{L}+|\p{N}{1,3}| ?[^\s\p{L}\p{N}]+[\r\n]*|\s*[\r\n]+|\s+(?!\S)|\s+/";
                $specialTokens = [
                    self::ENDOFTEXT => 100257,
                    self::FIM_PREFIX => 100258,
                    self::FIM_MIDDLE => 100259,
                    self::FIM_SUFFIX => 100260,
                    self::ENDOFPROMPT => 100276,
                ];

                return new Encoding($mergeableRanks, $pattenRegex, $specialTokens);
            },
        ];
    }

    protected static function loadTiktokenBpe(string $filename)
    {
        $file = new SplFileObject($filename);
        $file->setFlags(SplFileObject::DROP_NEW_LINE);

        $mergeableRanks = [];
        while (!$file->eof()) {
            $line = trim($file->fgets());
            if (empty($line)) {
                continue;
            }
            $cell = explode(' ', $line);

            $mergeableRanks[base64_decode($cell[0])] = intval($cell[1]);
        }

        return $mergeableRanks;
    }
}
