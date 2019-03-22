
window.addEventListener("load", function() { 
    jQuery( '.hide_val' ).click( function () {
        jQuery( '#edit_form_id' ).val( '' );
        jQuery( "#fedexSmpkg_warehouse_zip" ).val( '' );
        jQuery( '.city_select' ).hide();
        jQuery( '.city_input' ).show();
        jQuery( "#warehouse_origin_city" ).val( '' ); 
        jQuery( "#warehouse_origin_state" ).val( '' );
        jQuery( "#warehouse_origin_country" ).val( '' );
    });
    
});

    /**
    * Mangage script conflict
    * @type Object
    */
//    var $j = jQuery.noConflict();
    
    /**
     * Get address against zipcode from smart street api
     * @param {type} ajaxUrl
     * @returns {Boolean}
     */
    function fedexSmpkgGetAddressFromZip(ajaxUrl, $this, callfunction) {
        var zipCode         = $this.value; 
        var action          = jQuery($this).data('action'); 
        if (zipCode === '') {
            return false;
        }
        var parameters = {
            'action'      : action,
            'origin_zip'  : zipCode
        };

        ajaxRequest(parameters, ajaxUrl, callfunction);
    }
        
        
    function fedexSmpkgGetAddressResSettings(data){

        if( data.country === 'US' || data.country === 'CA'){
            
            if (data.postcode_localities === 1) {
                jQuery( '.city_select' ).show();
                jQuery( '#actname' ).replaceWith( data.city_option );
                jQuery( '.city-multiselect' ).replaceWith( data.city_option );
                jQuery( '.city-multiselect' ).change( function(){
                    var city = jQuery(this).val();
                    jQuery('#warehouse_origin_city').val(city);
                });
                jQuery( "#warehouse_origin_city" ).val( data.first_city );
                jQuery( "#warehouse_origin_state" ).val( data.state );
                jQuery( "#warehouse_origin_country" ).val( data.country ); 
                jQuery( '.city_input' ).hide();
             }else{
                jQuery( '.city_input' ).show();
                jQuery( '#_city' ).removeAttr('value');
                jQuery( '.city_select' ).hide();
                jQuery( "#warehouse_origin_city" ).val( data.city );
                jQuery( "#warehouse_origin_state" ).val( data.state );
                jQuery( "#warehouse_origin_country" ).val( data.country );
             }
             
        }else if( data.result === 'false' ){
            jQuery( '.not_allowed' ).show('slow');
            setTimeout(function () {
                jQuery('.not_allowed').hide('slow');
            }, 5000);
        }else if( data.error ){
                jQuery( '.api_error' ).show('slow');
                jQuery( '.api_error' ).html(data.error);
                setTimeout(function () {
                    jQuery('.api_error').hide('slow');
                }, 5000);
            }else{
            jQuery( '.not_allowed' ).show('slow');
            setTimeout(function () {
                jQuery('.not_allowed').hide('slow');
            }, 5000);
        }
        return true;
    }
    
    function en_wd_check_postal_length(html_id){
        return jQuery(html_id).text().length == 0 ? true : false;
    }
        
    /**
     * Save Warehouse Function
     * @param {type} ajaxUrl
     * @returns {Boolean}
     */
    function fedexSmpkgSaveWarehouseData(ajaxUrl){
        var fedexSmpkgWhFormID = '#fedexSmpkgWarehouseForm';
        var enable_instore_pickup = jQuery(fedexSmpkgWhFormID + " #enable-instore-pickup").is(':checked');
        var enable_local_delivery = jQuery(fedexSmpkgWhFormID + " #enable-local-delivery").is(':checked');

        jQuery('.local-delivery-fee-err').remove();
        switch(true){
            case (enable_instore_pickup && ( jQuery(fedexSmpkgWhFormID + " #instore-pickup-address").val().length == 0 && en_wd_check_postal_length(fedexSmpkgWhFormID + " #instore-pickup-zipmatch") )):
                jQuery('.instore-miles-postal-err').show('slow');
                document.querySelector(fedexSmpkgWhFormID + " .instore-pickup-heading").scrollIntoView({ behavior: 'smooth' });
                setTimeout(function(){ jQuery('.instore-miles-postal-err').hide('slow'); }, 5000);
                return false;
                    
            case (enable_local_delivery && ( jQuery(fedexSmpkgWhFormID + " #local-delivery-address").val().length == 0 && en_wd_check_postal_length(fedexSmpkgWhFormID + " #local-delivery-zipmatch") )):
                jQuery('.local-miles-postals-err').show('slow');
                document.querySelector(fedexSmpkgWhFormID + " .local-miles-postals-err").scrollIntoView({ behavior: 'smooth' });
                setTimeout(function(){ jQuery('.local-miles-postals-err').hide('slow'); }, 5000);
                return false;
                    
            case (enable_local_delivery && jQuery(fedexSmpkgWhFormID + " #local-delivery-fee").val().length <= 0):
                jQuery(fedexSmpkgWhFormID + " #local-delivery-fee").after('<span class="local-delivery-fee-err">Local delivery fee is required.</span>');
                return false;
            }

        var validationCheck = fedexSmpkgFieldsValidation(fedexSmpkgWhFormID);
        var fedexSmpkgInstorePickupFileds = typeof instorePickupInputVal !== 'undefined' && jQuery.isFunction(instorePickupInputVal)?instorePickupInputVal(fedexSmpkgWhFormID):'';
        var fedexSmpkgLocalDeliveryFileds = typeof localDeliveryInputVal !== 'undefined' && jQuery.isFunction(localDeliveryInputVal)?localDeliveryInputVal(fedexSmpkgWhFormID):'';
         
        if(validationCheck == true){
            var parameters = {
                    'action'         : 'saveWarehouse',
                    'originId'       : jQuery('#edit_form_id').val(),
                    'city'           : jQuery('#warehouse_origin_city').val(),
                    'state'          : jQuery('#warehouse_origin_state').val(),
                    'zip'            : jQuery('#fedexSmpkg_warehouse_zip').val(),
                    'country'        : jQuery('#warehouse_origin_country').val(),
                    'location'       : 'warehouse'
                };

            var arrObj = [parameters, fedexSmpkgInstorePickupFileds, fedexSmpkgLocalDeliveryFileds];
            if(fedexSmpkgInstorePickupFileds !== '' && fedexSmpkgLocalDeliveryFileds !== ''){
                var whData = mergeWarehouseSectionObjects(arrObj);
            }else{
                var whData = parameters;
            }
            
            ajaxRequest(whData, ajaxUrl, fedexSmpkgwarehouseSaveResSettings);
        }
        return false;
    }
        
    function fedexSmpkgwarehouseSaveResSettings(data){

        var WarehouseDataId = data.id;
        if (data.insert_qry == 1) {

            jQuery('.warehouse_created').css('display' , 'block');
            window.location.href = jQuery('.close').attr('href');
            jQuery('#append_warehouse tr:last').after('<tr id="row_'+WarehouseDataId+'" data-id="'+WarehouseDataId+'"><td>'+data.origin_city+'</td><td>'+data.origin_state+'</td><td>'+data.origin_zip+'</td><td>'+data.origin_country+'</td><td><a href="javascript(0)" onclick="return fedexSmpkgEditWarehouse('+ WarehouseDataId +',\''+ fedexSmpkgWHEditAjaxUrl +'\');">Edit</a> / <a href="javascript(0)" onclick="return fedexSmpkgDeleteWarehouse('+ WarehouseDataId +',\''+ fedexSmpkgWHDeleteAjaxUrl +'\');">Delete</a></td></tr>');

            jQuery('html, body').animate({
                'scrollTop' : jQuery('.warehouse_text').position().top
            });

            setTimeout(function(){
                jQuery('.warehouse_created').hide('slow');
             }, 5000);
        }else if(data.update_qry == 1){
            jQuery('.warehouse_updated').css('display' , 'block');
            window.location.href = jQuery('.close').attr('href');

            jQuery('tr[id=row_'+WarehouseDataId+']').html('<td>'+data.origin_city+'</td><td>'+data.origin_state+'</td><td>'+data.origin_zip+'</td><td>'+data.origin_country+'</td><td><a href="javascript(0)" onclick="return fedexSmpkgEditWarehouse('+ WarehouseDataId +',\''+ fedexSmpkgWHEditAjaxUrl +'\');">Edit</a> / <a href="javascript(0)" onclick="return fedexSmpkgDeleteWarehouse('+ WarehouseDataId +',\''+ fedexSmpkgWHDeleteAjaxUrl +'\');">Delete</a></td>');

            jQuery('html, body').animate({
                'scrollTop' : jQuery('.warehouse_text').offset().top
            });
            jQuery( '#edit_form_id' ).val('');
            setTimeout(function(){
                jQuery('.warehouse_updated').hide('slow');
             }, 5000);
        }
        else{
            jQuery('.already_exist').show('slow');
//            jQuery('.warehouseSection').height('54%');
            setTimeout(function () {
                jQuery('.already_exist').hide('slow');
            }, 5000);
        }
        return true;
    }
        
    /**
     * Edit warehouse
     * @param {type} dataId
     * @param {type} ajaxUrl
     * @returns {Boolean}
     */
    function fedexSmpkgEditWarehouse(dataId, ajaxUrl)
    {
        var parameters = {
            'action'    : 'edit_warehouse',
            'edit_id'   : dataId
        };
        
        ajaxRequest(parameters, ajaxUrl, fedexSmpkgWarehouseEditResSettings);
        return false;
    }
        
    function fedexSmpkgWarehouseEditResSettings(data){
        if (data[0]) {
            jQuery( '#edit_form_id' ).val( data[0].warehouse_id );
            jQuery( '#fedexSmpkg_warehouse_zip' ).val( data[0].zip );
            jQuery( '.city_select' ).hide();
            jQuery( '.city_input' ).show();
            jQuery( '#warehouse_origin_city' ).val( data[0].city );
            jQuery( '#warehouse_origin_state' ).val( data[0].state );
            jQuery( '#warehouse_origin_country' ).val( data[0].country );
            
            // Load inside pikup and local delivery data
            typeof loadInsidePikupAndLocalDeliveryData !== 'undefined' && jQuery.isFunction(loadInsidePikupAndLocalDeliveryData)?loadInsidePikupAndLocalDeliveryData(data, '#fedexSmpkgWarehouseForm'):'';
            
            jQuery('.fedexSmpkg_warehouse_overlay').show();
            window.location.href = jQuery('.add_warehouse_btn').attr('href');
            setTimeout(function(){
                if(jQuery('.add_warehouse_popup').is(':visible')){
                  jQuery('.add_warehouse_input > input').eq(0).focus();
                }
              },500);

        }
        return true;
    }
    
    /**
     * Delete selected Warehouse
     * @param {type} dataId
     * @param {type} ajaxUrl
     * @returns {bool}
     */
    function fedexSmpkgDeleteWarehouse(dataId, ajaxUrl)
    {
        var parameters = {
            'action'    : 'delete_warehouse',
            'delete_id' : dataId
        };
        ajaxRequest(parameters, ajaxUrl, fedexSmpkgWarehouseDeleteResSettings);
        return false;
    }

    function fedexSmpkgWarehouseDeleteResSettings(data){
        
        if (data.qryResp == 1) {
            jQuery('#row_'+data.deleteID).remove();
            jQuery('.warehouse_deleted').show('slow');
            setTimeout(function () {
                jQuery('.warehouse_deleted').hide('slow');
            }, 5000);
        }
        return true;
    }
    