require(["jquery", "domReady!"], function ($) {
    /* Test Connection Validation */
    addfedexSmpkgTestConnTitle($);
    $('#test_fedexsmpkg_connection').click(function (event) {
        event.preventDefault();
        if ($('#config-edit-form').valid()) {
            let ajaxURL = $(this).attr('connAjaxUrl');
            fedexSmpkgTestConnectionAjaxCall($, ajaxURL);
        }
        return false;
    });
});
/**
 * Assign Title to inputs
 */
function addfedexSmpkgTestConnTitle($) 
{
    $('#fedexconnsettings_first_title').attr('title', 'Plugin Title');
    $('#fedexconnsettings_first_title').attr('data-optional', '1');
    $('#fedexconnsettings_first_fedexClientId').attr('title', 'API Key');
    $('#fedexconnsettings_first_fedexClientSecret').attr('title', 'Secret Key');
    $('#fedexconnsettings_first_AccountNumber').attr('title', 'Account Number');
    $('#fedexconnsettings_first_ProdutionPassword').attr('title', 'Production Password');
    $('#fedexconnsettings_first_MeterNumber').attr('title', 'Meter Number');
    $('#fedexconnsettings_first_AuthenticationKey').attr('title', 'Authentication Key');
    $('#fedexconnsettings_first_licnsKey').attr('title', 'Plugin License Key');
}
    
/**
 * Test connection ajax call
 * @param {type} ajaxURL
 * @returns {Success or Error}
 */
function fedexSmpkgTestConnectionAjaxCall($, ajaxURL){

    let endPoint = $('#fedexconnsettings_first_fedexEndPoint').val();

    let credentials = {
        accountNumber       : $('#fedexconnsettings_first_AccountNumber').val(),
        pluginLicenceKey    : $('#fedexconnsettings_first_licnsKey').val()
    };

    if (endPoint === '1') { 
        credentials.endPoint = 'legacy';
        credentials.productionPass = $('#fedexconnsettings_first_ProdutionPassword').val();
        credentials.meterNumber = $('#fedexconnsettings_first_MeterNumber').val();
        credentials.authenticationKey = $('#fedexconnsettings_first_AuthenticationKey').val();
    }else{
        credentials.endPoint = 'new';
        credentials.clientId = $('#fedexconnsettings_first_fedexClientId').val();
        credentials.clientSecret = $('#fedexconnsettings_first_fedexClientSecret').val();
    }

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

/**
 * Plan Refresh ajax call
 * @param {object} $
 * @param {string} ajaxURL
 * @returns {function}
 */
function fedexSmpkgPlanRefresh(e){
    let ajaxURL = e.getAttribute('planRefAjaxUrl');
    let parameters = {};
    ajaxRequest(parameters, ajaxURL, fedexSmpkgPlanRefreshResponse);
}

/**
 * Handle response
 * @param {object} data
 * @returns {void}
 */
function fedexSmpkgPlanRefreshResponse(data){}
