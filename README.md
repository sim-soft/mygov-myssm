# MySSM Business Registration Number (BRN) Parser

A simple MySSM business registration number and validator.

## Install

```sh
composer require my-gov/myssm
```

## Basic Usage

Use MySSM BRN parser to retrieve basic information from a given business
registration number.

```php
require 'vendor/autoload.php';

use MyGOV\MySSM\BRN;
use MyGOV\MySSM\Exceptions\ParseBRNException;
use MyGOV\MySSM\Exceptions\InvalidBRNException;
use MyGOV\MySSM\Exceptions\InvalidBRNFormatException;
use MyGOV\MySSM\Format\Enums\EntityCode;
use Throwable;

try {
    $brn = BRN::parse('201901000005 (1312525-A)');
    if ($brn->isValid()) {
        echo $brn;                                      // 201901000005 (1312525-A)
        echo $brn->format2019;                          // 201901000005
        echo $brn->format2019->isValid();               // true
        echo $brn->format2019->getYear();               // 2019
        echo $brn->format2019->getEntityCode();         // 01
        echo $brn->format2019->getEntityType();         // Local Companies
        echo $brn->format2019->getSequenceNumber();     // 000005
        echo $brn->format2019->is(EntityCode::Business);// false

        echo $brn->classic;                             // 1312525-A
        echo $brn->classic->isValid();                  // true
        echo $brn->classic->getSequenceNumber();        // 1312525
        echo $brn->classic->getCheckDigit();            // A
        echo $brn->classic->is(EntityCode::Business);   // false
    }

    $brn = BRN::parse('SA0173178-P/ 202003000005');
    if ($brn->isValid()) {
        echo $brn;                                      // 202003000005 (SA0173178-P)
        echo $brn->format2019;                          // 202003000005
        echo $brn->format2019->isValid();               // true
        echo $brn->format2019->getYear();               // 2020
        echo $brn->format2019->getEntityCode();         // 03
        echo $brn->format2019->getEntityType();         // Business (ROB)
        echo $brn->format2019->getSequenceNumber();     // 000005
        echo $brn->format2019->is(EntityCode::Business);// true

        echo $brn->classic;                             // SA0173178-P
        echo $brn->classic->isValid();                  // true
        echo $brn->classic->getSequenceNumber();        // SA0173178
        echo $brn->classic->getCheckDigit();            // P
        echo $brn->classic->is(EntityCode::Business);   // true
    }

    $brn = BRN::parse('SA0173178-P');
    if ($brn->isValid()) {
        echo $brn;                                      // SA0173178-P
        echo $brn->format2019;                          // null
        echo $brn->format2019->isValid();               // false
        echo $brn->format2019->getYear();               // null
        echo $brn->format2019->getEntityCode();         // null
        echo $brn->format2019->getEntityType();         // null
        echo $brn->format2019->getSequenceNumber();     // null
        echo $brn->format2019->is(EntityCode::Business);// false

        echo $brn->classic;                             // SA0173178-P
        echo $brn->classic->isValid();                  // true
        echo $brn->classic->getSequenceNumber();        // SA0173178
        echo $brn->classic->getCheckDigit();            // P
        echo $brn->classic->is(EntityCode::Business);   // true
    }

    $brn = BRN::parse('3178-Q');
    if ($brn->isValid()) {
        echo $brn;                                          // 0003178-Q
        echo $brn->format2019;                              // null
        echo $brn->format2019->isValid();                   // false
        echo $brn->format2019->getYear();                   // null
        echo $brn->format2019->getEntityCode();             // null
        echo $brn->format2019->getEntityType();             // null
        echo $brn->format2019->getSequenceNumber();         // null
        echo $brn->format2019->is(EntityCode::Business);    // false

        echo $brn->classic;                                         // 3178-P
        echo $brn->classic->isValid();                              // true
        echo $brn->classic->leadingZeros();                         // 0003178-P
        echo $brn->classic->getSequenceNumber();                    // 3178
        echo $brn->classic->leadingZeros()->getSequenceNumber();    // 0003178  show leading zeros.
        echo $brn->classic->getCheckDigit();                        // P
        echo $brn->classic->is(EntityCode::Business);               // false
    }
} catch (Throwable $throwable) {
    echo $throwable->getMessage();
}
```

