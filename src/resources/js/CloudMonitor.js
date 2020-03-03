window.onerror = function (messageOrEvent, source, lineno, colno, error) {
    try {
        console.log({
            //error message(string).Available as event (sic!) in HTML onerror = "" handler.
            messageOrEvent: messageOrEvent, 
            //URL of the script where the error was raised(string)
            source: source, 
            //Line number where error was raised(number)
            lineno: lineno, 
            //Column number for the line where the error occurred(number)
            colno: colno, 
            //Error Object(object)
            error: error 
        });
        

        /*
         * WE THEN MAKE A REQUEST TO YOUR SERVERS AS IF YOU ARE LOADING A
         * JAVASCRIPT FILE. THE URL WILL CONTAIN THE QUERY PARAMETERS THAT
         * YOU WANT TO PASS TWO THE SERVER.
         * THIS METHOS HAS TWO ADVANTAGES:
         *  1. THE CODE FOR SENDING THE REQUEST IS SIMPLE AS WE ARE JUST CREATING/CHANGING THE SCRIPT TAG `src`
         *  2. WE CAN RETURN SOME BEHAVIOR BACK TO THE BROWSER FROM THE SERVER POSSIBLE REDIRECTING USERS TO SOME OTHER LOCATION
         */

        //placeholder array for request parameters
        var params = [], 
            //saves a unique id to prevent creating a new script tags for each error
            ___guid = window.onerror___guid || (window.onerror___guid = (new Date().getTime() + '-' + new Date().getTime())), //a guidto for the error script element id
            //create a new function if none exists with the unique id
            ___logError = function (___url) {
                ___domScript = document.getElementById(___guid);
                if (!___domScript) {
                    var ___head = document.head || document.getElementsByTagName('head')[0],
                        ___domScript = document.createElement('script');

                    ___domScript.id = ___guid;
                    ___domScript.async = 'async';

                    ___head.insertBefore(___domScript, ___head.firstChild);
                }

                ___domScript.src = ___url;
            };

        params.push('browser=' + encodeURIComponent(((navigator.userAgent + '|' + navigator.vendor + '|' + navigator.platform + '|' + navigator.platform) || '').toString().substring(0, 150)));
        params.push('lineNumber=' + encodeURIComponent((lineno || '').toString().substring(0, 150)));
        params.push('colNumber=' + encodeURIComponent((colno || '').toString().substring(0, 150)));
        params.push('source=' + encodeURIComponent((source || '').toString().substring(0, 150)));
        params.push('error=' + encodeURIComponent((error || '').toString().substring(0, 150)));
        params.push('messageOrEvent=' + encodeURIComponent((messageOrEvent || '').toString().substring(0, 150)));
        params.push('url=' + encodeURIComponent((window.location.href || '').toString().substring(0, 150)));


        // `/JavascriptError` is your servers endpoint
        ___logError('/cloudmonitor?' + params.join('&'));
    }
    catch (e) {
        // squelch, because we don’t want to prevent method from returning true
        console.log(e);
    }

    //When the function returns true, this prevents the firing of the default event handler.
    return true;
};
