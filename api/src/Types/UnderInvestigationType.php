<?php

namespace App\Types;

use App\ValueObject\UnderInvestigation;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class UnderInvestigationType extends Type
{
    const UNDERINVESTIGATION = 'underInvestigation';

    public function getName()
    {
        return self::UNDERINVESTIGATION;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return 'JSON';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // Lets make this nullable
        if (!$value) {
            return;
        }
        //list($longitude, $latitude) = sscanf($value, 'JSON(%s)');
        $value = json_decode($value, true);
        //var_dump($data);
        $date = $value['date'];
        $properties = $value['properties'];

        return new UnderInvestigation($properties, $date);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // Lets make this nullable
        if (!$value) {
            return;
        }
        if ($value instanceof UnderInvestigation) {
            /* @todo throw an error ir the property isn't a boolean*/
            $value = ['properties'=> $value->getProperties(), 'date'=> $value->getDate()];
            $value = json_encode($value);
        } else {
            // lets make sure we have a properties array
            if (!array_key_exists('properties', $value)) {
                $value['properties'] = [];
            }
            // Lets analyse this dataset
            foreach ($value as $key => $property) {
                // lets skip the date and propertieskeys
                if ($key == 'date' || $key == 'properties') {
                    continue;
                }
                /* @todo throw an error ir the property isn't a boolean*/

                // lets add the property to the stack
                $value['properties'][$key] = $property;
                unset($value[$key]);
            }
            $value = json_encode($value);
        }

        return $value;
    }
}
