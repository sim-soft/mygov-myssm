<?php

namespace Tests;

use DateTimeZone;
use MyGOV\MySSM\BRN;
use MyGOV\MySSM\Format\Enums\EntityCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * BRNTest
 */
class BRNTest extends TestCase
{
    /**
     * Data provider.
     *
     * @return array[]
     */
    public static function dataProvider(): array
    {
        $today = date_create(timezone: new DateTimeZone('Asia/Kuala_Lumpur'));
        $nextYear = (int)$today->format('Y') + 1;

        return [
            'Formal' => ['201901000005 (1312525-A)', true, true, 2019, '01', '000005', true, '1312525', 'A', EntityCode::LocalCompany],
            'Formal (Invalid ROC)' => ['201901000005 (131B5D5-A)', true, true, 2019, '01', '000005', false, null, null, EntityCode::LocalCompany],
            'Formal (no space 1)' => ['201202003209(TR018426S)', true, true, 2012, '02', '003209', false, null, null, EntityCode::ForeignCompany],
            'Formal (no space 2)' => ['201903003209(TR0184266S)', true, true, 2019, '03', '003209', true, 'TR0184266', 'S', EntityCode::Business],
            'Formal (no space 3)' => ['201903003209(000184266S)', true, true, 2019, '03', '003209', true, '000184266', 'S', EntityCode::Business],
            'Formal (with hyphen)' => ['201401002005(1312885-Z)', true, true, 2014, '01', '002005', true, '1312885', 'Z', EntityCode::LocalCompany],
            'Formal (Not ROB)' => ['201409002005(1312885-Z)', true, false, null, null, null, true, '1312885', 'Z', EntityCode::LocalCompany],
            'Formal (ROB only)' => ['2019003209(000184266S)', true, false, null, null, null, true, '000184266', 'S', EntityCode::Business],
            'Formal (Invalid)' => ['2019003209(00084266S)', false, false, null, null, null, false, null, null, null],
            'Formal (Invalid ROB)' => ['197003005359(00001CB39-A)', true, true, 1970, '03', '005359', false, null, null, EntityCode::Business],

            'BRN2019 1' => ['198501011959', true, true, 1985, '01', '011959', false, null, null, EntityCode::LocalCompany],
            'BRN2019 2' => ['198301007970', true, true, 1983, '01', '007970', false, null, null, EntityCode::LocalCompany],
            'BRN2019 3' => ['202003007970', true, true, 2020, '03', '007970', false, null, null, EntityCode::Business],
            'BRN2019 (Invalid year)' => ["{$nextYear}01007970", false, false, null, null, null, false, null, null, null],
            'BRN2019 (Invalid entity code)' => ["202008007970", false, false, null, null, null, false, null, null, null],
            'BRN2019 (Invalid length)' => ["20200007970", false, false, null, null, null, false, null, null, null],

            'BRNClassic (ROC) 1' => ['412121K', true, false, null, null, null, true, '412121', 'K', EntityCode::LocalCompany],
            'BRNClassic (ROC) 2' => ['226784T', true, false, null, null, null, true, '226784', 'T', EntityCode::LocalCompany],
            'BRNClassic (ROC) 3' => ['63871D', true, false, null, null, null, true, '63871', 'D', EntityCode::LocalCompany],
            'BRNClassic (ROC) 4' => ['22685U', true, false, null, null, null, true, '22685', 'U', EntityCode::LocalCompany],
            'BRNClassic (ROC) Invalid' => ['bc0053-B', false, false, null, null, null, false, null, null, null],

            'BRNClassic (ROB) 1' => ['AC0000003-D', true, false, null, null, null, true, 'AC0000003', 'D', EntityCode::Business],
            'BRNClassic (ROB) 2' => ['000125034-M', true, false, null, null, null, true, '000125034', 'M', EntityCode::Business],
            'BRNClassic (ROB) 3' => ['JM0125034-M', true, false, null, null, null, true, 'JM0125034', 'M', EntityCode::Business],
            'BRNClassic (ROB) 3 with space' => ['JM 0125034-M', true, false, null, null, null, true, 'JM0125034', 'M', EntityCode::Business],
            'BRNClassic (ROB) Invalid' => ['AA00188141-T', false, false, null, null, null, false, null, null, null],
            'BRNClassic (ROB) Invalid 2' => ['AA001Z8141-T', false, false, null, null, null, false, null, null, null],

            'BRNClassic (LLP-LGN)' => ['LLP0027514-LGN', true, false, null, null, null, true, 'LLP0027514', 'LGN', EntityCode::LLP],
            'BRNClassic (LLP-LCA)' => ['LLP1234567-LCA', true, false, null, null, null, true, 'LLP1234567', 'LCA', EntityCode::LLP],
            'BRNClassic (LLP-old-LGN)' => ['1234567-LGN', true, false, null, null, null, true, '1234567', 'LGN', EntityCode::LLP],
            'BRNClassic (LLP-old-LCA)' => ['1234567-LCA', true, false, null, null, null, true, '1234567', 'LCA', EntityCode::LLP],
            'BRNClassic (LLP-old-LGN) Invalid' => ['134567-LCA', false, false, null, null, null, false, null, null, null],
            'BRNClassic (LLP-old-LCA) Invalid' => ['123457-LCA', false, false, null, null, null, false, null, null, null],
            'BRNClassic (LLP-LCA) Invalid' => ['LLP12345-LCA', false, false, null, null, null, false, null, null, null],
            'BRNClassic (LLP-LCA) Invalid 2' => ['LLP1234567-ABC', false, false, null, null, null, false, null, null, null],
            'BRNClassic (LLP-AF)' => ['AF123456', true, false, null, null, null, true, 'AF123456', null, EntityCode::LLP],
            'BRNClassic (LLP-AF 2)' => ['AF1234', true, false, null, null, null, true, 'AF1234', null, EntityCode::LLP],
            'BRNClassic (LLP-AF) Invalid' => ['AF12', false, false, null, null, null, false, null, null, null],
            'BRNClassic (LL)' => ['LL123456', true, false, null, null, null, true, 'LL123456', null, EntityCode::LocalCompany],
            'BRNClassic (LL) 2' => ['LL12345', true, false, null, null, null, true, 'LL12345', null, EntityCode::LocalCompany],
            'BRNClassic (LL) 3' => ['LL123', true, false, null, null, null, true, 'LL123', null, EntityCode::LocalCompany],
            'BRNClassic (LL) Invalid' => ['LL1234567', false, false, null, null, null, false, null, null, null],
            'BRNClassic (LL) Invalid 2' => ['LL12', false, false, null, null, null, false, null, null, null],

            /*
            'BRNClassic 7' => ['AS0103548-D'],
            'BRNClassic 8' => ['IP0179579-V'],
            'BRNClassic 9' => ['SA0016342-U'],
            'BRNClassic 10' => ['TR0064050-T'],
            'BRNClassic 11' => ['KT0200873-T'],
            'BRNClassic 12' => ['MA0114799-V'],
            'BRNClassic 13' => ['PG0123417-T'],
            'BRNClassic 14' => ['bc0053-B'],
            'BRNClassic 15' => ['LL04610-L'],
            'BRNClassic 16' => ['CA0019787-A'],
            'BRNClassic 17' => ['RA0113743-V'],

            'BRNClassic 18' => ['LA0001354-H'],
            'BRNClassic 19' => ['NS0088451-M'],
            'BRNClassic 20' => ['JR0164463-W'],
            'BRNClassic 21' => ['CT0087161-V'],
            'BRNClassic 22' => ['UT0040306-P'],
            'BRNClassic 23' => ['AA00188141-T'],*/
        ];
    }

