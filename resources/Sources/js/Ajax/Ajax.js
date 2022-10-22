import {trim} from "lodash";

let $ = require('jquery')

export default class Ajax {
    static toastTime = 5000;

    jobRequest(){
        $(document).on('submit change','form.job',function(event){
            event.preventDefault();

            let options = {
                isFullyFilledForm       : !$(this).hasClass('nocheck_empty_formfields'),
                reload                  : $(this).hasClass('refresh_page'),
                updateAutomaticOnChange : $(this).hasClass('updateAutomaticOnChange'),
                toastTime               : 10000
            };
            console.log(options)

            if(event.type==='change' && options.updateAutomaticOnChange){
                Ajax.ajaxCallForm(this, event, options);
            }
            else if(event.type === 'submit'){
                Ajax.ajaxCallForm(this, event, options);
            }
        });
    }


    static ajaxSetup(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }



    /**
     * @param {this} $this this
     * @param {Event} event
     * @param options
     */
    static ajaxCallForm($this, event,options={isFullyFilledForm:true,reload:false,toastTime : this.toastTime}){

        console.log($($this));
        let action = trim($($this).find('input[type="submit"]').attr('value'))+"'";
        // let isReloadPage = $($this).hasClass('reload_page_after_job');
        if(confirm("Voulez-vous lancer l'action '" + action)){
            this.ajaxSetup()

            // OPTIONS
            let fullyFilled = true;
            if(options.isFullyFilledForm){
                let formArrayKeyValue = this.formSerializeArrayKeyValue($($this));
                fullyFilled = formArrayKeyValue.fullyFilled;
            }

            if(fullyFilled){
                // AJAX ACTIONS
                $.ajax({
                    url: $($this).attr('action'),
                    method : $($this).attr('method'),
                    data: new FormData($this),
                    processData: false,
                    contentType: false,
                    success: function (e){
                        console.log('success',e);
                        window.ajaxCallFormData = e;
                        showToast(e.toString(),"Job / Action : " + action,'success',options.toastTime);
                    },
                    error: function (e){
                        console.log('error',e);
                        let message = e.hasOwnProperty('responseJSON') ? e.responseJSON.message : e.responseText;
                        message = message === '' ? e.statusText : message;
                        showToast(message,"Job / Action : " + action,'danger',options.toastTime);
                    },
                    complete: function(){
                        // console.log('data', window.ajaxCallFormData)
                        if(options.reload){
                            location.reload();
                        }
                    }
                })
            }
            else{
                showToast('Les entrées du formulaire doivent être complétées.',"Job / Action : " + action,'warning',options.toastTime);
            }
        }
    }
}
