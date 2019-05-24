
    /**
     * 
     * @param {type} ajaxURL
     * @returns {Boolean}
     */
    function fedexSmpkgTestConn(ajaxURL) {
        addfedexSmpkgTestConnTitle();
        var validationCheck = fedexSmpkgFieldsValidation('#carriers_ENFedExSmpkg');
        if(validationCheck == true){
            fedexSmpkgTestConnectionAjaxCall(ajaxURL);
        }
        return false;
    }
    
    /**
     * Assign Title to inputs
     */
    function addfedexSmpkgTestConnTitle() 
    {
        jQuery('#carriers_ENFedExSmpkg_title').attr('title', 'Plugin Title');
        jQuery('#carriers_ENFedExSmpkg_title').attr('data-optional', '1');
        jQuery('#carriers_ENFedExSmpkg_AccountNumber').attr('title', 'Account Number');
        jQuery('#carriers_ENFedExSmpkg_ProdutionPassword').attr('title', 'Production Password');
        jQuery('#carriers_ENFedExSmpkg_MeterNumber').attr('title', 'Meter Number');
        jQuery('#carriers_ENFedExSmpkg_AuthenticationKey').attr('title', 'Authentication Key');
        jQuery('#carriers_ENFedExSmpkg_licnsKey').attr('title', 'Plugin License Key');
    }
    
    /**
     * Test connection ajax call
     * @param {type} ajaxURL
     * @returns {Success or Error}
     */
    function fedexSmpkgTestConnectionAjaxCall(ajaxURL){
        var credentials = {
            accountNumber       : jQuery('#carriers_ENFedExSmpkg_AccountNumber').val(),
            productionPass      : jQuery('#carriers_ENFedExSmpkg_ProdutionPassword').val(),
            meterNumber         : jQuery('#carriers_ENFedExSmpkg_MeterNumber').val(),
            authenticationKey   : jQuery('#carriers_ENFedExSmpkg_AuthenticationKey').val(),
            pluginLicenceKey    : jQuery('#carriers_ENFedExSmpkg_licnsKey').val()
        };

        ajaxRequest(credentials, ajaxURL, fedexSmpkgConnectSuccessFunction);
        
    }
    
    /**
     * 
     * @param {type} data
     * @returns {undefined}
     */
    function fedexSmpkgConnectSuccessFunction(data){
        if (data.Error) {
            hideShowDiv("failCon","errorText",data.Error);
        }
        else{
            hideShowDiv("successCon","succesText",data.Success);
        }
    }
    
    /**
     * 
     * @param {type} divId
     * @param {type} textId
     * @param {type} text
     * @returns {undefined}
     */
    function hideShowDiv(divId,textId,text){
        jQuery("#"+textId).text(text);
        jQuery("#"+divId).show('slow');     
        setTimeout(function () {
            jQuery("#"+divId).hide('slow');
        }, 5000);
    }