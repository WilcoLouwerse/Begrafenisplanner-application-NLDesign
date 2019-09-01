<?php

namespace App\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

use App\ValueObject\UnderInvestigation;

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
		//list($longitude, $latitude) = sscanf($value, 'JSON(%s)');
		$data = json_decode ($value, true);
		$date = $value['date'];
		$properties = $value['properties'];
		return new UnderInvestigation($properties, $date);
	}
	
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if ($value instanceof UnderInvestigation) {			
			$data = ["properties"=> $value->getProperties(),"date"=> $value->getDate()];			
			$value = sprintf("JSON(%s)", json_encode ($data));
		}
		
		return $value;
	}
	
	public function canRequireSQLConversion()
	{
		return true;
	}
	
	public function convertToPHPValueSQL($sqlExpr, AbstractPlatform $platform)
	{
		return sprintf('AsText(%s)', $sqlExpr);
	}
	
	public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
	{
		return sprintf('PointFromText(%s)', $sqlExpr);
	}
}