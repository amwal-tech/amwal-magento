<?php
namespace Amwal\Payments\Lib;

/**
 * Amwal Payments M2 Integration Version
 */
class Version
{
	/**
	 * class constants
	 */
	const MAJOR = 1;
	const MINOR = 0;
	const TINY = 0;

	public function  __construct()
	{
	}

	public static function getVersionString()
	{
		return self::MAJOR . '.' . self::MINOR . '.' . self::TINY;
	}
}