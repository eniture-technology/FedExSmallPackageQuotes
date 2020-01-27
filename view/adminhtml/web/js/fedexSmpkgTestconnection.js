
    /**
     * 
     * @param {type} ajaxURL
     * @returns {Boolean}
     */
    function fedexSmpkgTestConn(ajaxURL) {
        addfedexSmpkgTestConnTitle();
        var validationCheck = fedexSmpkgFieldsValidation('#fedexconnsettings_first');
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
        jQuery('#fedexconnsettings_first_title').attr('title', 'Plugin Title');
        jQuery('#fedexconnsettings_first_title').attr('data-optional', '1');
        jQuery('#fedexconnsettings_first_AccountNumber').attr('title', 'Account Number');
        jQuery('#fedexconnsettings_first_ProdutionPassword').attr('title', 'Production Password');
        jQuery('#fedexconnsettings_first_MeterNumber').attr('title', 'Meter Number');
        jQuery('#fedexconnsettings_first_AuthenticationKey').attr('title', 'Authentication Key');
        jQuery('#fedexconnsettings_first_licnsKey').attr('title', 'Plugin License Key');
    }
    
    /**
     * Test connection ajax call
     * @param {type} ajaxURL
     * @returns {Success or Error}
     */
    function fedexSmpkgTestConnectionAjaxCall(ajaxURL){
        var credentials = {
            accountNumber       : jQuery('#fedexconnsettings_first_AccountNumber').val(),
            productionPass      : jQuery('#fedexconnsettings_first_ProdutionPassword').val(),
            meterNumber         : jQuery('#fedexconnsettings_first_MeterNumber').val(),
            authenticationKey   : jQuery('#fedexconnsettings_first_AuthenticationKey').val(),
            pluginLicenceKey    : jQuery('#fedexconnsettings_first_licnsKey').val()
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
            hideShowDiv("fedexfailCon","errorText",data.Error);
        }
        else{
            hideShowDiv("fedexsuccessCon","succesText",data.Success);
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
