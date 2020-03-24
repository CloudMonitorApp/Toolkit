var cloudmonitor = cloudmonitor || (function(){
    return {
        init: function(token) {
            window.onerror = function (msg, file, line, col, error) {
                try {
                    var http = new XMLHttpRequest();
                    var params = [];

                    console.log(error);

                    StackTrace.fromError(error).then(function(s) {
                        params.push('user_agent=' + encodeURIComponent(((navigator.userAgent + '|' + navigator.vendor + '|' + navigator.platform + '|' + navigator.platform) || '').toString().substring(0, 150)));
                        params.push('line=' + encodeURIComponent((line || '').toString().substring(0, 150)));
                        params.push('colNumber=' + encodeURIComponent((col || '').toString().substring(0, 150)));
                        params.push('file=' + encodeURIComponent((file || '').toString().substring(0, 150)));
                        params.push('error=' + encodeURIComponent((error || '').toString().substring(0, 150)));
                        params.push('msg=' + encodeURIComponent((msg || '').toString().substring(0, 150)));
                        params.push('url=' + encodeURIComponent((window.location.href || '').toString().substring(0, 150)));
                        params.push('trace='+ JSON.stringify(s))
                
                        http.open('POST', '/cloudmonitor', true);
                        http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                        http.setRequestHeader('X-CSRF-TOKEN', token);
                        http.send(params.join('&'));
                    });
            
                    return error;
                }
                catch(e) {
                    console.log(e);
                }
            };
        }
    };
}());