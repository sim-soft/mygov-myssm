<?php

namespace MyGOV\MySSM\Format\Enums;

/**
 * EntityCode
 */
enum EntityCode: string
{
    case LocalCompany = '01'; // ROC
    case ForeignCompany = '02'; // ROC
    case Business = '03'; // ROB
    case LLP = '04'; // Local Limited liability partnership
    case ForeignLLP = '05';
    case ProfessionalLLP = '06'; // LLP for professional practice.

    /**
     * Get entity type name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return match ($this) {
            self::LocalCompany => 'Local Companies',
            self::ForeignCompany => 'Foreign Companies',
            self::Business => 'Business (ROB)',
            self::LLP => 'Local Limited Liability Partnership',
            self::ForeignLLP => 'Foreign Limited Liability Partnership',
            self::ProfessionalLLP => 'Limited Liability Partnership for Professional practice',
        };
    }
}
