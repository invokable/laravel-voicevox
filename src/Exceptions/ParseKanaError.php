<?php

declare(strict_types=1);

namespace Revolution\Voicevox\Exceptions;

use Revolution\Voicevox\Enums\ParseKanaErrorCode;
use RuntimeException;

/**
 * AquesTalk 風記法のパースが失敗した。
 */
class ParseKanaError extends RuntimeException
{
    public readonly ParseKanaErrorCode $errorCode;

    public readonly string $errorName;

    /** @var array<string, string> */
    public readonly array $errorArgs;

    public readonly string $errorText;

    /**
     * @param  array<string, string>  $args
     */
    public function __construct(ParseKanaErrorCode $code, array $args = [])
    {
        $this->errorCode = $code;
        $this->errorName = $code->name;
        $this->errorArgs = $args;
        $this->errorText = $code->format($args);

        parent::__construct($this->errorText);
    }
}
