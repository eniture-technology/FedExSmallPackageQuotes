/**
     * Document load function
     * @type type
     */
    
    require([ 'jquery', 'jquery/ui'], function($){ 
        $(document).ready(function($) {
            if($("#suspend-rad-use").length > 0 && $("#suspend-rad-use").is(":disabled") == false) {
                disablealwaysresidentialfedex();
                if (($('#suspend-rad-use:checkbox:checked').length)>0) {
                    $("#fedexQuoteSetting_third_residentialDlvry").prop({disabled: false});    
                } else {
                    $("#fedexQuoteSetting_third_residentialDlvry").val('0');
                    $("#fedexQuoteSetting_third_residentialDlvry").prop({disabled: true});
                }
            }
        });
        
        /**
        * windows onload
        */
        $(window).load(function(){
            if($("#suspend-rad-use").length > 0 && $("#suspend-rad-use").is(":disabled") == false) {
                if(!isdisabled){
                    if (($('#suspend-rad-use:checkbox:checked').length)>0) {
                       $("#fedexQuoteSetting_third_residentialDlvry").prop({disabled: false});
                   } else {
                       $("#fedexQuoteSetting_third_residentialDlvry").val('0');
                       $("#fedexQuoteSetting_third_residentialDlvry").prop({disabled: true});
                   }
               }
            }
        });
    });
    
    /**
     * 
     * @return {undefined}
     */
    function disablealwaysresidentialfedex(){
        jQuery("#suspend-rad-use").on('click', function ()
        {
            if (this.checked) {
                jQuery("#fedexQuoteSetting_third_residentialDlvry").prop({disabled: false});
            } else {
                jQuery("#fedexQuoteSetting_third_residentialDlvry").val('0');
                jQuery("#fedexQuoteSetting_third_residentialDlvry").prop({disabled: true});
            }
        });
    }
