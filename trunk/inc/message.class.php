<?php

/** Extend validXML to include some simple elements from XHTML 1.0 Strict */
class validMessageXML extends validXML
{
	/** Inject some elements */
	function validMessageXML()
	{
		// Actual element categories
		$elem['inline'] = array('em', 'strong', 'dfn', 'code', 'q', 'samp',
		 'kbd', 'var', 'cite', 'abbr', 'acronym', 'sub', 'sup', 'a', 'img',
		 'br');
		$elem['block'] = array('dl', 'ul', 'ol', 'blockquote', 'p');
		$elem['flow'] = array_merge($elem['inline'], $elem['block']);

		// Types of contents
		$cont = array();
		$cont['inline'] = array('p', 'dt', 'em', 'strong', 'dfn', 'code', 'q',
		 'samp', 'kbd', 'var', 'cite', 'abbr', 'acronym', 'sub', 'sup', 'a');
		$cont['block'] = array('blockquote');
		$cont['flow'] = array('li', 'dd', 'dt');

		// Allowed attributes
		$attrs = array(
			'blockquote' => array('cite'),
			'q' => array('cite'),
			'a' => array('href', 'title'),
			'dfn' => array('title'),
			'acronym' => array('title'),
			'abbr' => array('title')
		);

		// Create classes for all elements (flow includes all)
		$this->accept = array();
		foreach ($elem['flow'] as $eName) {
			$this->elemWithAttr($eName, $attrs);
		}

		// Can only contain a few items so treated uniquely
		$ul =& $this->accept['ul'];
		$ol =& $this->accept['ol'];
		$dl =& $this->accept['dl'];

		// Div allows inline, block, flow and ul, ol and dl but not li, dt or dd
		$div =& $this->elemWithAttr('div', $attrs);
		$div->children = $this->accept;

		// List entries
		$li =& $this->elemWithAttr('li', $attrs);
		$dt =& $this->elemWithAttr('dt', $attrs);
		$dd =& $this->elemWithAttr('dd', $attrs);

		// Reference to classes
		$class = array();
		foreach ($elem as $type => $names) {
			$class[$type] = array();
			foreach ($names as $name) {
				if (isset($this->accept[$name])) {
					$class[$type][$name] =& $this->accept[$name];
				}
			}
		}

		// Add all the classes
		foreach ($this->accept as $name => $eClass) {
			foreach ($cont as $type => $cElem) {
				if (in_array($name, $cElem) || $eClass->children === true) {
					$this->accept[$name]->children =
					 array_merge($eClass->children, $class[$type]);
				}
			}
		}

		$ol->children = array('li' => &$li);
		$ul->children = array('li' => &$li);
		$dl->children = array('dt' => &$dt, 'dd' => &$dd);
	}

	/** Quick interface to creating a new element */
	function elemWithAttr($eName, &$attrs)
	{
		$this->accept[$eName] =& new validXMLElem($eName);
		// Assign attributes if they exist
		if (isset($attrs[$eName])) {
			$new->attrs = $attrs[$eName];
		}
		return $this->accept[$eName];
	}
}



class forum
{
	var $table = '';
	
	function forum($table)
	{
		$this->table = $table;
	}

	function view()
	{
	
	}
	
	function add()
	{
	
	}
	
	function remove()
	{
	
	}
};

class message
{
	/** SDA database */
	var $db;

	var $id = false;
	var $name;

	/** Valid message XHTML */
	var $message;

	function sender($name, $id = false)
	{
		$this->name = $name;
		$this->id = $id;
	}

	/** Plain text to XHTML and message */
	function contentText($text)
	{
		return $this->contentXHTML($this->textToXHTML($text));
	}

	/** Validate and assign XHTML as the message content */
	function contentXHTML($xhtml)
	{
		if (!$this->validateXHTML("<div>$xhtml</div>")) {
			return false;
		}

		$this->message = $xhtml;

		return true;
	}

	/** Uses validMessageXML to validate messagee XHTML */
	function validateXHTML($xhtml)
	{
		$validation = new validMessageXML;
		return $validation->validate($xhtml);
	}

	/** Convert a simple text document to XHTML */
	function textToXHTML($text)
	{
		$xhtml = $this->initialStrip($text);
		$xhtml = preg_replace('/\n{2,}/', "</p>\n<p>", $xhtml);
		$xhtml = str_replace("\n", "<br />\n", $xhtml);

		return "<p>$xhtml</p>";
	}

	/** Fix windows line breaks */
	function initialStrip($text)
	{
		return str_replace("\r", "\n", str_replace("\n\r", "\n",
		 str_replace("\r\n", "\n", trim($text))));
	}
};

?>
