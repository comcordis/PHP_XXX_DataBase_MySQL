<?php

abstract class XXX_DataBase_MySQL_Filter
{	
	////////////////////
	// Filter
	////////////////////
	
	public static function filterString ($string)
	{
		return XXX_String::replace($string, array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z')); 
				
		//return XXX_String::addSlashes($string);
	}
	
	public static function filterInteger ($integer)
	{
		return XXX_Type::makeInteger($integer);
	}
	
	public static function filterFloat ($float)
	{
		return XXX_Type::makeFloat($float);
	}
	
	public static function filterLike ($string)
	{
		$string = self::filterString($string);
		$string = XXX_String::replace($string, array('%', '_'), array('\\%', '\\_'));
		
		return $string;
	}
	
	public static function filterPattern ($string)
	{
		return XXX_String_Pattern::replace($string, '([().\[\]*^$])', '', '\\\$1');
	}
}

?>