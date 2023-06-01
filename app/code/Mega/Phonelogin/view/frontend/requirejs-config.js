var config = {
    // map: {
    //     '*': {
    //         intlTelInput: "Mega_Phonelogin/js/plugin/intlTelInput.min",
    //     }
    // },
    paths: {
        "intlTelInput": 'Mega_Phonelogin/js/intlTelInput',
        "intlTelInputUtils": 'Mega_Phonelogin/js/utils',
        "internationalTelephoneInput": 'Mega_Phonelogin/js/internationalTelephoneInput'
    },
    shim: {
        'intlTelInput': {
            'deps':['jquery', 'knockout']
        },
        'internationalTelephoneInput': {
            'deps':['jquery', 'intlTelInput']
        }
    }
    // "shim": {
    //     intlTelInput: "Mega_Phonelogin/js/plugin/intlTelInput.min",
    //
    // }
};

