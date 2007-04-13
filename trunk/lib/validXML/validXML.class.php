<?php

/**
 * Produces and validates XML documents
 * @author Michael J.A. Clark <mjac@mjac.co.uk>
 *
 * Designed to be extended to enable support for different DTDs such as 
 * XHTML 1.0 Strict.
 */
class validXML
{
	/**
	 * Accepted elements
	 *
	 * If a sub-array is provided then attributes are also allowed; otherwise
	 * the parser will assume any attribute is invalid.	 Left blank in the base 
	 * class so that there is a low overhead.	 
	 */
	var $accept = array();

	/** Validates XML data */
	function validate($xml)
	{
		return $this->fine($this->problems($xml));
	}

	/** Whether the result from $this->problems indicates conformity */
	function fine($result)
	{
		return $result !== false &&
		 (isset($result['elem']) ? empty($result['elem']) : true) && 
		 (isset($result['attr']) ? empty($result['attr']) : true);
	}

	/**
	 * Validates the document and returns invalid elements
	 * 
	 * @retval false If a parse error occured or an array of invalid elements
	 * and attributes otherwise
	 * @retval array If parsed correctly an array is returned containing 
	 * errors for attributes and elements; if these arrays are empty then the 
	 * document has validated correctly.	 	 	 	 
	 */	 	 	
	function problems($xml)
	{
		$parser = xml_parser_create('UTF-8');

		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);

		$values = array();
		$tags = array();
		if (!xml_parse_into_struct($parser, $xml, $values, $tags)) {
		    return false;
		}
		xml_parser_free($parser);

		// Report invalid elements and attributes
		$invalid = array('elem' => array(), 'attr' => array());

		// Stack of allowed elements
		$allowed = array(&$this->accept);

		foreach ($values as $no => $elem) {
			$current =& $allowed[count($allowed) - 1];

			if ($elem['type'] === 'open' || $elem['type'] === 'complete') {
				if (isset($current[$elem['tag']])) {
					// Always validate attributes but update allowed if open
					if ($this->validAttr($specElem, $invalid) && $elem['type'] === 'open') {
						if ($current[$elem['tag']]->children === true) {
							$allowed[] = &$allowed[0];
						} else {
							$allowed[] = &$current[$elem['tag']]->children;
						}
					}
				} else {
					$this->setOrInc($invalid['elem'], $elem['tag']);
				}
			}

			if ($elem['type'] === 'close') {
				array_pop($allowed);
			}
		}

		return $invalid;
	}

	/** Quick helper function to set or increment an array value */
	function setOrInc(&$invalid, $name)
	{
		if (isset($invalid[$name])) {
			++$invalid[$name];
		} else {
			$invalid[$name] = 1;
		}
	}

	/** Validate element attributes */
	function validAttr($specElem, &$invalid)
	{
		// All seems valid
		if (!isset($specElem['attributes'])) {
			return true;
		}

		// Check attributes as well
		$okay = true;
		foreach ($specElem['attributes'] as $attrName => $attrvalue) {
			if (!in_array($attrName, $this->accept[$name])) {
				$okay = false;
				if (isset($invalid['attr'][$name])) {
					$this->setOrInc($invalid['attr'][$name], $attrName);
				} else {
					$invalid['attr'][$name] = array($attrName => 1);
				}
			}
		}

		return $okay;
	}
};


/**
 * Produces and validates XML documents
 * @author Michael J.A. Clark <mjac@mjac.co.uk>
 *
 * Designed to be extended to enable support for different DTDs such as 
 * XHTML 1.0 Strict.
 */
class validXMLElem
{
	/** Name of this element */
	var $name;
	
	/**
	 * Allowed child elements
	 *
	 *  - array of references (element names)
	 *  - true allows all elements 
	 */	
	var $children = array();
	
	/** Allowed attributes */
	var $attr = array();

	/** All elements must have a name */
	function validXMLElem($name)
	{
		$this->name = $name;
	}
};

?>
