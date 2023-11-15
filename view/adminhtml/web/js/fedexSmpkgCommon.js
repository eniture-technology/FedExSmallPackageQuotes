    /**
     *
     * @return {Boolean}
     */
    function checkFedexConnectionFields(){
        addfedexSmpkgTestConnTitle();
        var connIDs = [
            'fedexconnsettings_first_AccountNumber',
            'fedexconnsettings_first_ProdutionPassword',
            'fedexconnsettings_first_MeterNumber',
            'fedexconnsettings_first_AuthenticationKey',
            'fedexconnsettings_first_licnsKey'
        ];

        var validationCheck = fedexSmpkgFieldsValidation('#fedexconnsettings_first');
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

    require(['jquery'], function($){
        $(document).ready(function($) {

            $('.numberonly').bind('keyup keydown',function(event) {
                var node = $(this);
                node.val(node.val().replace(/[^0-9]/,'') );
            });

            $('.alphanumonly').bind('keyup keydown',function(event) {
                validateAlphaNumOnly($, this);
            });

            $('.bootstrap-tagsinput input').bind('keyup keydown',function(event) {
                validateAlphaNumOnly($, this);
            });

            $('.decimalonly').bind('keyup keydown', function(e){
                var input = $(this);
                var oldVal = input.val();
                var pattern=/^\d*(\.\d{0,2})?$/;
                var regex = new RegExp(pattern, 'g');

                setTimeout(function(){
                    var newVal = input.val();
                    if(!regex.test(newVal)){
                        input.val(oldVal);
                    }
                }, 4);
            });

            connSettingsNote($);
            $('#fedexQuoteSetting_third span, #fedexconnsettings_first span').attr('data-config-scope', '');

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

            $('.close-ds').click(function(){
                $('html, body').animate({
                    'scrollTop' : $('.warehouse_text').offset().top
                });
            });

            // Set focus on first input field
            $('.add_warehouse_btn').click(function(){
                setTimeout(function(){
                  if($('.add_warehouse_popup').is(':visible')){
                    $('.add_warehouse_input > input').eq(0).focus();
                  }
                },500);
            });
            $('.add_dropship_btn').click(function(){
                setTimeout(function(){
                  if($('.ds-popup').is(':visible')){
                      $('#fedexSmpkg_dropship_nickname').focus();
                  }
                },500);
            });

        });
    });


    function validateAlphaNumOnly($, element){
        var value = $(element);
        value.val(value.val().replace(/[^a-z0-9]/g,''));
    }

    /**
     * Display connection setting fedex account note
     */
    function connSettingsNote($) {
        var divafter = '<div class="conn-setting-note">Note! You must have a Fedex account to use this application. If you do not have one, contact FedEx at 800-463-3339 or <a target="_blank" href="https://www.fedex.com/en-us/create-account.html">register online</a>.</div>';
        var carrierdiv = '#fedexconnsettings_first-head';
        notesToggleHandling($, divafter, '.conn-setting-note', carrierdiv);
    }

    function currentPlanNote($, planMsg, carrierdiv){
        var divafter = '<div class="plan-note">'+planMsg+'</div>';
        notesToggleHandling($, divafter, '.plan-note', carrierdiv);
    }

    function notesToggleHandling($, divafter, className, carrierdiv){

        if($(carrierdiv).attr('class') === 'open'){
            $(carrierdiv).after(divafter);
        }
        $(carrierdiv).click(function(){
            if($(carrierdiv).attr('class') === 'open'){
                $(carrierdiv).after(divafter);
            }else if($(className).length){
                $(className).remove();
            }
        });
    }

    /**
     * Set empty values to warehouse and dropship fields and remove error class
     * @param {string} form_id
     */
    function fedexSmpkgEmptyFieldsAndErr(form_id){
        jQuery(form_id + " input[type='text']").each(function () {
            if(jQuery(this).attr('name') !== undefined) {
                jQuery(this).val('');
                jQuery('.err').text('');
            }
        });
        jQuery(jQuery(".bootstrap-tagsinput").find("span[data-role=remove]")).trigger("click");
        jQuery(form_id + " input[type='checkbox']").each(function () {
            jQuery(this).prop('checked', false);
        });
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

            if(!jQuery(this).parent().find('.err').length) {
                jQuery(this).parent().append('<span class="err"></span>');
            }
            var input = jQuery(this).val();

            var response = fedexSmpkgValidateString(input);

            //Exception for specific inputs within disableddiv which are already validated
            if(jQuery(this).parents('#disableddiv').length == 1) {
                response = true;
            }
            
            jQuery( '.validation-advice' ).remove() ;

            var errorElement = jQuery(this).parent().find('.err');
            jQuery(errorElement).html('');
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');
            optional = (optional === undefined) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';
            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(errorElement).html(errorText);
                jQuery('.FedEx_small_warehouseFormContent').animate({ scrollTop: 0 }, 'slow');
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

    /**
     * @param canAddWh
     */
    function addWarehouseRestriction(canAddWh){
        switch(canAddWh)
        {
            case 0:
                jQuery("#append_warehouse").find("tr").removeClass('inactiveLink');
                jQuery('.add_warehouse_btn').addClass('inactiveLink');
                if (jQuery( ".required-plan-msg" ).length == 0) {
                    jQuery('.add_warehouse_btn').after('<a href="https://eniture.com/magento2-fedex-small-package" target="_blank" class="required-plan-msg">Standard Plan required</a>');
                }
                jQuery("#append_warehouse").find("tr:gt(1)").addClass('inactiveLink');
                break;
            case 1:
                jQuery('.add_warehouse_btn').removeClass('inactiveLink');
                jQuery('.required-plan-msg').remove();
                jQuery("#append_warehouse").find("tr").removeClass('inactiveLink');
                break;
            default:
                break;
        }

    }

    /**
     * Restrict Quote Settings Fields
     * @param {array} qRestriction
     */
    function planQuoteRestriction(qRestriction){
        var quoteSecRowID = "#row_fedexQuoteSetting_third_";
        var quoteSecID = "#fedexQuoteSetting_third_";
        var parsedData = JSON.parse(qRestriction)
        if(parsedData['advance']){
            jQuery(''+quoteSecRowID+'transitDaysNumber').before('<tr><td><label><span data-config-scope=""></span></label></td><td class="value"><a href="https://eniture.com/magento2-fedex-small-package" target="_blank" class="required-plan-msg adv-plan-err">Advance Plan required</a></td><td class=""></td></tr>');
            disabledFieldsLoop(parsedData['advance'], quoteSecID);
        }

        if(parsedData['standard']){
            jQuery(''+quoteSecRowID+'onlyGndService').before('<tr><td><label><span data-config-scope=""></span></label></td><td class="value"><a href="https://eniture.com/magento2-fedex-small-package" target="_blank" class="required-plan-msg std-plan-err">Standard Plan required</a></td><td class=""></td></tr>');
            disabledFieldsLoop(parsedData['standard'], quoteSecID);
        }
    }

    function disabledFieldsLoop(dataArr, quoteSecID){
        jQuery.each(dataArr, function( index, value ) {
            jQuery(quoteSecID + value).attr('disabled','disabled');
        });
    }

    function loadInsidePikupAndLocalDeliveryData(data, formid){
        var instore = JSON.parse(data.in_store);
        var localdel= JSON.parse(data.local_delivery);
        //Filling form data
        if(instore != null && instore != 'null'){
            instore.enable_store_pickup == 1 ? jQuery(formid + 'enable_instore_pickup').prop('checked', true) : '';
            jQuery(formid + 'within_miles').val(instore.miles_store_pickup);
            jQuery(formid + 'postcode_match').tagsinput('add', instore.match_postal_store_pickup);
            jQuery(formid + 'checkout_descp').val(instore.checkout_desc_store_pickup);
            instore.suppress_other == 1 ? jQuery(formid + 'ld_sup_rates').prop('checked', true) : '';
        }

        if(localdel != null && localdel != 'null'){
            localdel.enable_local_delivery == 1 ? jQuery(formid + 'enable_local_delivery').prop('checked', true) : '';
            jQuery(formid + 'ld_within_miles').val(localdel.miles_local_delivery);
            jQuery(formid + 'ld_postcode_match').tagsinput('add', localdel.match_postal_local_delivery);
            jQuery(formid + 'ld_checkout_descp').val(localdel.checkout_desc_local_delivery);
            jQuery(formid + 'ld_fee').val(localdel.fee_local_delivery);
            localdel.suppress_other == 1 ? jQuery(formid + 'ld_sup_rates').prop('checked', true) : '';
        }
    }
