<?php

namespace MyGOV\MySSM\Format;

use MyGOV\MySSM\Format\Enums\EntityCode;

/**
 * BRNClassic class
 *
 * Class BRNClassic format.
 */
class BRNClassic extends BRNFormat
{
    const ROB_REGEX = '/\b([A-Z]{2}\s?\d{7}[A-Z]|\d{9}[A-Z])\b/'; // Extract 10 alphanumeric characters, last character is an alphabet
    const ROC_REGEX = '/\b[0-9]\d{0,6}[A-Z]\b/'; // Extract numeric parts with max 7 digits and last character being an alphabet

    const ROB_MAX_LENGTH = 10;
    const ROB_SEQUENCE_NUM_LENGTH = 9;
    const ROC_SEQUENCE_NUM_LENGTH = 7;

    /** @var string|null Sequence number */
    protected ?string $sequenceNumber = null;

    /** @var string|null The check digit. */
    protected ?string $checkDigit = null;

    /**
     * {@inheritdoc}
     */
    public static function extract(string $brnText): ?string
    {
        if (preg_match(static::ROB_REGEX, $brnText, $matches)
            || preg_match(static::ROC_REGEX, $brnText, $matches)
        ) {
            return $matches[0];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    protected function parse(): bool
    {
        if (!$this->brn) {
            return false;
        }

        $this->brn = str_replace(' ', '', $this->brn);
        $this->entityType = strlen($this->brn) === static::ROB_MAX_LENGTH
            ? EntityCode::Business
            : EntityCode::LocalCompany;
        $this->checkDigit = substr($this->brn, -1);
        $this->sequenceNumber = rtrim($this->brn, $this->checkDigit);

        if ($this->entityType !== EntityCode::Business) {
            $this->sequenceNumber = ltrim($this->sequenceNumber, '0');
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function make(?int $year = null, ?EntityCode $entityCode = null): string
    {
        if (in_array($entityCode, [EntityCode::LocalCompany, EntityCode::ForeignCompany])) {
            return str_pad(
                    (string)rand(1, pow(10, static::ROC_SEQUENCE_NUM_LENGTH) - 1),
                    static::ROC_SEQUENCE_NUM_LENGTH, '0', STR_PAD_LEFT
                ) . chr(rand(65, 90));
        }

        if (rand(0, 1) == 1) {
            return chr(rand(65, 90)) . chr(rand(65, 90))
                . str_pad(
                    (string)rand(1, pow(10, static::ROB_SEQUENCE_NUM_LENGTH - 2) - 1),
                    static::ROB_SEQUENCE_NUM_LENGTH - 2, '0', STR_PAD_LEFT
                ) . chr(rand(65, 90));
        }

        return str_pad(
                (string)rand(1, pow(10, static::ROB_SEQUENCE_NUM_LENGTH) - 1),
                static::ROB_SEQUENCE_NUM_LENGTH, '0', STR_PAD_LEFT
            ) . chr(rand(65, 90));
    }

    /**
     * {@inheritdoc}
     */
    public function toFormal(): ?string
    {
        return $this->isValid() ? $this->getSequenceNumber() . "-$this->checkDigit" : null;
    }

    /**
     * Get sequence number.
     *
     * @return string|null
     */
    public function getSequenceNumber(): ?string
    {
        if ($this->isValid()) {
            if ($this->entityType !== EntityCode::Business && $this->leadingZeros) {
                return str_pad((string)$this->sequenceNumber, static::ROC_SEQUENCE_NUM_LENGTH, '0', STR_PAD_LEFT);
            }
            return $this->sequenceNumber;
        }
        return null;
    }

    /**
     * Get check digit.
     *
     * @return string|null
     */
    public function getCheckDigit(): ?string
    {
        return $this->checkDigit;
    }

    /**
     * {@inheritdoc}
     */
    public function is(EntityCode $entityType): bool
    {
        if ($this->isValid()) {
            if (in_array($entityType, [EntityCode::LocalCompany, EntityCode::ForeignCompany])) {
                return $this->entityType === EntityCode::LocalCompany;
            }

            return $this->entityType === $entityType;
        }

        return false;
    }

}
