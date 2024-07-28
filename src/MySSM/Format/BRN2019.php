<?php

namespace MyGOV\MySSM\Format;

use Exception;
use MyGOV\MySSM\Format\Enums\EntityCode;

/**
 * BRN2019 class.
 *
 * The new BRNClassic format was used since 11 October 2019.
 */
class BRN2019 extends BRNFormat
{
    const REGEX = '/\b\d{12}\b/';   // Extract 12-digit numeric value
    const LENGTH = 12;
    const SEQUENCE_NUM_LENGTH = 6;

    /** @var int|null Year of registration. */
    protected ?int $year = null;

    /** @var string|null Sequence number. */
    protected ?string $sequenceNumber = null;

    /**
     * {@inheritdoc}
     */
    public static function extract(string $brnText): ?string
    {
        if (preg_match(static::REGEX, $brnText, $matches)) {
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

        $this->year = (int)substr($this->brn, 0, 4);

        $today = date_create();
        if ($today === false || $this->year > (int)$today->format('Y')) {
            throw new Exception('Invalid registration year');
        }

        $this->entityType = EntityCode::tryFrom(substr($this->brn, 4, 2));
        if ($this->entityType === null) {
            throw new Exception('Invalid entity type');
        }

        $this->sequenceNumber = substr($this->brn, 6, 6);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function make(?int $year = null, ?EntityCode $entityCode = null): string
    {
        $today = date_create();
        if ($today) {
            $thisYear = (int)$today->format('Y');

            if ($year === null) {
                $year = rand(1950, $thisYear);
            } elseif ($year > $thisYear) {
                throw new Exception('Invalid registration year');
            }
        }

        if ($entityCode === null) {
            $entityCode = EntityCode::cases()[array_rand(EntityCode::cases())];
        }

        return "$year$entityCode->value" . str_pad(
                (string)rand(1, pow(10, static::SEQUENCE_NUM_LENGTH) - 1),
                static::SEQUENCE_NUM_LENGTH, '0', STR_PAD_LEFT);
    }

    /**
     * Get registration year from new BRNClassic.
     *
     * @return int|null
     */
    public function getYear(): ?int
    {
        return $this->isValid() ? $this->year : null;
    }

    /**
     * Get entity code from new BRNClassic.
     *
     * @return string|null
     */
    public function getEntityCode(): ?string
    {
        return $this->entityType?->value;
    }

    /**
     * Get entity type from new BRNClassic.
     *
     * @return string|null
     */
    public function getEntityType(): ?string
    {
        return $this->entityType?->getName();
    }

    /**
     * Get sequence number from new BRNClassic.
     *
     * @return string|null
     */
    public function getSequenceNumber(): ?string
    {
        return $this->sequenceNumber;
    }

}
