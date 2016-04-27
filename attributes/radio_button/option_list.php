<?php
namespace Concrete\Package\AttributeRadioButton\Attribute\RadioButton;
use Loader;
use \Concrete\Core\Foundation\Object;

class OptionList extends Object implements \Iterator {

	private $options = array();
	
	public function add(Option $opt) {
		$this->options[] = $opt;
	}
	
	public function rewind() {
		reset($this->options);
	}
	
	public function current() {
		return current($this->options);
	}
	
	public function key() {
		return key($this->options);
	}
	
	public function next() {
		next($this->options);
	}
	
	public function valid() {
		return $this->current() !== false;
	}
	
	public function count() {return count($this->options);}
	
	public function contains(Option $opt) {
		foreach($this->options as $o) {
			if ($o->getRadioButtonAttributeOptionID() == $opt->getRadioButtonAttributeOptionID()) {
				return true;
			}
		}
		
		return false;
	}
	
	public function get($index) {
		return $this->options[$index];
	}
	
	public function getOptions() {
		return $this->options;
	}

	/** Sort the options by their display value. */
	public function sortByDisplayName() {
		usort($this->options, array(__CLASS__, 'displayValueSorter'));
	}
	/**
	* @param RadioButtonAttributeTypeOption $a
	* @param RadioButtonAttributeTypeOption $b
	* @return int
	*/
	protected static function displayValueSorter($a, $b) {
		return strcasecmp($a->getRadioButtonAttributeOptionDisplayValue('text'), $b->getRadioButtonAttributeOptionDisplayValue('text'));
	}

	public function __toString() {
		$str = '';
		$i = 0;
		foreach($this->options as $opt) {
			$str .= $opt->getRadioButtonAttributeOptionValue();
			$i++;
			if ($i < count($this->options)) {
				$str .= "\n";
			}
		}
		return $str;
	}


}
