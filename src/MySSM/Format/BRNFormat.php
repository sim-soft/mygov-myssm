<?php

namespace MyGOV\MySSM\Format;

use MyGOV\MySSM\Format\Enums\EntityCode;
use Throwable;

/**
 * BRNFormat class
 */
abstract class BRNFormat
{
    /** @var bool Show leading zeros in */
    protected bool $leadingZeros = false;

    /** @var EntityCode|null Entity type. */
    protected ?EntityCode $entityType = null;

    /** @var bool Determine is valid */
    protected bool $isValid = false;

    /** @var string|null Error message if any */
    protected ?string $errorMessage = null;

    /**
     * Extract BRN from text.
     *
     * @param string $brnText
     * @return string|null
     */
    abstract public static function extract(string $brnText): ?string;

    /**
     * Parse BRN.
     *
     * @throws Throwable
     */
    abstract protected function parse(): bool;

    /**
     * Generate a random BRN.
     *
     * @param int|null $year Year of registration.
     * @param EntityCode|null $entityCode Entity type to be generated.
     * @return string
     * @throws Throwable
     */
    abstract public static function make(?int $year = null, ?EntityCode $entityCode = null): string;

    /**
     * Constructor
     *
     * @param string|null $brn Business registration number.
     */
    final public function __construct(protected ?string $brn)
    {
        try {
            $this->isValid = $this->parse();
        } catch (Throwable $throwable) {
            $this->errorMessage = $throwable->getMessage();
        }
    }

    /**
     * Enable leading zeros in sequence number.
     *
     * @param bool $enable Enable leading zeros.
     * @return $this
     */
    public function leadingZeros(bool $enable = true): static
    {
        $this->leadingZeros = $enable;
        return $this;
    }

    /**
     * Determine is the format valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Determine is ROB.
     *
     * @param EntityCode $entityType EntityCode to be checked.
     * @return bool
     */
    public function is(EntityCode $entityType): bool
    {
        return $this->isValid() && $this->entityType === $entityType;
    }

    /**
     * Get entity type.
     *
     * @return EntityCode|null
     */
    public function getEntityType(): ?EntityCode
    {
        return $this->isValid() ? $this->entityType : null;
    }

    /**
     * Get error message.
     *
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Get formal format.
     *
     * @return string|null
     */
    public function toFormal(): ?string
    {
        return $this->brn;
    }

    /**
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->toFormal();
    }

}
