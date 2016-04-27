<?php defined('C5_EXECUTE') or die("Access Denied.");

/**
 * RadioButton .
 */

	$form = Core::make('helper/form');;
	$options = array();
	foreach($controller->getOptions() as $option) {
		$options[$option->getRadioButtonAttributeOptionID()] = $option->getRadioButtonAttributeOptionDisplayValue();
        echo $form->radio($view->field('atRadioButtonOptionValue'), $option->getRadioButtonAttributeOptionID(), $selectedRadioButtonOptions[0]);
        echo ' <label>' . $option->getRadioButtonAttributeOptionDisplayValue() . '</label> &nbsp;&nbsp;&nbsp;&nbsp;';
	}
