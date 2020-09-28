/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */            

            
var Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});

var toastOverlay = Swal.mixin({
    position: 'center',
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    showConfirmButton: false
});    

var callToastOverlay = function(type, title)
{
    toastOverlay.fire({
        type: type,
        title: title,
        onBeforeOpen: () => {
            Swal.showLoading();
        }
    });
}

var callToast = function(type, message)
{
    Toast.fire({
        type: type,
        title: message
    });
}

var closeToastOverlay = function()
{
    toastOverlay.close();
}

