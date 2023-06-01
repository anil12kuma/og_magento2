define([
    'underscore',
    'uiRegistry',
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/modal/modal'
],  function (_, uiRegistry, ko, Component) {
    'use strict';
   return Component.extend({       
        isShowPhoneButton: ko.observable(false),        
        showPhonePopUp: function (){
            alert("I am Clicked");
        },
        onUpdate: function(){
            var self = this;
            this._super();
            if(this.checkInvalid()){                                
                self.isShowPhoneButton(false);
            } else {                
                self.isShowPhoneButton(true);
            }
        }       
    });
});