    /**
     * @param string $brnText
     * @param bool $isValid
     * @param bool|null $is2019Valid
     * @param int|null $year
     * @param string|null $entityCode
     * @param string|null $sequenceNumber
     * @param bool|null $isClassicValid
     * @param string|null $classicSequenceNumber
     * @param string|null $classicCheckDigit
     * @param EntityCode|null $entityType
     * @throws Throwable
     */
    #[DataProvider('dataProvider')]
    public function testBRN(
        string  $brnText,
        bool    $isValid,
        ?bool   $is2019Valid = null,
        ?int    $year = null,
        ?string $entityCode = null,
        ?string $sequenceNumber = null,

        ?bool   $isClassicValid = null,
        ?string $classicSequenceNumber = null,
        ?string $classicCheckDigit = null,

        ?EntityCode $entityType = null
    ): void
    {
        $brn = BRN::parse($brnText);

        $this->assertEquals($isValid, $brn->isValid());
        $this->assertEquals($is2019Valid, $brn->format2019->isValid());
        $this->assertEquals($year, $brn->format2019->getYear());
        $this->assertEquals($entityCode, $brn->format2019->getEntityCode());
        $this->assertEquals($sequenceNumber, $brn->format2019->getSequenceNumber());

        if ($brn->format2019->isValid()) {
            $this->assertEquals($brn->format2019->getEntityType(), $entityType);
        }

        $this->assertEquals($isClassicValid, $brn->classic->isValid());
        $this->assertEquals($classicSequenceNumber, $brn->classic->getSequenceNumber());
        $this->assertEquals($classicCheckDigit, $brn->classic->getCheckDigit());

        if ($brn->classic->isValid()) {
            $this->assertEquals($brn->classic->getEntityType(), $entityType);
        }
    }
}
