var condition = ["New", "Like New", "Very Good", "Good", "Acceptable", "Ask"];
var pageTitles = {buy : 'Bearcat Exchange - Buy and sell textbooks at Binghamton', sell : 'Sell Textbooks On Bearcat Exchange', account : 'Edit Your Listings On Bearcat Exchange', faq : 'Common Questions About Bearcat Exchange', legal : 'Terms and Privacy For Bearcat Exchange', feedback : 'Give Feedback About Bearcat Exchange'};
var textbooks;
var selectedRows = [];
window.onhashchange = pageChangeHandler;
var errorMessageViews = 0;
var subjectCodes = ["AAAS", "ACCT", "AFST", "ANTH", "ARAB", "ARTH", "ARTS", "ASTR", "BCHM", "BE", "BIOL", "BLS", "BME", "CCPA", "CDCI", "CHEM", "CHIN", "CINE", "CLAS", "COLI", "CQS", "CS", "CW", "DDPR", "DDP", "ECON", "EDUC", "EECE", "EGYN", "ELED", "ENG", "ENT", "ENVI", "ERED", "ESL", "EVOS", "FIN", "FREN", "GEOG", "GEOL", "GERM", "GLST", "GRD", "GRK", "HARP", "HDEV", "HEBR", "HIST", "HWS", "IBUS", "ISE", "ITAL", "JPN", "JUST", "KOR", "LACS", "LAT", "LEAD", "LING", "LTRC", "LXC", "MASS", "MATH", "MDVL", "ME", "MGMT", "MIS", "MKTG", "MSE", "MSL", "MUS", "MUSP", "NURS", "OPM", "OUT", "PAFF", "PERS", "PHIL", "PHYS", "PIC", "PLSC", "PPL", "PSYC", "RHET", "RLIT", "ROML", "RPHL", "RUSS", "SAA", "SCHL", "SCM", "SEC", "SOC", "SPAN", "SPED", "SSIE", "SW", "THEA", "THEP", "TRIP", "TURK", "UNIV", "VIET", "WGSS", "WRIT", "WTSN", "YIDD"];
var loadDate= new Date();
var submittingInfoBoxId = 0;
var currentPage;
var pageViews = 0;
var useAnalytics = true;//Google Analytics stuff
var consentedToNewsletter = false;
var userData;
var $rows;
var errorCode = 0;
var developmentServer = false;
var buyScroll = 0;
var shouldAutoselect = Math.max(document.documentElement.clientWidth, window.innerWidth || 0) > 1200 || !$( "#open-html" ).hasClass( "touch" );

