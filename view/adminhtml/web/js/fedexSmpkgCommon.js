    /**
     * 
     * @return {Boolean}
     */
    function checkFedexConnectionFields(){
        addfedexSmpkgTestConnTitle();
        var connIDs = [
            'carriers_fedexConnectionSettings_AccountNumber',
            'carriers_fedexConnectionSettings_ProdutionPassword',
            'carriers_fedexConnectionSettings_MeterNumber',
            'carriers_fedexConnectionSettings_AuthenticationKey',
            'carriers_fedexConnectionSettings_licnsKey'
        ];
        
        var validationCheck = fedexSmpkgFieldsValidation('#carriers_fedexConnectionSettings');
        if(!validationCheck){
            connIDs.each(function(id) {
                jQuery('#'+id).removeClass('mage-error');
                jQuery('#'+id+'-error').remove();
            });
        }
        return true;
    }
    
    /**
     * Document load function
     * @type type
     */
    
    require([ 'jquery', 'jquery/ui'], function($){ 
        $(document).ready(function($) {
            $('#carriers_fedexConnectionSettings-head').after('<div class="conn-setting-note">Note! You must have a FedEx account to use this application. If you do not have one, contact FedEx at 800-463-3339 or <a target="_blank" href="https://www.fedex.com/en-us/create-account.html">register online</a>.</div>');
            
            $('#fedexQuoteSetting_third span, #carriers_fedexConnectionSettings span').attr('data-config-scope', '');
            
            $('.close').click(function(){ 
                $('.fedexSmpkg_warehouse_overlay').hide(); 
            });
            $('.add_dropship_btn, .add_warehouse_btn').click(function(){ 
                $('.fedexSmpkg_warehouse_overlay').show(); 
            });
            
            $('#fedexQuoteSetting_third_hndlngFee').attr('title', 'Handling Fee / Markup');
            
            $('#save').on('click', function(){
                setTimeout(function() {
                    checkFedexConnectionFields();
                }, 10);
            });

            $( '.hide_val' ).click( function () {
                fedexSmpkgEmptyFieldsAndErr('#fedexSmpkgWarehouseForm');
            });

            $( '.hide_drop_val' ).click( function () {
                fedexSmpkgEmptyFieldsAndErr('#fedexSmpkgDropshipForm');
            });
            
            jQuery('.close-ds').click(function(){           
                jQuery('html, body').animate({
                    'scrollTop' : jQuery('.warehouse_text').offset().top
                });
            });
            
            // Set focus on first input field
            jQuery('.add_warehouse_btn').click(function(){
                setTimeout(function(){
                  if(jQuery('.add_warehouse_popup').is(':visible')){
                    jQuery('.add_warehouse_input > input').eq(0).focus();
                  }
                },500);
            });
            jQuery('.add_dropship_btn').click(function(){
                setTimeout(function(){
                  if(jQuery('.ds-popup').is(':visible')){
                    jQuery('.fedexSmpkgDropshipForm > input').eq(0).focus();
                  }
                },500);
            });
            
        });
    });
    
    
    /**
     * Set empty values to warehouse and dropship fields and remove error class
     * @param {string} form_id
     */
    function fedexSmpkgEmptyFieldsAndErr(form_id){
        jQuery(form_id + " input[type='text']").each(function () {
            jQuery(this).val('');
            jQuery( '.err' ).remove();
        });
        jQuery('.local-delivery-fee-err').remove();
        jQuery(form_id).find("input[type='checkbox']").prop('checked',false);
        jQuery('#instore-pickup-zipmatch .tag-i, #local-delivery-zipmatch .tag-i').trigger('click');
        jQuery( '.city_select' ).hide();
        jQuery( '.city_input' ).show();
        jQuery( '#edit_form_id' ).val('');
        jQuery( '#edit_dropship_form_id' ).val('');
    }

    /**
     * Varify connection credentials
     * @returns {Boolean}
     */
    function fedexSmpkgFieldsValidation(formId){
        var input = fedexSmpkgValidateInput(formId);
        if (input === false) {
            return false;
        }
        return true;
    }

    /**
     * Validate input
     * @param {type} form_id
     * @returns {Boolean}
     */
    function fedexSmpkgValidateInput(form_id)
    {
        var has_err = true;

        jQuery(form_id + " input[type='text']").each(function () {
            var input = jQuery(this).val();

            var response = fedexSmpkgValidateString(input);            
                jQuery( '.validation-advice' ).remove() ;
            if( jQuery( this ).parent().find( '.err' ).length < 1 ){
                jQuery( this ).parent().append( '<span class="err"></span>' );
            }

            var errorElement = jQuery(this).parent().find('.err');
            jQuery(errorElement).html('');
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');
            optional = (optional === undefined) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';
            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(errorElement).html(errorText);
            }
            has_err = (response != true && optional == 0) ? false : has_err;
        });

        return has_err;
    }
    
    /**
     * Validate Input String 
     * @param {type} string
     * @returns {String|Boolean}
     */

    function fedexSmpkgValidateString(string)
    {
        if (string == '') {
            return 'empty';
        } else {
            return true;
        }
    }



        
    /**
     * check int value
     * @param {type} value
     * @returns {Boolean}
     */
    function inIntValue(value){
        var insInt = false;
        if(value == parseInt(value)){
            if(parseInt(value) > 0)
                insInt = true;
        }
        return insInt;
    }
        
    /**
     * check float value
     * @param {type} value
     * @returns {Boolean}
     */
    function isFloatValue(value){
        var inFloat = false;
        if(value == parseFloat(value)){
            if(parseFloat(value) > 0)
                inFloat = true;
        }
        return inFloat;
    }
        
    /**
     * call for warehouse ajax requests
     * @param {type} parameters
     * @param {type} ajaxUrl
     * @param {type} responseFunction
     * @returns {ajax response}
     */
    function ajaxRequest(parameters, ajaxUrl, responseFunction){
        new Ajax.Request(ajaxUrl, {
            method:  'POST',
            parameters: parameters,
            onSuccess: function(response){
                var json = response.responseText;
                var data = JSON.parse(json);
                var callbackRes = responseFunction(data);
                return callbackRes;

            }
        });
    }