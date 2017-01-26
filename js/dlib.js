function lefts(str, n){
    if (n <= 0)
        return "";
    else if (n > String(str).length)
        return str;
        else
            return String(str).substring(0, n);
}

function rights(str, n){
    if (n <= 0)
        return "";
    else if (n > String(str).length)
        return str;
        else {
            var iLen = String(str).length;
            return String(str).substring(iLen, iLen - n);
        }
}

function len(str){
    return str.length;
}

function ldel(str, n){
    return String(str).substring(n,len(str));
}

function rdel(str, n){
    return String(str).substring(0,len(str)-n);
}

/**
*  funkcija pos
*  prvi index je 0
*  ukoliko rema rezultata vraća -1
*/

function pos(substr,str){
    return String(str).indexOf(substr);
}

function rpos(substr,str){
    return  String(str).lastIndexOf(substr);
}

/**
*  funkcija mids
*  prvi index je 0
*/

function mids(str, posfrom, poslen){
    return String(str).substring(posfrom, posfrom+poslen);
}

function base64_encode(str){
    if (typeof(btoa) === 'function') {
        return btoa(str);
    } else {
        return Base64.encode(txt);
    }
}

function base64_decode(str){
    if (typeof(atob) === 'function') {
        return atob(str);
    } else {
        return Base64.decode(txt);
    }
}

var Base64 = {

    // private property
    _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

    // public method for encoding
    encode : function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output +
            this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
            this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },

    // public method for decoding
    decode : function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    // private method for UTF-8 encoding
    _utf8_encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }
} 









var dlib = function(){

    
 

    return {
        grid_select_first: function (grid_id, value){
            if ((value === undefined) || (value==='') || (value === null))
                var el=$("#"+grid_id).find("div[data-id]").first();   
            else
                var el=$("#"+grid_id).find("div[data-id='"+value+"']");   
            if (el.length !==0) {
                el.closest('tr').addClass('selected');
                return el.attr('data-id');
            } 
            else
                return false; 
        },




        post_form: function (id, url){
            var fdata = new FormData();
            var form=$('#'+id);
            var params = $(form).serializeArray();
            $.each(params, function (i, val) {
                fdata.append(val.name, val.value);
            });
            fdata.append('formId', id);

            $.each($(form).find('input[type=\'file\']'), function(i, tag) {
                $.each($(tag)[0].files, function(i, file) {
                    fdata.append(tag.name, file);
                });
            });

            jQuery.ajax({
                'url': url,
                'data':fdata,
                'type':'post',
                'dataType':'script',
                'processData': false,
                'contentType': false,
                'cache':false,
                'success':function(data){

                },
                'complete':function(jqXHR, textStatus){

                },
            });
            // event.preventDefault(); //ne ovdje
            // event.stopPropagation(); //ne ovdje
        },


        message_saving: function(message) {
            if (message === undefined) {
                message = '<b>Please wait</b>';
            } 
            $.bootstrapGrowl(message, {
                type: 'danger',
                width: 'auto',
                delay: 10000,
                allow_dismiss: false, 
            });
        },
        
        message_saved: function(message) {
            if (message === undefined) {
                message = '<b>Saved</b>';
            } 
            $('.bootstrap-growl').remove();
            $.bootstrapGrowl(message, {
                type: 'success',
                width: 'auto',
                delay: 2000,
                allow_dismiss: true, 
            });
        },
        
        page_block:function(){
            $('<div class="spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>').appendTo(document.body);
        },
        page_unblock:function(){
            $(".spinner").remove();  
        },



    }
}();


    $.fn.extend({
    animateCss: function (animationName, callback) {
        var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        this.addClass('animated ' + animationName).one(animationEnd, function() {
            $(this).removeClass('animated ' + animationName);
            if (typeof callback == 'function') { // make sure the callback is a function
        callback.call(this); 
            }
        });
    }
});

 $.fn.extend({
     fadeOutIn: function (msg) {
        this.animateCss('fadeOut', function(){
           $(this).html(msg);
           $(this).animateCss('fadeIn');
        });
     }
});