function startJavascript (localErrorCode, data, devServer, pageToLoad){
    currentPage = window.location.pathname.replace(/\//g, '');
    window.scrollTo(0,0);
    $rows = $('#textbooks tbody tr:not(.soldUserItem)');
    developmentServer = devServer;
    userData = data;
    errorCode = localErrorCode;
    if(errorCode >= 500){
        $('.form-noscript-warning').removeClass("hidden");
    }
    if(userData.email) {
        $.updateCookie('prefs', 'name', userData.name, { expires: 90 });
        $.updateCookie('prefs', 'email', userData.email, { expires: 90 });
    }
    printPrefs();
    hideNewsletters();
    if (pageToLoad != '.') {
        window.location.hash = pageToLoad;
    }
    //Start Google Analytics code
    try { //Turn off analytics if 'analytics=off' is included as a request parameter.
        if (getUrlVars()["analytics"] == 'off' || developmentServer == true) {
            $.cookie('analytics', 'off', { expires: 180 });
        }
        else if (getUrlVars()["analytics"] == 'on') {
            $.removeCookie('analytics');
        }
        if($.cookie('analytics') == 'off') {
            useAnalytics = false;
            $('#legal-link').append('<br><a href="/phpmyadmin/" target="_blank">PHPMyAdmin</a><br><a href="/?analytics=on">Analytics is off</a>');
        }
    } catch(e) {
        //Just in case something goes wrong...
        useAnalytics = true;
    }
    if(useAnalytics) {
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                                })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
        ga('create', 'UA-58620297-1', 'auto');
        ga('require', 'linkid', 'linkid.js');
        ga('require', 'displayfeatures');
        ga('send', 'pageview');
    }
    //End Google Analytics code
    if(!$rows.exists()) {
        $("#search-message").removeClass("hidden");
        $("#textbooks thead th").css("border-bottom-width", "0");
    }
    var infoBox = $('#info-box').html();
    Handlebars.parse(infoBox);   // optional, speeds up future uses
    var handlebarsInfoBoxCompilation = Handlebars.compile(infoBox);
    var infoBoxEdit = $('#info-box-edit').html();
    Handlebars.parse(infoBoxEdit);
    var handlebarsInfoBoxEditCompilation = Handlebars.compile(infoBoxEdit);
    $('.items tbody').on('click', 'td', function () {
        if(!$(this).hasClass("status")) {
            closeAlertBox();
            var id = $(this).parent().attr("item");
            var idInSelectedRows = selectedRows.indexOf(id);
            if (idInSelectedRows == -1){
                if(selectedRows.length < Math.max(Math.max(document.documentElement.clientWidth, window.innerWidth || 0) / 320 -1, 1)){
                    selectedRows.push(id);
                    $(this).parent().addClass('selected');
                    var rowData = {
                        id: id,
                        title: $("[item=" + id + "] .title").html(),
                        author: $("[item=" + id + "] .author").html(),
                        course: $("[item=" + id + "] .course").html(),
                        price: $("[item=" + id + "] .price .val").html(),
                        time: $("[item=" + id + "] .time").html(),
                        comments: $("[item=" + id + "] .comments").html()
                    };
                    if(rowData.title.length > 28){
                        rowData.boxTitle = rowData.title.slice(0,25) + '...';//replace with regex
                    }
                    else {
                        rowData.boxTitle = rowData.title;
                    }
                    var infoBoxEdit = false;
                    if($("[item=" + id + "]").hasClass("userItem")){
                        infoBoxEdit = true;
                        var checked = $("#owned-items [item=" + id + "] .status input").prop('checked');
                        rowData.checkedForSold = (checked)?"checked":"";
                        var renderedInfoBox = handlebarsInfoBoxEditCompilation(rowData);
                    }
                    else{
                        var renderedInfoBox = handlebarsInfoBoxCompilation(rowData);
                    }
                    $('#info-box-area').append(renderedInfoBox);
                    hideNewsletters();
                    var infoBoxInstance = $('#info-box-' + id);
                    var rightPosition = parseInt(infoBoxInstance.css('right')) + (selectedRows.length-1) * 320;
                    infoBoxInstance.css('right', rightPosition);
                    printPrefs();
                    $('body').addClass("infoBoxOpen");
                    $('#info-box-' + id + '-name').focus();
                }
            }
            else{
                closeInfoBox(id);
            }
        }
    });
    $(".status input").change(function() {
        var $checkbox = $(this);
        var itemId = $checkbox.parent().parent().attr("item");
        var status = '';
        if ($checkbox.prop('checked')) {
            status = "sold";
            $checkbox.parent().parent().addClass("sold");
            $('[item='+itemId+'].userItem').addClass("soldUserItem");
        } else {
            status = "unsold";
            $checkbox.parent().parent().removeClass("sold");
            $('[item='+itemId+'].userItem').removeClass("soldUserItem");
        }
        colorizeTextbooks();
        var soldInputs = {//get the values submit
            itemId : {
                category : 'novalidate',
                fieldValue : itemId
            },
            status : {
                category : 'novalidate',
                fieldValue : status
            },
            request : {
                category : 'novalidate',
                fieldValue : 'update-item'
            }
        };
        if(validateInputs(soldInputs, 'sold-form', receivedSoldFormResponse, miscMessage) == false) {
            miscMessage('There was a problem updating the status of your item.', "error");
        }
    });
    $(function() {
        $(".courseEdit").autocomplete({
            source: subjectCodes
        });
    });
    $("button:reset").click(function() {
        this.form.reset();
        $('#' + $(this).closest("form").attr('id') + ' .form-error label').html('');
        $('#' + $(this).closest("form").attr('id') + ' .form-message-wrapper').addClass('hidden');
        $('#' + $(this).closest("form").attr('id') + ' input[cookie]').trigger('change');
        return false;
    });
    $("#search-bar").keyup(function(event) {
        if(event.keyCode == 13 && !shouldAutoselect){
            document.getElementById("search-bar").blur();
        }
        var $searchVal = $(this).val();
        if($searchVal != ''){
            pageChangeHandler('buy');
            $('#clear').removeClass('hidden');
            var val = $.trim($searchVal).replace(/ +/g, ' ').toLowerCase();
            $rows.removeClass("hiddenRow").filter(function() {
                var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).addClass("hiddenRow");
            $visibleRows = $('#textbooks tbody tr:not(.hiddenRow):not(.soldUserItem)');
            if(!$visibleRows.exists()){
                searchMessage('No one listed that textbook yet. <a href="#sell" onClick="sellItYourself(&quot;sell&quot;);">Try selling it yourself!</a>');
            }
            else {
                searchMessage(false);
            }
        }
        else {
            clearSearchBar();
        }
        colorizeTextbooks();
    });
    $(document).on( "change", "input[cookie]", storePrefs);
    if(currentPage != 'sell' && !$(".alert-message").length){
        miscMessage("<a href='#sell'>Ready to get a head start on textbook selling? List your textbooks now!</a>", 'info');
    }
}

