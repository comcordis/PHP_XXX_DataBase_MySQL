<?php

/*

TODO setup regression test \" \' escaping (also within stuff), empty "", '', 

*/

abstract class XXX_DataBase_MySQL_Search
{
		
	////////////////////
	// Escaping
	////////////////////
		
	public static function escapeTermsForQuery ($terms)
	{
		$result = array();
		
		// Make sure each term matches on word boundaries so that 'foo' matches 'a foo a' but not 'a food a'. In MySQL regular expression syntax, these are '[[:<:]]' and '[[:>:]]'
		foreach ($terms as $term)
		{
			$result[] = '[[:<:]]' . XXX_DataBase_MySQL_Filter::filterString(XXX_DataBase_MySQL_Filter::filterPattern($term)) . '[[:>:]]';
		}
		
		return $result;
	}
	
	public static function escapeTermsForPattern ($terms)
	{
		$result = array();
		
		// Escape the pattern delimiter and mark it within word boundaries
		foreach ($terms as $term)
		{
			$result[] = '\b' . XXX_String_Pattern::escape($term) . '\b';
		}
		
		return $result;
	}
	
	public static function escapeTermsForHTML ($terms)
	{
		$result = array();
	
		foreach ($terms as $term)
		{
			// If it's a group of words
			if (XXX_String_Pattern::hasMatch($term, '\s|,', ''))
			{
				$result[] = '"' . XXX_String_HTMLEntities::encode($term) . '"';
			}
			// Single words
			else
			{
				$result[] = XXX_String_HTMLEntities::encode($term);
			}
		}
	
		return $result;	
	}
	
	////////////////////
	// Parsing
	////////////////////
	
	public static function parseSearchString ($searchString)
	{
		// Find all pairs of quotes and pass them to parseTerm for processing
		$searchString = XXX_String_Pattern::replace($searchString, '"(.*?[^\\\\])"', 'e', 'XXX_DataBase_MySQL_Search::parseTerm(\'$1\')');
		$searchString = XXX_String_Pattern::replace($searchString, '\'(.*?[^\\\\])\'', 'e', 'XXX_DataBase_MySQL_Search::parseTerm(\'$1\')');
			
		// Remove whitespace around commas
		$searchString = XXX_String_Pattern::replace($searchString, '\s+,', '', ',');
		$searchString = XXX_String_Pattern::replace($searchString, ',\s+', '', ',');
		
		// Split on whitespace and commas
		$searchString = XXX_String_Pattern::splitToArray($searchString, '\s+|,', '');
	
		$terms = array();
		
		// Replace the holding tokens back with their original contents
		foreach ($searchString as $term)
		{
			$term = XXX_String_Pattern::replace($term, '\{WHITESPACE-([0-9]+)\}', 'e', 'XXX_String::asciiCodePointToCharacter($1)');
			$term = XXX_String_Pattern::replace($term, '\{COMMA\}', '', ',');
	
			$terms[] = $term;
		}
	
		return $terms;
	}
	
	public static function parseTerm ($term)
	{
		$term = XXX_String::removeSlashes($term);
		
		echo '{' . $term . '}<br>';
		
		// Replace whitespace with a holder token
		$term = XXX_String_Pattern::replace($term, '(\s)', 'e', '\'{WHITESPACE-\' . XXX_String::characterToASCIICodePoint(\'$1\') . \'}\'');
		
		echo '|' . $term . '|<br>';
		
		// Replace commas with a holder token
		$term = XXX_String_Pattern::replace($term, ',', '', '{COMMA}');
		
		return $term;
	}
		
	////////////////////
	// Searching
	////////////////////
	
	// TODO match at word boundary optional, and wildcards, - etc.
	
