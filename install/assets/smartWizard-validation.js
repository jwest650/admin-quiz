"use strict"

$(document).ready(function () {
    // Smart Wizard         
    $('#wizard_verticle').smartWizard({
        transitionEffect: 'slideleft',
        onLeaveStep: leaveAStepCallback,
        onFinish: onFinishCallback,
    });

    function leaveAStepCallback(obj) {
        var step_num = obj.attr('rel');
        return validateSteps(step_num);
    }

    function onFinishCallback() {
        if (validateAllSteps()) {
            $('form').submit();
        }
    }

});

function validateAllSteps() {
    var isStepValid = true;

    if (validateStep1() == false) {
        isStepValid = false;
        $('#wizard_verticle').smartWizard('setError', {stepnum: 1, iserror: true});
    } else {
        $('#wizard_verticle').smartWizard('setError', {stepnum: 1, iserror: false});
    }

    var res = validateStep3();
    if (res.error == true) {
        isStepValid = false;
        $('#wizard_verticle').smartWizard('showMessage', res.message);
        $('#wizard_verticle').smartWizard('setError', {stepnum: 3, iserror: true});
    } else {
        $('#wizard_verticle').smartWizard('hideMessage');
        $('#wizard_verticle').smartWizard('setError', {stepnum: 3, iserror: false});
    }

    if (!isStepValid) {
        $('#wizard_verticle').smartWizard('showMessage', 'Please required all field.!');
    }
    return isStepValid;
}

function validateSteps(step) {
    var isStepValid = true;
    var minPHPVersion = '7.3';
    // validate step 1
    if (step == 1) {
        if (validateStep1() == false) {
            isStepValid = false;
            $('#wizard_verticle').smartWizard('showMessage', "Your PHP version must be " + minPHPVersion + " or higher");
            $('#wizard_verticle').smartWizard('setError', {stepnum: step, iserror: true});
        } else {
            $('#wizard_verticle').smartWizard('hideMessage');
            $('#wizard_verticle').smartWizard('setError', {stepnum: step, iserror: false});
        }
    }

//  validate step3
    if (step == 3) {
        if (validateStep3() == false) {
            isStepValid = false;
            $('#wizard_verticle').smartWizard('showMessage', 'Please correct the errors in step' + step + ' and click next.');
            $('#wizard_verticle').smartWizard('setError', {stepnum: step, iserror: true});
        } else {
            $('#wizard_verticle').smartWizard('hideMessage');
            $('#wizard_verticle').smartWizard('setError', {stepnum: step, iserror: false});
        }
    }

    return isStepValid;
}

function validateStep1() {
    var isValid = true;
    $('#step-1 input').each(function () {
        if ($(this).val() == 0) {
            isValid = false;
        }
    });
    return isValid;
}

function validateStep3() {
    var data = {
        'error': false,
        'message': ""
    };

    var hostname = $("#step-3 input#hostname").val();
    var database = $("#step-3 input#database").val();
    var username = $("#step-3 input#username").val();
    var password = $("#step-3 input#password").val();

    var jwt_key = $("#step-3 input#jwt_key").val();
    //&& password != ""
    if (hostname != "" && database != "" && username != "" && jwt_key != "") {
        data = {
            'error': false,
            'message': ""
        };
    } else {
        data = {
            'error': true,
            'message': "Please required all field."
        };
    }

    return data;
}

