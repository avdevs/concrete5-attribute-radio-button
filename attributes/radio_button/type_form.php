<?php

function getAttributeOptionHTML($v){
	if ($v == 'TEMPLATE') {
		$akRadioButtonValueID = 'TEMPLATE_CLEAN';
		$akRadioButtonValue = 'TEMPLATE';
	} else {
		if ($v->getRadioButtonAttributeOptionTemporaryID() != false) {
			$akRadioButtonValueID = $v->getRadioButtonAttributeOptionTemporaryID();
		} else {
			$akRadioButtonValueID = $v->getRadioButtonAttributeOptionID();
		}
		$akRadioButtonValue = $v->getRadioButtonAttributeOptionValue();
	}
		?>
		<div id="akRadioButtonValueDisplay_<?php echo $akRadioButtonValueID?>" >
			<div class="rightCol">
				<input class="btn btn-primary" type="button" onClick="ccmAttributesHelper.editValue('<?php echo addslashes($akRadioButtonValueID)?>')" value="<?php echo t('Edit')?>" />
				<input class="btn btn-danger" type="button" onClick="ccmAttributesHelper.deleteValue('<?php echo addslashes($akRadioButtonValueID)?>')" value="<?php echo t('Delete')?>" />
			</div>			
			<span onClick="ccmAttributesHelper.editValue('<?php echo addslashes($akRadioButtonValueID)?>')" id="akRadioButtonValueStatic_<?php echo $akRadioButtonValueID?>" class="leftCol"><?php echo $akRadioButtonValue ?></span>
		</div>
		<div id="akRadioButtonValueEdit_<?php echo $akRadioButtonValueID?>" style="display:none">
			<span class="leftCol">
				<input name="akRadioButtonValueOriginal_<?php echo $akRadioButtonValueID?>" type="hidden" value="<?php echo $akRadioButtonValue?>" />
				<?php if (is_object($v) && $v->getRadioButtonAttributeOptionTemporaryID() == false) { ?>
					<input id="akRadioButtonValueExistingOption_<?php echo $akRadioButtonValueID?>" name="akRadioButtonValueExistingOption_<?php echo $akRadioButtonValueID?>" type="hidden" value="<?php echo $akRadioButtonValueID?>" />
				<?php } else { ?>
					<input id="akRadioButtonValueNewOption_<?php echo $akRadioButtonValueID?>" name="akRadioButtonValueNewOption_<?php echo $akRadioButtonValueID?>" type="hidden" value="<?php echo $akRadioButtonValueID?>" />
				<?php } ?>
				<input id="akRadioButtonValueField_<?php echo $akRadioButtonValueID?>" onkeypress="ccmAttributesHelper.keydownHandler(event);" class="akRadioButtonValueField form-control" data-radio-value-id="<?php echo $akRadioButtonValueID; ?>" name="akRadioButtonValue_<?php echo $akRadioButtonValueID?>" type="text" value="<?php echo $akRadioButtonValue?>" size="40" />
			</span>		
			<div class="rightCol">
				<input class="btn btn-default" type="button" onClick="ccmAttributesHelper.editValue('<?php echo addslashes($akRadioButtonValueID)?>')" value="<?php echo t('Cancel')?>" />
				<input class="btn btn-success" type="button" onClick="ccmAttributesHelper.changeValue('<?php echo addslashes($akRadioButtonValueID)?>')" value="<?php echo t('Save')?>" />
			</div>		
		</div>	
		<div class="ccm-spacer">&nbsp;</div>
<?php } ?>

<fieldset class="ccm-attribute ccm-attribute-radio">
<legend><?php echo t('Radio Button Options')?></legend>


<div class="form-group">
<label for="akRadioButtonOptionDisplayOrder"><?php echo t("Option Order")?></label>
	<?php
	$displayOrderOptions = array(
		'display_asc' => t('Display Order'),
		'alpha_asc' => t('Alphabetical'),
		'popularity_desc' => t('Most Popular First')
	);
	?>

	<?php echo $form->select('akRadioButtonOptionDisplayOrder', $displayOrderOptions, $akRadioButtonOptionDisplayOrder)?>
</div>

<div class="clearfix">
<label><?php echo t('Values')?></label>
<div class="input">
	<div id="attributeValuesInterface">
	<div id="attributeValuesWrap">
	<?php
	Core::make('helper/text');
    foreach($akRadioButtonValues as $v) {
		if ($v->getRadioButtonAttributeOptionTemporaryID() != false) {
			$akRadioButtonValueID = $v->getRadioButtonAttributeOptionTemporaryID();
		} else {
			$akRadioButtonValueID = $v->getRadioButtonAttributeOptionID();
		}
		?>
		<div id="akRadioButtonValueWrap_<?php echo $akRadioButtonValueID?>" class="akRadioButtonValueWrap akRadioButtonValueWrapSortable">
			<?php echo getAttributeOptionHTML( $v )?>
		</div>
	<?php } ?>
	</div>
	
	<div id="akRadioButtonValueWrapTemplate" class="akRadioButtonValueWrap" style="display:none">
		<?php echo getAttributeOptionHTML('TEMPLATE') ?>
	</div>
	
	<div id="addAttributeValueWrap" class="form-inline">
		<input id="akRadioButtonValueFieldNew" name="akRadioButtonValueNew" type="text" value="<?php echo $defaultNewOptionNm ?>" size="40"  class="form-control"
		onfocus="ccmAttributesHelper.clrInitTxt(this,'<?php echo $defaultNewOptionNm ?>','faint',0)" 
		onblur="ccmAttributesHelper.clrInitTxt(this,'<?php echo $defaultNewOptionNm ?>','faint',1)"
		onkeypress="ccmAttributesHelper.keydownHandler(event);"
		 /> 
		<input class="btn btn-primary" type="button" onClick="ccmAttributesHelper.saveNewOption(); $('#ccm-attribute-key-form').unbind()" value="<?php echo t('Add') ?>" />
	</div>
	</div>

</div>
</div>


</fieldset>
<script type="text/javascript">
//<![CDATA[
$(function() {
	ccmAttributesHelper.makeSortable();
});
//]]>
</script>
