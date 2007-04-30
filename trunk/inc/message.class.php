<?php
defined('PATH_INC') || exit;

if (!class_exists('validMessageXML')) {
	require(PATH_INC . '/validMessageXML.class.php');
}

/** Forum */
class forum
{
	/** Database table */
	var $table = '';

	/** Set the active table */
	function forum($table)
	{
		$this->table = $table;
	}

	/** Retrieve the current forum entries */
	function view($from = false, $to = false)
	{
	
	}

	/**
	 * Add a message to the forum
	 *
	 * The only required attribute is message.
	 */
	function add($message, $fromId = false, $fromName = false, $toId = false, $toName = false)
	{
		$problems = array();

		$new = new message;

		if ($new->contentText($message) === false) {
			$problems[] = 'content';
		}
	}

	/** Remove a message by id or all */
	function remove($id = false)
	{
		global $db;

		$delete = $db->query('DELETE FROM ' . $this->table .
		 ($id === false ? '' : ' WHERE message_id = %[1]'), (int)$id);

		return $db->affectedRows($delete);
	}
};


/** Message */
class message
{
	/** Valid message XHTML */
	var $message;

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

		return $xhtml;
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