function hideNewsletters () {
    if(userData.newsletter == "subscribed" && $.cookie("prefs").email == userData.email) {
        $(".newsletter-form").addClass("hidden");
    }
}

function printPrefs () {
    if($.cookie("prefs")){
        for (pref in $.cookie('prefs')) {
            $('[cookie='+pref+"]").val($.cookie('prefs')[pref]);
        }
    }
}

function storePrefs () {
    if($( this ).val().length <= 6 && $( this ).val().length != 0) {
        return;
    }
    $.updateCookie('prefs', $(this).attr('cookie'), $( this ).val(), { expires: 90 });
    $('input[cookie="' + $(this).attr('cookie') + '"]').val($( this ).val());
    if($.cookie("prefs").email != userData.email) {
        $(".newsletter-form").removeClass("hidden");
    }
    if($.cookie("prefs").email == userData.email) {
        $(".newsletter-form").addClass("hidden");
    }
}

function searchMessage (message) {
    if (message == false) {
        $("#textbooks thead th").css("border-bottom-width", "1px");
        $("#search-message").addClass('hidden');
    }
    else {
        $("#search-message").html(message);
        $("#search-message").removeClass("hidden");
        $("#textbooks thead th").css("border-bottom-width", "0");
    }
}

function colorizeTextbooks () {
    $("#textbooks tr").removeClass("even odd");
    $("#textbooks tr:not(.hiddenRow):not(.soldUserItem)").filter(':even').addClass("even");
    $("#textbooks tr:not(.hiddenRow):not(.soldUserItem)").filter(':odd').addClass("odd");
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

function loggedIn () {
    $('#toggleLogout p').html("Logout");
}

function closeInfoBox (id) {
    var idInSelectedRows = selectedRows.indexOf(id + '');
    $('#info-box-' + id).remove();
    selectedRows.splice(idInSelectedRows, 1);
    var refreshedSelectedRows = [];
    for (i = 0; i < selectedRows.length; i++) {
        var infoBoxToModify = $('#info-box-' + selectedRows[i]);
        refreshedSelectedRows.push(selectedRows[i]);
        var rightPosition = (refreshedSelectedRows.length-1) * 320 + 10;
        infoBoxToModify.css('right', rightPosition);
    }
    $("[item=" + id + "]").removeClass('selected');
    if(selectedRows.length == 0) {
        $('body').removeClass("infoBoxOpen");
    }
}

function minimizeInfoBox (id) {
    var localInfoBox = $('#info-box-' + id);
    var localInfoBoxMinimize = $('#info-box-' + id + ' .info-box-minimize');
    localInfoBox.addClass('info-box-minimized');
    localInfoBoxMinimize.removeAttr('onclick');
    localInfoBoxMinimize.attr('onclick', 'expandInfoBox('+id+');');
    localInfoBoxMinimize.attr('title', 'Expand');
    $('#info-box-' + id + ' .info-box-minimize').css('margin-top', '-4px');
}

function expandInfoBox (id) {
    var localInfoBox = $('#info-box-' + id);
    var localInfoBoxMinimize = $('#info-box-' + id + ' .info-box-minimize');
    localInfoBox.removeClass('info-box-minimized');
    localInfoBoxMinimize.removeAttr('onclick');
    localInfoBoxMinimize.attr('onclick', 'minimizeInfoBox('+id+');');
    localInfoBoxMinimize.attr('title', 'Minimize');
    $('#info-box-' + id + ' .info-box-minimize').css('margin-top', '6px');
}

jQuery.fn.exists = function(){return this.length>0;}

Date.prototype.timeSince = function (sinceDate) {
    if(!(sinceDate instanceof Date)){
        sinceDate = new Date();
    }
    var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var secondsSince = Math.floor((sinceDate - this) / 1000);//Remove milliseconds
    var interval = Math.floor(secondsSince / (60*60*24*10));
    if (interval >= 1) {//Returns true if sinceDate is more than 10 days ago.
        if (this.getFullYear() != sinceDate.getFullYear()) {//If during a different year include the year in the return date.
            return monthNames[this.getMonth()] + ' ' + this.getDate() + ', ' + this.getFullYear();
        }
        return monthNames[this.getMonth()] + ' ' + this.getDate();
    }
    interval = Math.floor(secondsSince / (60*60*24));
    if (interval >= 1) {
        return (interval >= 2)?interval + " days ago":"1 day ago";
    }
    interval = Math.floor(secondsSince / (60*60));
    if (interval >= 1) {
        return (interval >= 2)?interval + " hours ago":"1 hour ago";
    }
    interval = Math.floor(secondsSince / 60);
    if (interval >= 1) {
        return (interval >= 2)?interval + " minutes ago":"1 minute ago";
    }
    return "Just now";//Less than one minute ago.
}

function dateTimeToObject (dateTime) {
    var t = dateTime.split(/[- :]/);
    var d = new Date(t[0], t[1] - 1, t[2], t[3], t[4], t[5]);
    return d;
}

$(function() {
    $('.spaLoad').click(function(e) {
        href = $(this).attr("href");
        pageChangeHandler(href);
        e.preventDefault();
    });

    // THIS EVENT MAKES SURE THAT THE BACK/FORWARD BUTTONS WORK AS WELL
    window.onpopstate = function(event) {
        pageChangeHandler(location.pathname);
    };

});

function pageChangeHandler (newPage){
    if($('#textbooks').is(":visible")){
        buyScroll = window.pageYOffset;
    }
    currentPage = window.location.pathname.replace(/\//g, '');
    if(newPage){
        currentPage = newPage.replace(/\//g, '');
    }
    if($('#server-messages').length && pageViews > 0 && errorCode < 500){
        $('#server-messages').remove();
        $('#houston').addClass('hidden');
        $('#welcome-text').removeClass('hidden');
    }
    disappear();
    if (currentPage == '') {
        currentPage = 'buy';
    }
    if(currentPage == 'buy') {
        $('#buy-page-text').removeClass("hidden");
        $('#welcome-text').removeClass("hidden");
        window.scrollTo(0, buyScroll);
        history.pushState('', '', '/');
    }
    else {
        history.pushState('', '', '/'+currentPage+'/');
        window.scrollTo(0, 0);
    }
    if(currentPage == 'legal'){
        loadAjax('legal');
    }
    else {
        loadNonAjax(currentPage);
    }
    if(currentPage == 'sell') {
        closeAlertBox();
    }
    $('title').text(pageTitles[currentPage]);
    if(shouldAutoselect){
        if (currentPage == 'sell'){
            $('#name').focus();
        }
        else if (currentPage == 'account' && !userData['loggedIn']){
            $('#login-email').focus();
        }
        else {
            $('#search-bar').focus();
        }
    }
    pageViews++;
}

function loadAjax(name) {
    $.ajax({
        url: '/' + name + '.html',
        success: function(result) {
            $('#extra-page').removeClass("hidden");
            $("#extra-page").html(result);
        }
    });
}

function loadNonAjax(name) {
    $('#' + name + '-text').removeClass("hidden");
    $('#' + name + 'Link').addClass('navBarCurrent');
}

function disappear() {
    $('#buy-page-text').addClass("hidden");
    $("#pages > div").addClass("hidden");
    $('.navBarCurrent').removeClass('navBarCurrent');
}

function sellItYourself() {
    var searchTerm = $('#search-bar').val();
    clearSearchBar();
    pageChangeHandler('sell');
    $('#title').val(searchTerm);
}

function clearSearchBar () {
    $('#search-bar').val('');
    $('#clear').addClass("hidden");
    if($rows.exists()){
        searchMessage(false);
    }
    else {
        searchMessage("There are currently no textbooks to display. Please come back later. ");
    }
    $('#search-bar').focus();
    $rows.removeClass("hiddenRow");
    colorizeTextbooks();
}

function miscMessage(message, priority) {
    document.getElementById("alert-box-area").innerHTML = '<div class="alert-message '+priority+'"> <div class="box-icon"></div> <p>'+message+'</p><span onclick="closeAlertBox();" class="close">&times;</span></div>';
    $('#content').addClass("alertBoxOpen");
}

function closeAlertBox(){
    document.getElementById("alert-box-area").innerHTML = '';
    $('#content').removeClass("alertBoxOpen");
}

function submitSellForm(){
    var submitButton = $('#sell-submit');
    submitButton.val('LISTING');
    submitButton.attr('disabled','disabled');
    var inputs = {//get the values submit
        comments : {
            category : 'text',
            fieldValue : $('#comments').val(),
            required : false
        },
        course : {
            category : 'course',
            fieldValue : $('#course').val(),
            required : true
        },
        price : {
            category : 'price',
            fieldValue : $('#price').val(),
            required : true
        },
        author : {
            category : 'text',
            fieldValue : $('#author').val(),
            required : true
        },
        title : {
            category : 'text',
            fieldValue : $('#title').val(),
            required : true
        },
        email : {
            category : 'text',
            fieldValue : $('#email').val(),
            required : true,
            makeCookie : true
        },
        name : {
            category : 'name',
            fieldValue : $('#name').val(),
            required : true,
            makeCookie : true
        },
        newsletter : {
            category : 'novalidate',
            fieldValue : $('#newsletter').prop('checked')?'subscribed':'unsubscribed'
        },
        request : {
            category : 'novalidate',
            fieldValue : 'submit-sell-form'
        },
        didCheck : {
            category : 'novalidate',
            fieldValue : $('#didcheck').val()
        }
    };
    if(validateInputs(inputs, 'sell-form', receivedSellFormResponse, sellFormMiscMessage) == false) {
        submitButton.removeAttr('disabled');
        submitButton.val('LIST TEXTBOOK');
        sellFormMiscMessage('Fix the errors shown and then submit again.');
    }
    return false;
}

function infoBoxSubmit(id){
    var submitButton = $('#info-box-'+id+'-submit');
    submitButton.val('SENDING');
    submitButton.attr('disabled','disabled');
    submittingInfoBoxId = id;
    var infoBoxInputs = {//get the values submit
        message : {
            category : 'text',
            fieldValue : $('#info-box-'+id+'-message').val(),
            required : true
        },
        email : {
            category : 'text',
            fieldValue : $('#info-box-'+id+'-email').val(),
            required : true,
            makeCookie :true
        },
        name : {
            category : 'name',
            fieldValue : $('#info-box-'+id+'-name').val(),
            required : true,
            makeCookie : true
        },
        newsletter : {
            category : 'novalidate',
            fieldValue : $('#info-box-'+id+'-newsletter').prop('checked')?'subscribed':'unsubscribed'
        },
        textbookid : {
            category : 'novalidate',
            fieldValue : $('#info-box-'+id+'-id').val()
        },
        request : {
            category : 'novalidate',
            fieldValue : 'contact-seller'
        },
        didCheck : {
            category : 'novalidate',
            fieldValue : document.getElementById('info-box-'+id+'-didcheck').value
        }
    };
    if(validateInputs(infoBoxInputs, 'info-box-form-' + id, receivedInfoBoxResponse, infoBoxMiscMessage) == false) {
        submitButton.removeAttr('disabled');
        submitButton.val('SEND MESSAGE');
        infoBoxMiscMessage('Fix the errors shown and then submit again.');
    }
    return false;
}

function submitLoginForm(){
    var submitButton = $('#submit-login');
    submitButton.val('SENDING');
    submitButton.attr('disabled','disabled');
    var loginInputs = {//get the values submit
        email : {
            category : 'text',
            fieldValue : $('#login-email').val(),
            required : true,
            makeCookie :true
        },
        request : {
            category : 'novalidate',
            fieldValue : 'login'
        }
    };
    if(validateInputs(loginInputs, 'login-form', receivedLoginFormResponse, loginFormMiscMessage) == false) {
        submitButton.removeAttr('disabled');
        submitButton.val('VERIFY');
        infoBoxMiscMessage('Fix the errors shown and then submit again.');
    }
    return false;
}

function toggleLogout (){
    if(userData.loggedIn){
        logout();
    }
    else {
        window.location.hash = "account";
    }
}

function logout(){
    var logoutInputs = {
        request : {
            category : 'novalidate',
            fieldValue : 'logout'
        }
    };
    if(validateInputs(logoutInputs, 'logout', receivedLogoutResponse, miscMessage) == false) {
        miscMessage('Logout failed', "error");
    }
    return false;
}

function submitUpdateForm(id){
    var submitButton = $('#info-box-submit-' + id);
    submitButton.val('SAVING');
    submitButton.attr('disabled','disabled');
    submittingInfoBoxId = id;
    var $checkbox = $("#info-box-status-" + id);
    var status = '';
    if ($checkbox.prop('checked')) {
        status = "sold";
    } else {
        status = "unsold";
    }
    var updateFormInputs = {//get the values submit
        status : {
            category : 'novalidate',
            fieldValue : status
        },
        comments : {
            category : 'text',
            fieldValue : $('#info-box-' + id + '-comments').val(),
            required : false
        },
        course : {
            category : 'course',
            fieldValue : $('#info-box-' + id + '-course').val(),
            required : true
        },
        price : {
            category : 'price',
            fieldValue : $('#info-box-' + id + '-price').val() + "",
            required : true
        },
        author : {
            category : 'text',
            fieldValue : $('#info-box-' + id + '-author').val(),
            required : true
        },
        title : {
            category : 'text',
            fieldValue : $('#info-box-' + id + '-title').val(),
            required : true
        },
        itemId : {
            category : 'novalidate',
            fieldValue : id + ''
        },
        request : {
            category : 'novalidate',
            fieldValue : 'update-item'
        }
    };
    if(validateInputs(updateFormInputs, 'info-box-form-' + id, receivedUpdateFormResponse, infoBoxMiscMessage) == false) {
        submitButton.removeAttr('disabled');
        submitButton.val('SAVE CHANGES');
        infoBoxMiscMessage('Fix the errors shown and then submit again.');
    }
    return false;
}

function validateInputs(inputs, formName, responseFunction, miscMessageFunction){//Accepts an object of objects and the id of the form being submited with no hashtag. All objects in the object must have a string property named fieldValue which will be sent to the server along with the name of the object in lowercase.
    var errors = new Array();//If something is added, the form will not submit.
    for(var input in inputs) {//Apply validation to each field before sending.
        var fieldValue = inputs[input].fieldValue.trim();
        if(inputs[input].category != 'novalidate') {
            message(formName, {inputName : input, message : ""}, false);//clear old error messages.
        }
        //Begin categories. Unvalidated catagory: novalidate
        switch(inputs[input].category) {
            case 'text':
                if(fieldValue.length < 3 && inputs[input].required == true){
                    errors.push({inputName : input, message : "Required"});
                }
                break;
            case 'name':
                if(!fieldValue.match(/^[\D]{2,} [\D]{2,}$/i) && inputs[input].required == true){
                    errors.push({inputName : input, message : "Required"});
                }
                break;
            case 'price':
                fieldValue = fieldValue.replace(/[^A-Z0-9\.]+/ig, '');
                if (fieldValue == '' && inputs[input].required == true) {
                    errors.push({inputName : input, message : "Required"});
                }
                if (fieldValue.match(/[\.]/)){
                    errors.push({inputName : input, message : "Stick to whole dollars."});
                }
                else if (fieldValue.match(/[^0-9]/)){
                    errors.push({inputName : input, message : "Numbers only"});
                }
                else if (fieldValue < 1 || fieldValue > 400){
                    errors.push({inputName : input, message : "Stay between $1 and $400"});
                }
                break;
            case 'course':
                fieldValue = fieldValue.replace(/[^A-Z0-9]+/ig, '');
                if(fieldValue == '' && inputs[input].required == true){
                    errors.push({inputName : input, message : "Required"});
                }
                else if(!fieldValue.match(/^[A-Z]{2,4}[0-9]{3}[A-Z]?$/i)){
                    errors.push({inputName : input, message : "Please format the course like the example."});
                }
                else {
                    var subjectCode = fieldValue.match(/^[A-Z]{2,4}/i);
                    subjectCode[0] = subjectCode[0].toUpperCase();
                    if(subjectCodes.indexOf(subjectCode[0]) < 0) {
                        errors.push({inputName : input, message : "That is not real subject code."});
                    }//else success
                }
                break;
        }
    }
    var hasErrors = false;
    for(var error in errors) {//if there are errors display the errors and set hasErrors true so the form will not submit.
        hasErrors = true;
        message(formName, errors[error], true);
    }
    if(hasErrors){//Check if inputs are valid...
        return false;//...and return false if they are not.
    }
    var dataToSend = '';//Will become the parameters of the submission.
    for(input in inputs) {
        dataToSend += input + '=' + inputs[input].fieldValue + '&';
    }
    dataToSend = dataToSend.substring(0, dataToSend.length - 1);
    if(sendForm(responseFunction, miscMessageFunction, '/index.php', dataToSend)){
        return true;//inputs are valid and successfully sent.
    }
    else{
        return false;//Error during sendForm
    }
}

function message(formName, error, shouldSelect) {
    $('#' + formName + ' .' + error.inputName + '-container .form-error label').html(error.message);
    if(shouldSelect) {
        $('#' + formName + ' .' + error.inputName + '-container input').select();
    }
}

function badContactInfoCookie(fieldName){
    $.updateCookie('prefs', fieldName, '', { expires: 90 });//might be bug. Should delete entire value.
    printPrefs();
}

function sellFormMiscMessage(message) {
    $('#sell-form-message-wrapper').removeClass("hidden");
    $('#sell-form-message-wrapper').html(message);
}

function receivedSellFormResponse(data) {
    var submitButton = $('#sell-submit');
    submitButton.removeAttr('disabled');
    submitButton.val('LIST TEXTBOOK');
    if(data.loggedIn == true){
        loggedIn();
    }
    if(data.misc == 'success'){
        document.location.href = '';
    }
    else{
        if(data.email){
            badContactInfoCookie('email');
            message('sell-form', {inputName : 'email', message : data.email}, true);
        }
        if(data.didCheck == true) {
            document.getElementById("didcheck").value = true;
        }
        if(data.misc){
            sellFormMiscMessage(data.misc);
        }
        else{
            sellFormMiscMessage('Fix the errors shown and then submit again.');
        }
        if(data.reload){
            document.location.href = '';
        }
    }
}

function loginFormMiscMessage(message) {
    $('#login-form-message-wrapper').removeClass("hidden");
    $('#login-form-message-wrapper').html(message);
}

function receivedLoginFormResponse(data) {
    var submitButton = $('#login-submit');
    submitButton.removeAttr('disabled');
    submitButton.val('VERIFY');
//    console.log(JSON.stringify(data, null, 2));
    if(data.email){
        $.updateCookie('prefs', 'name', data.name, { expires: 90 });
        $.updateCookie('prefs', 'email', data.email, { expires: 90 });
        printPrefs();
        window.location.hash ='#';
        miscMessage('We sent you an email with a link. Click it to continue. ', 'success');
    }
    if(data.misc){
        sellFormMiscMessage(data.misc);
    }
}

function receivedLogoutResponse(data) {
    if(data.misc == "success"){
        location.reload();
    }
    else{
        miscMessage("There was an error logging you out. Refresh the page and try again.", "error");
    }
}

function receivedSoldFormResponse (data) {
    if(data.misc != "success"){
        miscMessage("There was a problem updating the status of your item. Refresh the page and try again.", "error");
    }
    else {
        miscMessage("<i>" + data.item.title + "</i> was marked as " + data.item.status + ".", "success");
    }
}

function receivedUpdateFormResponse(data) {
    if(data.misc == 'success'){
        closeInfoBox(submittingInfoBoxId);
        $('[item='+data.item.id+'] .title').html(data.item.title);
        $('[item='+data.item.id+'] .author').html(data.item.author);
        $('[item='+data.item.id+'] .course').html(data.item.course);
        $('[item='+data.item.id+'] .price .val').html(data.item.price);
        $('[item='+data.item.id+'] .comments').html(data.item.comments);
        if(data.item.status == 'sold'){
            $('#owned-items [item='+data.item.id+']').addClass("sold");
            $('#textbooks [item='+data.item.id+']').addClass("soldUserItem");
            $('[item='+data.item.id+'] .status input').attr("checked", "checked");
        }
        else {
            $('[item='+data.item.id+']').removeClass("sold soldUserItem");
            $('[item='+data.item.id+'] .status input').removeAttr("checked");
        }
        miscMessage("Your changes were saved.", 'success');
    }
    else{
        infoBoxMiscMessage(data.misc);
    }
}

function infoBoxMiscMessage(message) {
    $('#info-box-'+submittingInfoBoxId+'-message-wrapper').removeClass("hidden");
    $('#info-box-'+submittingInfoBoxId+'-message-wrapper').html(message);
}

function receivedInfoBoxResponse(data) {
    if(data.newsletter == "subscribed") {
        userData.email = data.email;
        userData.newsletter = "subscribed";
        $.updateCookie('prefs', 'email', data.email, { expires: 90 });
        printPrefs();
        $("newsletter-form").addClass("hidden");
    }
    if(data.misc == 'success'){
        closeInfoBox(submittingInfoBoxId);
        miscMessage("Message sent. Check your email spam folder if the seller doesn't contact you within a day.", 'success');
    }
    else{
        if(data.misc){
            infoBoxMiscMessage(data.misc);
        }
        else {
            infoBoxMiscMessage('Fix the errors shown and then submit again.');
        }
        if(data.email){
            badContactInfoCookie('email');
            message('info-box-form-' + submittingInfoBoxId, {inputName : 'email', message : data.email}, true);
        }
        if(data.didCheck == true) {
            document.getElementById('info-box-'+id+'-didcheck').value = true;
        }
    }
    if(data.reload){
        document.location.href = '';
    }
}

window.onscroll = scroll;

function scroll () {
    if(window.pageYOffset > 44){
        $('#top-bar-background').addClass('bottomGrayBorder');
    }
    else {
        $('#top-bar-background').removeClass('bottomGrayBorder');
    }
}

(function() {

/*
 * Natural Sort algorithm for Javascript - Version 0.7 - Released under MIT license
 * Author: Jim Palmer (based on chunking idea from Dave Koelle)
 * Contributors: Mike Grier (mgrier.com), Clint Priest, Kyle Adams, guillermo
 * See: http://js-naturalsort.googlecode.com/svn/trunk/naturalSort.js
 */
function naturalSort (a, b) {
    var re = /(^-?[0-9]+(\.?[0-9]*)[df]?e?[0-9]?$|^0x[0-9a-f]+$|[0-9]+)/gi,
        sre = /(^[ ]*|[ ]*$)/g,
        dre = /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/,
        hre = /^0x[0-9a-f]+$/i,
        ore = /^0/,
        // convert all to strings and trim()
        x = a.toString().replace(sre, '') || '',
        y = b.toString().replace(sre, '') || '',
        // chunk/tokenize
        xN = x.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
        yN = y.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
        // numeric, hex or date detection
        xD = parseInt(x.match(hre), 10) || (xN.length !== 1 && x.match(dre) && Date.parse(x)),
        yD = parseInt(y.match(hre), 10) || xD && y.match(dre) && Date.parse(y) || null;

    // first try and sort Hex codes or Dates
    if (yD) {
        if ( xD < yD ) {
            return -1;
        }
        else if ( xD > yD ) {
            return 1;
        }
    }

    // natural sorting through split numeric strings and default strings
    for(var cLoc=0, numS=Math.max(xN.length, yN.length); cLoc < numS; cLoc++) {
        // find floats not starting with '0', string or 0 if not defined (Clint Priest)
        var oFxNcL = !(xN[cLoc] || '').match(ore) && parseFloat(xN[cLoc], 10) || xN[cLoc] || 0;
        var oFyNcL = !(yN[cLoc] || '').match(ore) && parseFloat(yN[cLoc], 10) || yN[cLoc] || 0;
        // handle numeric vs string comparison - number < string - (Kyle Adams)
        if (isNaN(oFxNcL) !== isNaN(oFyNcL)) {
            return (isNaN(oFxNcL)) ? 1 : -1;
        }
        // rely on string comparison if different types - i.e. '02' < 2 != '02' < '2'
        else if (typeof oFxNcL !== typeof oFyNcL) {
            oFxNcL += '';
            oFyNcL += '';
        }
        if (oFxNcL < oFyNcL) {
            return -1;
        }
        if (oFxNcL > oFyNcL) {
            return 1;
        }
    }
    return 0;
}

}());
