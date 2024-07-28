<?php

namespace MyGOV\MySSM;

use MyGOV\MySSM\Exceptions\GenerateBRNException;
use MyGOV\MySSM\Exceptions\ParseBRNException;
use MyGOV\MySSM\Exceptions\InvalidBRNException;
use MyGOV\MySSM\Exceptions\InvalidBRNFormatException;
use MyGOV\MySSM\Format\BRN2019;
use MyGOV\MySSM\Format\BRNClassic;
use MyGOV\MySSM\Format\BRNFormat;
use MyGOV\MySSM\Format\Enums\EntityCode;
use Throwable;

/**
 * BRNClassic (Business Registration Number) class
 *
 * @property BRN2019 $format2019
 * @property BRNClassic $classic
 */
class BRN
{
    /** @var BRNFormat[] New business registration number. */
    protected array $brn = [];

    /**
     * Constructor.
     *
     * @param string $brnText Business registration number.
     * @param bool $exception Enable throw exception. Default: false.
     * @throws Throwable
     */
    final public function __construct(protected string $brnText, protected bool $exception = false)
    {
        $this->parsing();
    }

    /**
     * Get BRNClassic parser.
     *
     * @param string $brnText Business registration number.
     * @param bool $exception Enable throw exception. Default: false.
     * @return static
     * @throws Throwable
     */
    public static function parse(string $brnText, bool $exception = false): static
    {
        return new static($brnText, $exception);
    }

    /**
     * Analysis the BRNClassic provided.
     *
     * @return void
     * @throws Throwable
     */
    protected function parsing(): void
    {
        $brn = strtoupper(str_replace('-', '', $this->brnText));

        try {
            $result = BRN2019::extract($brn);
            $this->brn['format2019'] = new BRN2019($result);

            $result = BRNClassic::extract($brn);
            $this->brn['classic'] = new BRNClassic($result);

        } catch (Throwable $throwable) {
            $this->exception && throw new ParseBRNException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }

        if (!$this->isValid()) {
            $this->exception && throw new InvalidBRNException('Invalid business registration number');
        }
    }

    /**
     * Determine is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->brn['format2019']->isValid() || $this->brn['classic']->isValid();
    }

    /**
     * Get official format.
     *
     * @return string|null
     */
    public function toFormal(): ?string
    {
        $formal = [];
        foreach ($this->brn as $brn) {
            if ($brn->isValid()) {
                $formal[] = $brn;
            }
        }

        $lastKey = array_key_last($formal);
        if ($lastKey > 0) {
            $formal[$lastKey] = '(' . $formal[$lastKey] . ')';
        }

        return implode(' ', $formal);
    }

    /**
     * Get official format.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->toFormal();
    }

    /**
     * Get BRN format.
     *
     * @param string $name
     * @return BRNFormat
     * @throws Throwable
     */
    public function __get(string $name): BRNFormat
    {
        if (!array_key_exists($name, $this->brn)) {
            throw new InvalidBRNFormatException("Unrecognized BRN format: '$name'");
        }

        return $this->brn[$name];
    }

    /**
     * Generate random BRN.
     *
     * @param int|null $year Year of registration.
     * @param EntityCode|null $entityCode Generate BRN from the given entity code.
     * @param bool $exception Enable throw exception.
     * @return static|null
     */
    public static function make(
        ?int        $year = null,
        ?EntityCode $entityCode = null,
        bool        $exception = true
    ): ?static
    {
        try {

            return new static(implode(' ', [
                BRN2019::make($year, $entityCode),
                BRNClassic::make($year, $entityCode),
            ]), exception: $exception);

        } catch (Throwable $throwable) {
            $exception && throw new GenerateBRNException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
        return null;
    }
}
