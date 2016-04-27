<?php
namespace Concrete\Package\AttributeRadioButton\Attribute\RadioButton;
use Gettext\Translations;
use Loader;
use Core;
use \Concrete\Core\Foundation\Object;

class Option extends Object {

	public function __construct($ID, $value, $displayOrder, $usageCount = false, $akID = null) {
		$this->ID = $ID;
		$this->value = $value;
		$this->th = Core::make('helper/text');
		$this->displayOrder = $displayOrder;	
		$this->usageCount = $usageCount;
		$this->akID = $akID;
	}

	public function getAttributeKeyID()
	{
		return $this->akID;
	}
	
	public function getRadioButtonAttributeOptionID() {return $this->ID;}
	public function getRadioButtonAttributeOptionUsageCount() {return $this->usageCount;}
	public function getRadioButtonAttributeOptionValue($sanitize = true) {
		if (!$sanitize) {
			return $this->value;
		} else {
			return $this->th->specialchars($this->value);
		}
	}
	/** Returns the display name for this RadioButton option value (localized and escaped accordingly to $format)
	* @param string $format = 'html'
	*	Escape the result in html format (if $format is 'html').
	*	If $format is 'text' or any other value, the display name won't be escaped.
	* @return string
	*/
	public function getRadioButtonAttributeOptionDisplayValue($format = 'html') {
		$value = tc('RadioButtonAttributeValue', $this->getRadioButtonAttributeOptionValue(false));
		switch($format) {
			case 'html':
				return h($value);
			case 'text':
			default:
				return $value;
		}
	}
	public function getRadioButtonAttributeOptionDisplayOrder() {return $this->displayOrder;}
	public function getRadioButtonAttributeOptionTemporaryID() {return $this->tempID;}
	
	public function __toString() {return $this->value;}
	
	public static function add($ak, $option) {
		$db = Loader::db();
		$th = Core::make('helper/text');
		// this works because displayorder starts at zero. So if there are three items, for example, the display order of the NEXT item will be 3.
		$displayOrder = $db->GetOne('select count(ID) from atRadioButtonOptions where akID = ?', array($ak->getAttributeKeyID()));

		$v = array($ak->getAttributeKeyID(), $displayOrder, $th->sanitize($option));

		$db->Execute('insert into atRadioButtonOptions (akID, displayOrder, value) values (?, ?, ?)', $v);
		
		return Option::getByID($db->Insert_ID());
	}
	
	public function setDisplayOrder($num) {
		$db = Loader::db();
		$db->Execute('update atRadioButtonOptions set displayOrder = ? where ID = ?', array($num, $this->ID));
	}
	
	public static function getByID($id) {
		$db = Loader::db();
		$row = $db->GetRow("select ID, displayOrder, value, akID from atRadioButtonOptions where ID = ?", array($id));
		if (isset($row['ID'])) {
			$obj = new Option($row['ID'], $row['value'], $row['displayOrder'], null, $row['akID']);
			return $obj;
		}
	}
	
	public static function getByValue($value, $ak = false) {
		$db = Loader::db();
		if (is_object($ak)) {
			$row = $db->GetRow("select ID, displayOrder, akID, value from atRadioButtonOptions where value = ? and akID = ?", array($value, $ak->getAttributeKeyID()));
		} else {
			$row = $db->GetRow("select ID, displayOrder, akID, value from atRadioButtonOptions where value = ?", array($value));
		}
		if (isset($row['ID'])) {
			$obj = new Option($row['ID'], $row['value'], $row['displayOrder'], null, $row['akID']);
			return $obj;
		}
	}
	
	public function delete() {
		$db = Loader::db();
		$db->Execute('delete from atRadioButtonOptions where ID = ?', array($this->ID));
		$db->Execute('delete from atRadioButtonOptionsSelected where atRadioButtonOptionID = ?', array($this->ID));
	}
	
	public function saveOrCreate($ak) {
		if ($this->tempID != false || $this->ID==0) {
			return Option::add($ak, $this->value);
		} else {
			$db = Loader::db();
			$th = Core::make('helper/text');
			$db->Execute('update atRadioButtonOptions set value = ? where ID = ?', array($th->sanitize($this->value), $this->ID));
			return Option::getByID($this->ID);
		}
	}

    public static function exportTranslations()
    {
        $translations = new Translations();
        $db = \Database::get();
        $r = $db->Execute('select ID from atRadioButtonOptions order by ID asc');
        while ($row = $r->FetchRow()) {
            $opt = static::getByID($row['ID']);
            $translations->insert('RadioButtonAttributeValue', $opt->getRadioButtonAttributeOptionValue());
        }
        return $translations;
    }

}