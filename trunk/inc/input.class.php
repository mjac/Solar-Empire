<?php
defined('PATH_INC') || exit;

/** General class for dealing with input */
class input
{
	/** Standard input array */
	var $std = array();

	/** Get input from the $_REQUEST variable and stripslashes if required */
	function input()
	{
	    $this->std =& $_REQUEST;
		if (get_magic_quotes_gpc() == 1) {
		    input::arrayStripslashes($this->std);
		}
	}

	/** Ascertains whether a standard variable exists */
	function exists(/* multiple arguments */)
	{
	    $varNames = func_get_args();

		foreach ($varNames as $varName) {
		    if (!isset($this->std[$varName])) {
		        return false;
		    }
		}

	    return true;
	}

	/** Performs stripslashes recursively */
	function arrayStripslashes(&$var)
	{
		foreach ($var as $key => $value) {
			if (is_array($value)) {
				input::arrayStripslashes($var[$key]);
			} else {
				$var[$key] = stripslashes($value);
			}
		}
	}


	// INPUT VALIDATION

	/** Deduce whether an e-mail address confirms to ISO standards */
	function isEmail($address)
	{
	    static $addrSpec = '';

		if (empty($addrSpec)) {
			$qText = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
			$dText = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';

			$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e' .
			 '\\x40\\x5b-\\x5d\\x7f-\\xff]+';
			$domainRef =& $atom;

			$quotedPair = '\\x5c[\\x00-\\x7f]';
			$domainLiteral = "\\x5b($dText|$quotedPair)*\\x5d";

			$quotedStr = "\\x22($qText|$quotedPair)*\\x22";
			$word = "($atom|$quotedStr)";

			$subDomain = "($domainRef|$domainLiteral)";
			$domain = "$subDomain(\\x2e$subDomain)*";

			$localPart = "$word(\\x2e$word)*";

			$addrSpec = "$localPart\\x40$domain";
		}

		return preg_match('/^' . $addrSpec . '$/', $address) ? true : false;
	}


	// INTERNAL INPUT

	/** Generate a random string */
	function randomStr($length,
	 $charSrc = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
	{
		$charMax = strlen($charSrc) - 1;
		$newStr = '';

		for ($strIndex = 0; $strIndex < $length; ++$strIndex) {
		    $newStr .= $charSrc[mt_rand(0, $charMax)];
		}

		return $newStr;
	}
}

?>
