  
    /**
     * Set Address from zipCode
     * @param {type} data
     * @returns {Boolean}
     */
        function fedexSmpkgGetDsAddressResSettings(data){
            if( data.country === 'US' || data.country === 'CA'){
                if (data.postcode_localities === 1) {
                    jQuery( '.city_select' ).show();
                    jQuery( '#dropship_actname' ).replaceWith( data.city_option );
                    jQuery( '.city-multiselect' ).replaceWith( data.city_option );
                    jQuery( '.city-multiselect' ).change( function(){
                        var city = jQuery(this).val();
                        jQuery('#dropship_city').val(city);
                    });
                    jQuery( "#dropship_city" ).val( data.first_city );
                    jQuery( '#dropship_state' ).val( data.state );
                    jQuery( '#dropship_country' ).val( data.country );
                    jQuery( '.city_input' ).hide();
                }else{
                    jQuery( '.city_input' ).show();
                    jQuery( '#_city' ).removeAttr('value');
                    jQuery( '.city_select' ).hide();
                    jQuery( '#dropship_city' ).val( data.city );
                    jQuery( '#dropship_state' ).val( data.state );
                    jQuery( '#dropship_country' ).val( data.country );
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

            return false;
        }
    
    /**
     * Save Dropship Function
     * @param {type} ajaxUrl
     * @returns {Boolean}
     */
        function fedexSmpkgSaveDropship(ajaxUrl) 
        {
            var fedexSmpkgDsFormID = '#fedexSmpkgDropshipForm';
            jQuery('.local-delivery-fee-err').remove();
            var enable_instore_pickup = jQuery(fedexSmpkgDsFormID + " #enable-instore-pickup").is(':checked');
            var enable_local_delivery = jQuery(fedexSmpkgDsFormID + " #enable-local-delivery").is(':checked');

            switch(true){
                case (enable_instore_pickup && ( jQuery(fedexSmpkgDsFormID + " #instore-pickup-address").val().length == 0 && en_wd_check_postal_length(fedexSmpkgDsFormID + " #instore-pickup-zipmatch") )):
                    jQuery('.instore-miles-postal-err').show('slow');
                    document.querySelector(fedexSmpkgDsFormID + " .instore-pickup-heading").scrollIntoView({ behavior: 'smooth' });
                    setTimeout(function(){ jQuery('.instore-miles-postal-err').hide('slow'); }, 5000);
                    return false;

                case (enable_local_delivery && ( jQuery(fedexSmpkgDsFormID + " #local-delivery-address").val().length == 0 && en_wd_check_postal_length(fedexSmpkgDsFormID + " #local-delivery-zipmatch") )):
                    jQuery('.local-miles-postals-err').show('slow');
                    document.querySelector(fedexSmpkgDsFormID + " .local-miles-postals-err").scrollIntoView({ behavior: 'smooth' });
                    setTimeout(function(){ jQuery('.local-miles-postals-err').hide('slow'); }, 5000);
                    return false;

                case (enable_local_delivery && jQuery(fedexSmpkgDsFormID + " #local-delivery-fee").val().length <= 0):
                    jQuery(fedexSmpkgDsFormID + " #local-delivery-fee").after('<span class="local-delivery-fee-err">Local delivery fee is required.</span>');
                    return false;
                }
           
            var validationCheck = fedexSmpkgFieldsValidation(fedexSmpkgDsFormID);
            var fedexSmpkgInstorePickupFileds = typeof instorePickupInputVal !== 'undefined' && jQuery.isFunction(instorePickupInputVal)?instorePickupInputVal(fedexSmpkgDsFormID):'';
            var fedexSmpkgLocalDeliveryFileds = typeof localDeliveryInputVal !== 'undefined' && jQuery.isFunction(localDeliveryInputVal)?localDeliveryInputVal(fedexSmpkgDsFormID):'';
          
            if(validationCheck == true){
                var city     = jQuery('#dropship_city').val();
                var parameters = {
                    'action'        : 'fedexSmpkgDropship',
                    'nickname'      : jQuery( '#fedexSmpkg_dropship_nickname' ).val(),
                    'dropshipId'    : jQuery( '#edit_dropship_form_id' ).val(),
                    'city'          : city,
                    'state'         : jQuery( '#dropship_state' ).val(),
                    'zip'           : jQuery( '#fedexSmpkg_dropship_zip' ).val(),
                    'country'       : jQuery( '#dropship_country' ).val(),
                    'location'      : 'dropship',
                };
                
                var dsArrObj = [parameters, fedexSmpkgInstorePickupFileds, fedexSmpkgLocalDeliveryFileds];
                if(fedexSmpkgInstorePickupFileds !== '' && fedexSmpkgLocalDeliveryFileds !== ''){
                    var dsData = mergeWarehouseSectionObjects(dsArrObj);
                }else{
                    var dsData = parameters;
                }
                
                ajaxRequest(dsData, ajaxUrl, fedexSmpkgDropshipSaveResSettings);
            }
            return false;
        }
        
        function fedexSmpkgDropshipSaveResSettings(data){
            var dropshipDataId = data.id;
            if (data.insert_qry == 1) {

                jQuery('.dropship_created').css('display' , 'block');
                window.location.href = jQuery('.close').attr('href');
                
                jQuery('#append_dropship tr:last').after('<tr id="row_'+dropshipDataId+'" data-id="'+dropshipDataId+'"><td>'+data.nickname+'</td><td>'+data.origin_city+'</td><td>'+data.origin_state+'</td><td>'+data.origin_zip+'</td><td>'+data.origin_country+'</td><td><a href="javascript(0)" onclick="return fedexSmpkgEditDropship('+ dropshipDataId +',\''+ fedexSmpkgDSEditAjaxUrl +'\');">Edit</a> / <a href="javascript(0)" onclick="return fedexSmpkgDeleteDropship('+ dropshipDataId +',\''+ fedexSmpkgDSDeleteAjaxUrl +'\');">Delete</a></td></tr>');
                
                jQuery('html, body').animate({
                    'scrollTop' : jQuery('.ds').offset().top
                });

                setTimeout(function(){
                    jQuery('.dropship_created').hide('slow');
                 }, 5000);
            }else if(data.update_qry == 1){
                jQuery('.dropship_updated').css('display' , 'block');
                window.location.href = jQuery('.close').attr('href');

                jQuery('tr[id=row_'+dropshipDataId+']').html('<td>'+data.nickname+'</td><td>'+data.origin_city+'</td><td>'+data.origin_state+'</td><td>'+data.origin_zip+'</td><td>'+data.origin_country+'</td><td><a href="javascript(0)" onclick="return fedexSmpkgEditDropship('+ dropshipDataId +',\''+ fedexSmpkgDSEditAjaxUrl +'\');">Edit</a> / <a href="javascript(0)" onclick="return fedexSmpkgDeleteDropship('+ dropshipDataId +',\''+ fedexSmpkgDSDeleteAjaxUrl +'\');">Delete</a></td></tr>');
                jQuery('html, body').animate({
                    'scrollTop' : jQuery('.ds').offset().top
                });

                setTimeout(function(){
                    jQuery('.dropship_updated').hide('slow');
                 }, 5000);
            }
            else{
                jQuery('.already_exist').show('slow');
                setTimeout(function () {
                    jQuery('.already_exist').hide('slow');
                }, 5000);
            }
                    
            return true;
        }
        
    /**
     * Edit warehouse
     * @param {type} e
     * @param {type} ajaxUrl
     * @returns {Boolean}
     */
        function fedexSmpkgEditDropship(dataId, ajaxUrl)
        {
            var parameters = {
                'action'    : 'edit_dropship',
                'edit_id'   : dataId
            };

            ajaxRequest(parameters, ajaxUrl, fedexSmpkgDropshipEditResSettings);
            return false;
        }
        
        function fedexSmpkgDropshipEditResSettings(data){
            if (data[0]) {
                jQuery( '#edit_dropship_form_id' ).val( data[0].warehouse_id );
                jQuery( '#fedexSmpkg_dropship_zip' ).val( data[0].zip );
                jQuery( '#fedexSmpkg_dropship_nickname' ).val( data[0].nickname );
                jQuery( '.city_select' ).hide();
                jQuery( '.city_input' ).show();
                jQuery( '#dropship_city' ).val( data[0].city );
                jQuery( '#dropship_state' ).val( data[0].state );
                jQuery( '#dropship_country' ).val( data[0].country );
                
                // Load inside pikup and local delivery data
                typeof loadInsidePikupAndLocalDeliveryData !== 'undefined' && jQuery.isFunction(loadInsidePikupAndLocalDeliveryData)?loadInsidePikupAndLocalDeliveryData(data, '#fedexSmpkgDropshipForm'):'';

                jQuery('.fedexSmpkg_warehouse_overlay').show();
                window.location.href = jQuery('.add_dropship_btn').attr('href');
                setTimeout(function(){
                    if(jQuery('.ds-popup').is(':visible')){
                      jQuery('.ds-input > input').eq(0).focus();
                    }
                  },500);

            }
            return true;
        }
        
    /**
    * Delete selected Warehouse
    * @param {type} e
    * @param {type} ajaxUrl
    * @returns {bool}
    */
        
        function fedexSmpkgDeleteDropship(dataId, ajaxUrl)
        {
            var id = dataId;
            jQuery('.fedexSmpkg_warehouse_overlay').show(); 
            window.location.href = jQuery('.delete_dropship_btn').attr('href');
            
            jQuery('.cancel_delete').on('click', function(){
                jQuery('.fedexSmpkg_warehouse_overlay').hide(); 
                window.location.href = jQuery('.cancel_delete').attr('href');
            });
            jQuery('.confirm_delete').on('click', function(){
                jQuery('.fedexSmpkg_warehouse_overlay').hide(); 
                window.location.href = jQuery('.confirm_delete').attr('href');
                return fedexSmpkgConfirmDeleteDropship(id, ajaxUrl);
            });
            return false;
        }
        
        function fedexSmpkgConfirmDeleteDropship(deleteid, ajaxUrl)
        {
            var parameters = {
                   'action'    : 'delete_dropship',
                   'delete_id' : deleteid
               };
               ajaxRequest(parameters, ajaxUrl, fedexSmpkgDropshipDeleteResSettings);
                
            return false;
        }
        
        function fedexSmpkgDropshipDeleteResSettings(data){
            if (data.qryResp == 1) {
                 jQuery('#row_'+data.deleteID).remove();
                 jQuery('.dropship_deleted').show('slow');
                 jQuery('html, body').animate({
                    'scrollTop' : jQuery('.ds').offset().top
                });
                 setTimeout(function () {
                     jQuery('.dropship_deleted').hide('slow');
                 }, 5000);
             }
            return true;
        }