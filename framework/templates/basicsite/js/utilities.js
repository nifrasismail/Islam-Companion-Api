var Utilities = {
/**********************************************************************************************************************/
    /**	 
     Used to make an ajax call. this function accepts following parameters: 
     url => url used in ajax request. method=>possible values are GET and POST.
     arguments => the parameters sent with ajax call.
     callaback => the function that is called after successfull response from server is recieved.
     error_call_back => the function that is called when an error response from server is received.	 
    */
    MakeAjaxCall: function(url, method, arguments, callback, error_call_back) {
            try {
                var request = new XMLHttpRequest();
                /** The function is called when the XMLHttpRequest object state changes */
                request.onreadystatechange = function() {
                    try {
                        var DONE = this.DONE || 4;
                        if (this.readyState === DONE) {
                            response = JSON.parse(this.responseText);
                            if (response.result != "success" && error_call_back) error_call_back(response);
                            else if (callback) callback(response);
                        }
                    } catch (err) {
                        Callbacks.ErrorCallBack();
                    }
                };
                /** The function is called when an error occurs */
                request.onerror = function() {
                    /** The error callback is called */
                    Callbacks.ErrorCallBack(result);
                };
                var parameters = "";
                /** Each parameter is added */
                for (field_name in arguments) {
                    parameters = parameters + field_name + "=" + arguments[field_name] + "&";
                }
                /** If the parameters are given */
                if (parameters.length > 0) {
                    /** The trailing & is removed */
                    parameters = parameters.substr(0, parameters.length - 1);
                    if (method == "GET") {
                        if (url.indexOf("?") > 0) url = url + "&" + parameters;
                        else url = url + "?" + parameters;
                    }
                    /** The XMLHttpRequest is opened */
                    request.open(method, url, true);
                    /** The request headers are sent. It indicates to server that ajax call is being made */
                    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    /** If the http method is set to POST */
                    if (method == "POST") {
                        /** The http request headers for post data is sent */
                        request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    }
                    /** The request parameters are sent */
                    request.send(parameters);
                }
                /** If no parameters are given */
                else {
                    /** The XMLHttpRequest is opened */
                    request.open(method, url, true);
                    /** The request headers are sent. It indicates to server that ajax call is being made */
                    request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    /** The parameters for the XMLHttpRequest */
                    /** If the HTTP method is GET */
                    if (method == "GET") {
                        /** The request is sent */
                        request.send();
                    }
                    /** If the HTTP method is POST */
                    else if (method == "POST") {
                        /** The request is sent */
                        request.send(null);
                    }
                }
            } catch (err) {
                Callbacks.ErrorCallBack();
            }
        }
/**********************************************************************************************************************/
};
var Callbacks = {
    /**
     * It is called when the ajax call fails or if there is an error in the application
     * 
     * @param array result the response from the application
     */
    ErrorCallBack: function(result) {
        alert("An error has occured in the application. Please contact the system administrator");
    },

    /**
     * It is called when the ajax call gets a response from the server
     * 
     * @param array result the response from the server
     */
    DeleteItemCallback: function(response) {
        /**
         * If the server returned a success in the response then the success message is shown
         * And the user is redirected to the given url
         */
        if (response.result == "success") {
            alert("Data was successfully deleted");
            location.href = response.data;
        } else {
            alert("Data could not be deleted. Please contact the system administrator");
        }
    }
};
/**********************************************************************************************************************/
