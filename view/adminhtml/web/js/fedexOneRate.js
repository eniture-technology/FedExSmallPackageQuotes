/**
     * Document load function
     * @type type
     */
    
    require([ 'jquery', 'jquery/ui'], function($){ 
        $(document).ready(function($) {
            showNotification = false;
            var oneRateSrvcs = $('#fedexQuoteSetting_third_FedExOneRateServices > option').length;
            if(oneRateSrvcs > 0){
                showNotification = true;
            }
            $('#fedexQuoteSetting_third_FedExOneRateServices > option').on('click', function(){
                if(oneRateSrvcs > 0){
                    showNotification = true;
                }
            });
            
            if(showNotification){
                $('#fedexQuoteSetting_third-head').after('<div class="fedex-onerate-note">Standard Box Sizes feature is required for One Rate services.</div>');
            }
        });
    });
