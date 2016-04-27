<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<?php
$options = $this->controller->getOptions();
if ($akRadioButtonAllowMultipleValues) { ?>

	<?php foreach($options as $opt) { ?>
		<div class="checkbox"><label><input type="checkbox" name="<?php echo $this->field('atRadioButtonOptionID')?>[]" value="<?php echo $opt->getRadioButtonAttributeOptionID()?>" <?php if (in_array($opt->getRadioButtonAttributeOptionID(), $selectedRadioButtonOptions)) { ?> checked <?php } ?> /><?php echo $opt->getRadioButtonAttributeOptionDisplayValue()?></label></div>
	<?php } ?>

<?php } else { ?>
	<select class="form-control" name="<?php echo $this->field('atRadioButtonOptionID')?>[]">
		<option value=""><?php echo t('** All')?></option>
	<?php foreach($options as $opt) { ?>
		<option value="<?php echo $opt->getRadioButtonAttributeOptionID()?>" <?php if (in_array($opt->getRadioButtonAttributeOptionID(), $selectedRadioButtonOptions)) { ?> selected <?php } ?>><?php echo $opt->getRadioButtonAttributeOptionDisplayValue()?></option>
	<?php } ?>
	</select>

<?php }