## Turn On the Exceptions

Set `exception` to **true** for enable `Exceptions`

```php
require 'vendor/autoload.php';

use MyGOV\MySSM\BRN;

try {
    $brn = BRN::parse('201901003209 (000184266S)', exception: true);

    if ($brn->isValid()) {
        echo $brn->classic->getSequenceNumber();   // 000184266
        echo $brn->classic->getCheckDigit();       // S
    } else {
        echo $brn->classic->getSequenceNumber();   // null
        echo $brn->classic->getCheckDigit();       // null
    }
} catch (ParseBRNException | InvalidBRNException | InvalidBRNFormatException | Throwable $throwable) {
    echo $throwable->getMessage();
}
```

## Business Registration Number (BRN) Generator

Generate a random business registration number.

```php
require 'vendor/autoload.php';

use MyGOV\MySSM\Exceptions\GenerateBRNException;
use MyGOV\MySSM\Format\Enums\EntityCode;
use MyGOV\MySSM\BRN;

try {
    $brn = BRN::make();
    if ($brn->isValid()) {
        echo $brn;                                      // 202003000005 (SA0173178-P)
        echo $brn->format2019;                          // 202003000005
        echo $brn->format2019->isValid();               // true
        echo $brn->format2019->getYear();               // 2020
        echo $brn->format2019->getEntityCode();         // 03
        echo $brn->format2019->getEntityType();         // Business (ROB)
        echo $brn->format2019->getSequenceNumber();     // 000005
        echo $brn->format2019->is(EntityCode::Business);// true

        echo $brn->classic;                             // SA0173178-P
        echo $brn->classic->isValid();                  // true
        echo $brn->classic->getSequenceNumber();        // SA0173178
        echo $brn->classic->getCheckDigit();            // P
        echo $brn->classic->is(EntityCode::Business);   // true
    }
} catch(GenerateBRNException | Throwable Throwable) {
    echo $throwable->getMessage();
}

```

Generate a BRN with year of registration.

```php
$brn = BRN::make(year: 2010);
if ($brn->isValid()) {
    echo $brn;                                      // 201001000005 (SA0173178-P)
    echo $brn->format2019;                          // 201001000005
    echo $brn->format2019->isValid();               // true
    echo $brn->format2019->getYear();               // 2010
    echo $brn->format2019->getEntityCode();         // 01
    echo $brn->format2019->getEntityType();         // Local Companies
    echo $brn->format2019->getSequenceNumber();     // 000005
    echo $brn->format2019->is(EntityCode::Business);// false

    echo $brn->classic;                             // SA0173178-P
    echo $brn->classic->isValid();                  // true
    echo $brn->classic->getSequenceNumber();        // SA0173178
    echo $brn->classic->getCheckDigit();            // P
    echo $brn->classic->is(EntityCode::Business);   // false
}
```

Generate a ROB BRN.

```php
$brn = BRN::make(entityCode: EntityCode::Business);
if ($brn->isValid()) {
    echo $brn;                                      // 199003000005 (SA0173178-P)
    echo $brn->format2019;                          // 199003000005
    echo $brn->format2019->isValid();               // true
    echo $brn->format2019->getYear();               // 1990
    echo $brn->format2019->getEntityCode();         // 03
    echo $brn->format2019->getEntityType();         // Business (ROB)
    echo $brn->format2019->getSequenceNumber();     // 000005
    echo $brn->format2019->is(EntityCode::Business);// true

    echo $brn->classic;                             // SA0173178-P
    echo $brn->classic->isValid();                  // true
    echo $brn->classic->getSequenceNumber();        // SA0173178
    echo $brn->classic->getCheckDigit();            // P
    echo $brn->classic->is(EntityCode::Business);   // true
}
```

## License

The Simsoft MyGOV/MySSM is licensed under the MIT License. See
the [LICENSE](LICENSE) file for details
