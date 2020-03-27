<?php

namespace App\Types;

use App\ValueObject\IncompleteDate;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class IncompleteDateType extends Type
{
    const INCOMPLETEDATE = 'incompleteDate';

    public function getName()
    {
        return self::INCOMPLETEDATE;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'INTEGER';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // Lets make this nullable
        if (!$value) {
            return;
        }
        // We save incomplete date's as YYYYMMDD integer values so that we can easily index and order on them
        list($year, $month, $day) = sscanf($value, '%04u%02u%02u');

        return new IncompleteDate($year, $month, $day);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // Lets make this nullable
        if (!$value) {
            return;
        }
        // We save incomplete date's as YYYYMMDD integer values so that we can easily index and order on them
        if ($value instanceof IncompleteDate) {
            $value = sprintf('%04u%02u%02u', $value->getYear(), $value->getMonth(), $value->getDay());
        } else {
            if (!array_key_exists('year', $value)) {
                $value['year'] = 0;
            }
            if (!array_key_exists('month', $value)) {
                $value['month'] = 0;
            }
            if (!array_key_exists('day', $value)) {
                $value['day'] = 0;
            }
            $value = sprintf('%04u%02u%02u', (int) $value['year'], (int) $value['month'], (int) $value['day']);
        }

        return $value;
    }
}
