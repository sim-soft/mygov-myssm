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
            'Formal' => ['201901000005 (1312525-A)', true, true, 2019, '01', '000005', false, true, '1312525', 'A', false],
            'Formal (Invalid ROC)' => ['201901000005 (131B5D5-A)', true, true, 2019, '01', '000005', false, false, null, null, false],
            'Formal (no space 1)' => ['201202003209(TR018426S)', true, true, 2012, '02', '003209', false, false, null, null, false],
            'Formal (no space 2)' => ['201903003209(TR0184266S)', true, true, 2019, '03', '003209', true, true, 'TR0184266', 'S', true],
            'Formal (no space 3)' => ['201903003209(000184266S)', true, true, 2019, '03', '003209', true, true, '000184266', 'S', true],
            'Formal (with hyphen)' => ['201405002005(1312885-Z)', true, true, 2014, '05', '002005', false, true, '1312885', 'Z', false],
            'Formal (Not ROB)' => ['201409002005(1312885-Z)', true, false, null, null, null, false, true, '1312885', 'Z', false],
            'Formal (ROB only)' => ['2019003209(000184266S)', true, false, null, null, null, false, true, '000184266', 'S', true],
            'Formal (Invalid)' => ['2019003209(00084266S)', false, false, null, null, null, false, false, null, null, false],
            'Formal (Invalid ROB)' => ['197003005359(00001CB39-A)', true, true, 1970, '03', '005359', true, false, null, null, false],

            'BRN2019 1' => ['198501011959', true, true, 1985, '01', '011959', false, false, null, null, false],
            'BRN2019 2' => ['198301007970', true, true, 1983, '01', '007970', false, false, null, null, false],
            'BRN2019 3' => ['202003007970', true, true, 2020, '03', '007970', true, false, null, null, false],
            'BRN2019 (Invalid year)' => ["{$nextYear}01007970", false, false, null, null, null, false, false, null, null, false],
            'BRN2019 (Invalid entity code)' => ["202008007970", false, false, null, null, null, false, false, null, null, false],
            'BRN2019 (Invalid length)' => ["20200007970", false, false, null, null, null, false, false, null, null, false],

            'BRNClassic (ROC) 1' => ['412121K', true, false, null, null, null, false, true, '412121', 'K', false],
            'BRNClassic (ROC) 2' => ['226784T', true, false, null, null, null, false, true, '226784', 'T', false],
            'BRNClassic (ROC) 3' => ['63871D', true, false, null, null, null, false, true, '63871', 'D', false],
            'BRNClassic (ROC) 4' => ['22685U', true, false, null, null, null, false, true, '22685', 'U', false],
            'BRNClassic (ROC) Invalid' => ['bc0053-B', false, false, null, null, null, false, false, null, null, false],

            'BRNClassic (ROB) 1' => ['AC0000003-D', true, false, null, null, null, false, true, 'AC0000003', 'D', true],
            'BRNClassic (ROB) 2' => ['000125034-M', true, false, null, null, null, false, true, '000125034', 'M', true],
            'BRNClassic (ROB) 3' => ['JM0125034-M', true, false, null, null, null, false, true, 'JM0125034', 'M', true],
            'BRNClassic (ROB) 3 with space' => ['JM 0125034-M', true, false, null, null, null, false, true, 'JM0125034', 'M', true],
            'BRNClassic (ROB) Invalid' => ['AA00188141-T', false, false, null, null, null, false, false, null, null, false],
            'BRNClassic (ROB) Invalid 2' => ['AA001Z8141-T', false, false, null, null, null, false, false, null, null, false],

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
        ?bool   $isROB = null,
        ?bool   $isClassicValid = null,
        ?string $classicSequenceNumber = null,
        ?string $classicCheckDigit = null,
        ?bool   $isClassicROB = null
    ): void
    {
        $brn = BRN::parse($brnText);

        $this->assertEquals($isValid, $brn->isValid());
        $this->assertEquals($is2019Valid, $brn->format2019->isValid());
        $this->assertEquals($year, $brn->format2019->getYear());
        $this->assertEquals($entityCode, $brn->format2019->getEntityCode());
        $this->assertEquals($sequenceNumber, $brn->format2019->getSequenceNumber());
        $this->assertEquals($isROB, $brn->format2019->is(EntityCode::Business));

        $this->assertEquals($isClassicValid, $brn->classic->isValid());
        $this->assertEquals($classicSequenceNumber, $brn->classic->getSequenceNumber());
        $this->assertEquals($classicCheckDigit, $brn->classic->getCheckDigit());
        $this->assertEquals($isClassicROB, $brn->classic->is(EntityCode::Business));
    }
}
