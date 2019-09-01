<?php

namespace App\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

use App\ValueObject\IncompleteDate;

class IncompleteDateType extends Type
{
	const INCOMPLETEDATE = 'incompleteDate';
	
	public function getName()
	{
		return self::INCOMPLETEDATE;
	}
	
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return 'INT(8)';
	}
	
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		// We save incomplete date's as YYYYMMDD integer values so that we can easily index and order on them
		list($year, $month, $day) = sscanf($value, '[%04u][%02u][%02u]');
		
		return new IncompleteDate($year, $month, $day);
	}
	
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		// We save incomplete date's as YYYYMMDD integer values so that we can easily index and order on them
		if ($value instanceof IncompleteDate) {
			$value = sprintf('INT([%04u][%02u][%02u])', $value->getYear(), $value->getMonth(), $value->getDay());
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