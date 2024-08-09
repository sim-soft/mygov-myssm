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
    const ROB_REGEX = '/\b([A-Z]{2}[\s-]?\d{7}[\s-]?[A-Z]|\d{9}[\s-]?[A-Z])\b/'; // Extract 10 alphanumeric characters, last character is an alphabet
    const ROC_REGEX = '/\b\d{3,7}[\s-]?[A-Z]\b/'; // Extract numeric parts with max 7 digits and last character being an alphabet
    const AF_REGEX = '/\bAF\d{3,6}\b/';
    const LLP_REGEX = '/\bLLP\d{7}[\s-]?(LGN|LCA)\b/';

    // Regex pattern: AZ1234567-A | 123456789-A | 123-A or 1234567-A | LLP1234567-LGN or LLP1234567-LCA | AF123 or AF123456 | LL123-A or LL123456-A
    const REGEX = '/\b([A-Z]{2}[\s-]?\d{7}[\s-]?[A-Z]|\d{9}[\s-]?[A-Z]|\d{3,7}[\s-]?[A-Z]|(LLP)?\d{7}[\s-]?(LGN|LCA)|AF\d{3,6}|L{2}\d{3,6}[\s-]?[A-Z]?)\b/';

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

        $this->brn = str_replace([' ', '-'], '', $this->brn);

        if (str_starts_with($this->brn, 'LLP')
            || str_ends_with($this->brn, 'LGN')
            || str_ends_with($this->brn, 'LCA')
        ) {
            $this->entityType = EntityCode::LLP;
            $this->checkDigit = substr($this->brn, -3);
            $this->sequenceNumber = rtrim($this->brn, $this->checkDigit);
            return true;
        }

        if (str_starts_with($this->brn, 'AF')) {
            $this->entityType = EntityCode::LLP;
            $this->checkDigit = null;
            $this->sequenceNumber = $this->brn;
            return true;
        }

        if (str_starts_with($this->brn, 'LL')) {
            $this->entityType = EntityCode::LocalCompany;
            $this->checkDigit = null;
            $this->sequenceNumber = $this->brn;
            return true;
        }

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
        if ($entityCode === null) {
            $entityCode = [EntityCode::Business, EntityCode::LocalCompany, EntityCode::LLP][rand(0, 2)];
        }

        if ($entityCode === EntityCode::Business) {
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

        if (in_array($entityCode, [EntityCode::LocalCompany, EntityCode::ForeignCompany])) {
            return str_pad(
                    (string)rand(1, pow(10, static::ROC_SEQUENCE_NUM_LENGTH) - 1),
                    static::ROC_SEQUENCE_NUM_LENGTH, '0', STR_PAD_LEFT
                ) . chr(rand(65, 90));
        }

        if (in_array($entityCode, [EntityCode::LLP, EntityCode::ForeignLLP, EntityCode::ProfessionalLLP])) {
            if (rand(0, 1) == 1) {
                return 'LLP'
                    . str_pad(
                        (string)rand(1, pow(10, 7) - 1),
                        7, '0', STR_PAD_LEFT
                    ) . (rand(0, 1) == 1 ? 'LGN' : 'LCA');
            }

            $num = rand(3, 6);
            return 'AF'
                . str_pad(
                    (string)rand(1, pow(10, $num - 2) - 1),
                    $num, '0', STR_PAD_LEFT
                );
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function toFormal(): ?string
    {
        if ($this->isValid()) {
            return $this->checkDigit ? $this->getSequenceNumber() . "-$this->checkDigit" : $this->getSequenceNumber();
        }
        return null;
    }

    /**
     * Get sequence number.
     *
     * @return string|null
     */
    public function getSequenceNumber(): ?string
    {
        if ($this->isValid()) {
            if ($this->leadingZeros
                && $this->entityType !== EntityCode::Business
                && preg_match('/^[1-9]/', (string)$this->sequenceNumber)
            ) {
                return str_pad(
                    (string)$this->sequenceNumber,
                    static::ROC_SEQUENCE_NUM_LENGTH,
                    '0',
                    STR_PAD_LEFT
                );
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
