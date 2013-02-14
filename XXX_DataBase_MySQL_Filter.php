<?php

abstract class XXX_DataBase_MySQL_Filter
{	
	////////////////////
	// Filter
	////////////////////
	
	public static function filterString ($string)
	{
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string); 
				
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
		return XXX_String::replace($string, array('%', '_'), array('\\%', '\\_'));
	}
	
	public static function filterPattern ($string)
	{
		return XXX_String_Pattern::replace($string, '([().\[\]*^$])', '', '\\\$1');
	}
}

?>