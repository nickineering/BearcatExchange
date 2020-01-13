window.onbeforeunload = setReloadingTrue;
var reloading = false;
function setReloadingTrue() {
    reloading = true;
}

function sendForm(successFunction, errorMessageFunction, page, dataString) {
    if (
        $.ajax({
            type: "POST",
            dataType: "json",
            data: dataString + "&isjson=true",
            beforeSend: function(x) {
                if (x && x.overrideMimeType) {
                    x.overrideMimeType("application/json;charset=UTF-8");
                }
            },
            url: page,
            success: function(data) {
                successFunction(data);
            },
            error: function() {
                if (reloading == false) {
                    errorMessageFunction(
                        "There was an error connecting to our server. Check your internet connection and try again.",
                    );
                }
            },
        })
    ) {
        return true;
    }
}
