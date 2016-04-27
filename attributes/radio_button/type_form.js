var ccmAttributesHelper={   
	valuesBoxDisabled:function(typeSelect){
		var attrValsInterface=document.getElementById('attributeValuesInterface')
		var requiredVals=document.getElementById('reqValues');
		var offMsg=document.getElementById('attributeValuesOffMsg');
		if (typeSelect.value == 'SELECT' || typeSelect.value == 'SELECT_MULTIPLE') {
			attrValsInterface.style.display='block';
			requiredVals.style.display='inline'; 
			offMsg.style.display='none';
		} else {  
			requiredVals.style.display='none'; 
			attrValsInterface.style.display='none';
			offMsg.style.display='block';
		}	
	},  
	
	deleteValue:function(val){
		if(confirm(ccmi18n.deleteAttributeValue)) {
			$('#akRadioButtonValueWrap_'+val).remove();
		}
	},
	
	editValue:function(val){ 
		if($('#akRadioButtonValueDisplay_'+val).css('display')!='none'){
			$('#akRadioButtonValueDisplay_'+val).css('display','none');
			$('#akRadioButtonValueEdit_'+val).css('display','block').find('input[type="text"]').focus();
		}else{
			$('#akRadioButtonValueDisplay_'+val).css('display','block');
			$('#akRadioButtonValueEdit_'+val).css('display','none');
			var txtValue =  $('#akRadioButtonValueStatic_'+val).html();
			$('#akRadioButtonValueField_'+val).val( $('<div/>').html(txtValue).text());
		}
	},
	
	changeValue:function(val){ 
		var txtValue = $('<div/>').text($('#akRadioButtonValueField_'+val).val()).html();
		$('#akRadioButtonValueStatic_'+val).html( txtValue );
		this.editValue(val)
	},
	
	makeSortable: function() {
		$("div#attributeValuesWrap").sortable({
			cursor: 'move',
			opacity: 0.5
		});
	},
	
	saveNewOption:function(){
		var newValF=$('#akRadioButtonValueFieldNew');
		var val = $('<div/>').text(newValF.val()).html();
		if(val=='') {
			return;
		}
		var ts = 't' + new Date().getTime();
		var template=document.getElementById('akRadioButtonValueWrapTemplate');
		var newRowEl=document.createElement('div');
		newRowEl.innerHTML=template.innerHTML.replace(/template_clean/ig,ts).replace(/template/ig,val);
		newRowEl.id="akRadioButtonValueWrap_"+ts;
		newRowEl.className='akRadioButtonValueWrap akRadioButtonValueWrapSortable';
		$('#attributeValuesWrap').append(newRowEl);		
		newValF.val(''); 
	},
	
	clrInitTxt:function(field,initText,removeClass,blurred){
		if(blurred && field.value==''){
			field.value=initText;
			$(field).addClass(removeClass);
			return;	
		}
		if(field.value==initText) field.value='';
		if($(field).hasClass(removeClass)) $(field).removeClass(removeClass);
	},
	
	keydownHandler:function(event){
		var form = $("#ccm-attribute-key-form");
		switch (event.keyCode) {
			case 13: // enter
				event.preventDefault();
				if (event.currentTarget.id === 'akRadioButtonValueFieldNew') { // if the event originates from the "add" input field, create the option
					ccmAttributesHelper.saveNewOption();
				} else { // otherwise just fire the existing option save
					ccmAttributesHelper.changeValue(event.currentTarget.getAttribute('data-radio-value-id'));
				}
				break;
			case 38: // arrow up
			case 40: // arrow down
				ccmAttributesHelper.changeValue(event.currentTarget.getAttribute('data-radio-value-id'));
				var find = (event.keyCode === 38) ? 'prev' : 'next';
				var $target = $(event.currentTarget).closest('.akRadioButtonValueWrap')[find]();
				if ($target.length) {
					$target.find('.leftCol').click();
				} else if (find === 'next') {
					$('#akRadioButtonValueFieldNew').focus();
				}
				break;
		}
	},

	// legacy stub method
	addEnterClick:function(){
		ccmAttributesHelper.keydownHandler.apply(this, arguments);
	}

}