	public static function search ($searchString, $column, $connector = 'and')
	{
		$terms = self::parseSearchString($searchString);
		
		$termsEscapedForQuery = self::escapeTermsForQuery($terms);
		$termsEscapedForPattern = self::escapeTermsForPattern($terms);
		$termsEscapedForHTML = self::escapeTermsForHTML($terms);
		
		$highlightedTermsForOverview = self::highlightTermsForOverview($termsEscapedForHTML);
		
		$connector = (XXX_String::convertToLowerCase($connector) == 'or') ? 'OR' : 'AND';
		
		
		$whereClause = array();
		
		// Prepare the WHERE clause for the query
		if (XXX_Type::isArray($column))
		{
			foreach ($column as $realColumn)
			{
				foreach ($termsEscapedForQuery as $termEscapedForQuery)
				{
					$whereClause[] = $realColumn . ' RLIKE "' . $termEscapedForQuery . '"';
				}
			}
		}
		else
		{
			foreach ($termsEscapedForQuery as $termEscapedForQuery)
			{
				$whereClause[] = $column . ' RLIKE "' . $termEscapedForQuery . '"';
			}
		}
		
		$whereClause = XXX_Array::joinValuesToString($whereClause, ' ' . $connector . ' ');
				
		$result = array
		(
		 	'originalString' => $searchString,
			'terms' => $terms,
			'termsEscapedForQuery' => $termsEscapedForQuery,
			'termsEscapedForPattern' => $termsEscapedForPattern,
			'termsEscapedForHTML' => $termsEscapedForHTML,
			'highlightedTermsForOverview' => $highlightedTermsForOverview,
			'whereClause' => $whereClause,
			'columns' => $column
		);
		
		return $result;
	}
	
	public static function applyHighlightingAndSorting ($records, $column, $termsEscapedForPattern)
	{
		foreach ($records as $record)		
		{
			$record['score'] = 0;
			
			if (XXX_Type::isArray($column))
			{			
				foreach ($column as $realColumn)
				{				
					foreach ($termsEscapedForPattern as $termEscapedForPattern)
					{
						$matches = XXX_String_Pattern::getMatches($record[$realColumn], $termEscapedForPattern, 'i');
						
						$record['score'] += XXX_Array::getFirstLevelItemTotal($matches);
					}
					
					// Apply the search highlighting
					$record[$realColumn] = self::highlightTermsInContent($record[$realColumn], $termsEscapedForPattern);
				}
			}
			else
			{		
				foreach ($termsEscapedForPattern as $termEscapedForPattern)
				{
					$matches = XXX_String_Pattern::getMatches($record[$column], $termEscapedForPattern, 'i');
					
					$record['score'] += XXX_Array::getFirstLevelItemTotal($matches);
				}
				
				// Apply the search highlighting
				$record[$column] = self::highlightTermsInContent($record[$column], $termsEscapedForPattern);
			}
	
			$records[] = $record;
		}
				
		// Sort the records
		$records = self::sortResultsByScore($records);
		
		return $records;
	}
	
	////////////////////
	// Sorting
	////////////////////
	
	public static function sortResultsByScore ($records)
	{
		uasort($records, 'XXX_DataBase_MySQL_Search::compareResultScores');
		
		return $records;
	}
	
	// Compare scores
	public static function compareResultScores ($a, $b)
	{
		$ax = $a['score'];
		$bx = $b['score'];
	
		if ($ax == $bx)
		{
			return 0;
		}
		
		return ($ax > $bx) ? -1 : 1;
	}
	
	////////////////////
	// Term highlighting
	////////////////////
	
	public static function applyTermHighlighting ($fragment, $highlight)
	{
		//return XXX_HTML::composeSearchTermHighlighting($fragment, $highlight);
	}
	
	////////////////////
	// Terms in content highlighting
	////////////////////
	
	public static function highlightTermsInContent ($text, $termsForPattern)
	{
		$tagStart = '(^|<(?:.*?)>)';		
		$tagEnd = '($|<(?:.*?)>)';
	
		return XXX_String_Pattern::replace($text, $tagStart . '(.*?)' . $tagEnd, 'se', 'XXX_String::removeSlashes(\'\\1\') . ' . 'XXX_DataBase_MySQL_Search::highlightTermInContent(XXX_String::removeSlashes(\'\\2\'), $termsForPattern) . ' . 'XXX_String::removeSlashes(\'\\3\')');
	}
		
	public static function highlightTermInContent ($text, $termsForPattern)
	{
		$highlight = 1;
	
		foreach ($termsForPattern as $termForPattern)
		{	
			$text = XXX_String_Pattern::replace($text, '(' . $termForPattern . ')', 'ise', 'XXX_DataBase_MySQL_Search::applyTermHighlighting(XXX_String::addSlashes(\'\\1\'), $highlight)');
			
			++$highlight;
		}
	
		return $text;
	}
	
	////////////////////
	// Terms in overview highlighting
	////////////////////
	
	public static function highlightTermsForOverview ($termsEscapedForHTML)
	{
		$highlight = 1;
		
		$result = array();
	
		foreach ($termsEscapedForHTML as $termEscapedForHTML)
		{
			$result[] = self::applyTermHighlighting($termEscapedForHTML, $highlight);
			
			++$highlight;
		}
	
		return $result;
	}
}

?>