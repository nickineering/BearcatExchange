<?php
$startTime = date("Y-m-d H:i:s");
$startTimestamp = strtotime($startTime);
require __DIR__ . '/vendor/autoload.php'; //Composer autoload feature
$programingErrorMessage = "Someone made an mistake and it might have been us. But, hey, it could be you so try <a href='.'>refreshing the page</a>. If this message does not go away within a minute, please let us know by emailing us at <a href='mailto:support@bearcatexchange.com'>support@bearcatexchange.com</a>.";
$loggedIn = false;
$serverError = false;
$errorCode = (isset($_GET['e'])) ? $_GET["e"] : 0;
$errorCodes = array(
    400 => array("Error 400 Bad Request", $programingErrorMessage),
    401 => array("Error 401 Login Error", "Are you trying to steal Jimmy's password? At any rate, the password and user name you entered are not real. Try it agin. "),
    403 => array("Error 403 Forbidden", "Everyone has their secrets, ours just happen to be super secure. Try looking at your own stuff."),
    404 => array("Error 404 Not Found", "Seems that page skipped town. How about you find a different one."),
    405 => array("Error 405 Method Not Allowed", $programingErrorMessage),
    408 => array("Error 408 Request Timeout", "Slow and steady doesn't always win the race, you know. It seems your internet is going a little too slow today. Try refreshing to page or coming page later. "),
    414 => array("Error 414 URL To Long", "Someone made a mistake, you or us? Seems a URL (that long string of gibberish at the top of your screen) was too long. Try clicking the back arrow in the top left cornor of your screen, then refreshing the page and then trying to come here again. If you get this error again, let us know at <a href='mailto:support@bearcatexchange.com'>support@bearcatexchange.com</a>."),
    500 => array("Error 500 Internal Server Error", $programingErrorMessage),
    502 => array("Error 502 Bad Gateway", $programingErrorMessage),
    504 => array("Error 504 Gateway Timeout", $programingErrorMessage),
    508 => array("Error 508 Loop Detected", $programingErrorMessage),
    600 => array("Error 600 Server Execution Failure", $programingErrorMessage),
    601 => array("Error 601 Database Failure", $programingErrorMessage),
    602 => array("Error 602 Failed To Connect To Database", $programingErrorMessage),
    603 => array("Error 603 Failed to Load UTF8 Charsset in Database", $programingErrorMessage)
);
session_start();
$serverList = array('localhost', '127.0.0.1');
if(in_array($_SERVER['HTTP_HOST'], $serverList)) {
    $con = mysqli_connect("localhost", "main", "Gc4CXzCrz8RR8WCxxPuWjsCg", "bearcatexchange");
}
else {
    $con = mysqli_connect("bearcat.cqfnkzrzji1p.us-east-1.rds.amazonaws.com", "main", "Gc4CXzCrz8RR8WCxxPuWjsCg", "bearcatexchange", 3306);
}
if (!mysqli_set_charset($con, "utf8")) {
    $errorCode = 603;
}
if (!$con) {
    $errorCode = 602;
}
if($_REQUEST['isjson'] == true && $errorCode != 0){
    $errors['misc'] = "Sorry, please submit again or email us at <a href='mailto:support@bearcatexchange.com'>support@bearcatexchange.com</a>. " . $errorCodes[$errorCode][0];
    die(json_encode($errors));
}
else {
    if(isset($_COOKIE['user-session'])){
        if(ctype_alnum($_COOKIE['user-session'])) {
            $session = mysqli_query($con, "SELECT user, time, status FROM sessions WHERE hash = '".$_COOKIE['user-session']."'");
            if (mysqli_num_rows($session) != 0) {
                $session = mysqli_fetch_array($session);
                $diff = $startTimestamp - strtotime($session['time']);
                if ($diff < 60 * 60 * 24 * 45) {
                    if($session['status'] == 1){
                        $loggedIn = true;
                        $theUser = findUser($con, $session['user']);
                    }
                }
            }
        }
    }
    if (isset($_REQUEST['request']) || (isset($_GET['email']) && isset($_GET['h']))) {
        $errors['misc'] = '';
        $getAnotherLinkInstructions = "aybe you should use a newer link. Send yourself a message through your listing to get a link sent to your email now. ";
        $mail = new PHPMailer;
        //$mail->SMTPDebug = 3;                               // Enable verbose debug output
        $mail->isSMTP();
        $mail->Host = 'email-smtp.us-east-1.amazonaws.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'AKIAJV3FTVKKHHAHE4YQ';
        $mail->Password = 'ArnoeqtqrnOBa/q6fDICu+vYlwYHr3/bmCv6XnSMSvrP';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('support@bearcatexchange.com', 'Bearcat Exchange');
        $mail->isHTML(true);
        switch ($_REQUEST['request']) {
            case "submit-sell-form";
                submitTextbook();
                break;
            case "contact-seller";
                contactSeller();
                break;
            case "markedSold";
                markedSold();
                break;
            case "login";
                login();
                break;
            case "logout";
                logout();
                break;
            default;
                verifyLink();
                break;
        }
    }
}

function logout ($status = 2) {
    global $con;
    $result["misc"] = 'failed';
    if(isset($_COOKIE['user-session'])){
        if(ctype_alnum($_COOKIE['user-session'])) {
            $session = mysqli_query($con, "SELECT user, time, status FROM sessions WHERE status = 1 AND hash = '".$_COOKIE['user-session']."'");
            if (mysqli_num_rows($session) != 0) {
                $insert = "UPDATE `sessions` SET `status` = $status WHERE `hash` LIKE '".$_COOKIE['user-session']."'";
                mysqli_query($con, $insert);
                unset($_COOKIE['prefs']);
                $loggedIn = false;
                $result["misc"] = 'success';
                printMessage("You are now logged out");
            }
        }
    }
    mysqli_close($con);
    die(json_encode($result));
}

function submitTextbook() {
    global $con;
    global $errors;
    $name = ucwords(check_input($_POST['yourname'], "Enter your name.", "misc"));
    if (strlen($name) > 50) {
        $errors['misc'] .= "Abbreviate your name to 50 characters or less";
    }
    else if (strlen($name) < 3) {
        $errors['misc'] .= "What is your name? ";
    }
    $email = strtolower($_POST['email']);
    validateEmail($email);
    $title = ucfirst(check_input($_POST['textbooktitle'], "Enter the textbook's title.", "misc"));
    if (strlen($title) > 75) {
        $errors['misc'] .= "Abbreviate the textbook's title to 75 characters or less. ";
    }
    else if (strlen($title) < 3) {
        $errors['misc'] .= "What is the textbook's title? ";
    }
    $author = ucwords(check_input($_POST['author'], "Enter the author's name.", "misc"));
    if (strlen($author) > 50) {
        $errors['misc'] .= "Abbreviate the author's name to 50 characters or less";
    }
    else if (strlen($author) < 3) {
        $errors['misc'] .= "Who is the textbook's author? ";
    }
    $price = preg_replace('/[^0-9]+/i', '', $_POST['price']);
    if ($price === NULL || $price == false || intval($price) < 1 || intval($price) > 400) {
        $errors['misc'] .= 'Enter a price between $1 and $400. ';
    }
    $course = $_POST['course'];
    $course = preg_replace('/[^A-Z0-9]+/i', '', $course);
    if ($course != '') {
        if (preg_match("/^[A-Z]{2,4}[0-9]{3}[A-Z]?$/i", $course)) {
            $course = strtoupper($course);
            $course = preg_replace('/(?=[0-9]{3})/', ' ', $course);
        }
        else {
            $errors['misc'] = 'Format the course like the examples. ';
        }
    }
    else {
        $errors['misc'] .= 'Please enter the course the textbook is used in. ';
    }
    $comments = ucfirst(check_input($_POST['comments']));
    if (strlen($comments) > 1000) {
        $errors['misc'] .= 'Limit your comments to 1000 characters. ';
    }
    $newsletter = 'unsubscribed';
    if ($_POST['newsletter'] == 'subscribed') {
        $newsletter = 'subscribed';
    }
    $showMessage = false;
    foreach($errors as $error => $value) {
        if ($value != "" && $value != NULL) {
            $showMessage = true;
        }
    }
    if ($showMessage) {
        echo json_encode($errors);
        die();
    }
    $insert = "INSERT INTO `textbooks` (`title`, `user_id`, `author`, `price`, `course`, `comments`) VALUES ('$title', '".getUser($con, $email, $name, $newsletter). "', '$author', '$price', '$course', '$comments');";
    mysqli_query($con, $insert);
    mysqli_close($con);
    $result["misc"] = 'success';
    printMessage("$title was listed successfully!");
    die(json_encode($result));
}

function contactSeller() {
    global $con;
    global $errors;
    global $mail;
    $name = ucwords(check_input($_POST['yourname']));
    if (strlen($name) > 50) {
        $errors['misc'].= "Abbreviate your name to 50 characters or less";
    }
    else if (strlen($name) < 3) {
        $errors['misc'].= "What is your name? ";
    }
    $email = strtolower($_POST['email']);
    validateEmail($email);
    $message = check_input($_POST['message']);
    if (strlen($message) < 3) {
        $errors['misc'].= "What do you want to tell the seller? ";
    }
    $newsletter = 'unsubscribed';
    if ($_POST['newsletter'] == 'subscribed') {
        $newsletter = 'subscribed';
    }
    $textbookId = check_input($_POST['textbookid']);
    $showMessage = false;
    foreach($errors as $error => $value) {
        if ($value != "") {
            $showMessage = true;
        }
    }
    if ($showMessage) {
        echo json_encode($errors);
        die();
    }
    $insert = "INSERT INTO `messages` (`sender_id` , `textbook_id` , `message` , `time` , `ip_address` ) VALUES ('".getUser($con, $email, $name, $newsletter). "', '$textbookId', '$message', CURRENT_TIMESTAMP , '".get_ip()."' );";
    mysqli_query($con, $insert);
    $textbookListing = mysqli_fetch_array(mysqli_query($con, "SELECT id, user_id, title, price FROM textbooks WHERE id LIKE $textbookId"));
    $seller = mysqli_fetch_array(mysqli_query($con, "SELECT id, email, name FROM users WHERE id = ".intval($textbookListing['user_id'])));
    $bodyTitle = "A buyer sent you a message about your textbook <i>".$textbookListing['title']."</i>!";
    $removalLink = "http://bearcatexchange.com?email=".$seller['email']."&h=".createHash(intval($seller['id']), $textbookListing['id']);
    $bodyMessage = "<p>$message</p><p style='margin-bottom: 1em'>--$name</p><p>Don't forget, your asking price is <strong>$".$textbookListing['price']."</strong>. You can email this buyer directly through reply email or do nothing to remain anonymous. Once you sell your textbook, click the following link to mark it as sold and remove it from the website: <a href='$removalLink' style='color: #007a5e' >$removalLink</a> . You can also copy the link into your browser's address bar if you can not click it.</p > ";
    $mail->addAddress($seller['email'], $seller['name']);
    $mail->addReplyTo($email, $name);
    $mail->Subject = 'A buyer for your textbook '.$textbookListing['title'];
    $mail->Body = "<div style='font-family: sans-serif; line-height: 2em;'><h2 style='color: #007a5e'>$bodyTitle</h2><div style='font-size: 1.2em;'><div style='color:#000'>$bodyMessage</div><div style='color:gray'><p>Thank you for using <a href='http://bearcatexchange.com' style='color: #007a5e'>Bearcat Exchange</a>, the best way to buy and sell textbooks at Binghamton. If you experience technical difficulties, please contact us at <a href='mailto:support@bearcatexchange.com' style='color: #007a5e'>support@bearcatexchange.com</a>.</p></div></div></div>";
    if(!$mail->send()) {
        printMessage("Sorry, we had an internal error. Please try again. Email us at support@bearcatexchange.com if this message appears again.");
        //echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
    else {
        $result["misc"] = 'success';
    }
    mysqli_close($con);
    $result["misc"] = 'success';
    die(json_encode($result));
}

function login () {
    global $con;
    global $errors;
    global $mail;
    $email = strtolower($_POST['email']);
    validateEmail($email);
    $userData = findUser($con, $email);
    if($userData == false) {
        $result['misc'] .= "We couldn't find any items listed by that email. Did you spell it correctly?";
        mysqli_close($con);
        die(json_encode($result));
    }
    else {
        $bodyTitle = "Click the link below to edit your textbook listings!";
        $removalLink = "http://bearcatexchange.com?email=".$userData['email']."&h=".createHash(intval($userData['id']), -1);
        $bodyMessage = "<p><a href='$removalLink' style='color: #007a5e' >$removalLink</a> . You can also copy the link into your browser's address bar if you can not click it. If you did not request this email simply ignore it.</p > ";
        $mail->addAddress($userData['email'], $userData['name']);
        $mail->Subject = 'Edit your textbook listings';
        $mail->Body = "<div style='font-family: sans-serif; line-height: 2em;'><h2 style='color: #007a5e'>$bodyTitle</h2><div style='font-size: 1.2em;'><div style='color:#000'>$bodyMessage</div><div style='color:gray'><p>Thank you for using <a href='http://bearcatexchange.com' style='color: #007a5e'>Bearcat Exchange</a>, the best way to buy and sell textbooks at Binghamton. If you experience technical difficulties, please contact us at <a href='mailto:support@bearcatexchange.com' style='color: #007a5e'>support@bearcatexchange.com</a>.</p></div></div></div>";
        if(!$mail->send()) {
            printMessage("Sorry, we had an internal error. Please try again. Email us at support@bearcatexchange.com if this message appears again.");
        }
        else {
            $result["misc"] = 'success';
        }
        mysqli_close($con);
        die(json_encode($userData));
    }
}

function markedSold () {
    global $con;
    global $loggedIn;
    global $theUser;
    if($loggedIn) {
        $itemId = intval($_POST['itemId']);
        $insert = "SELECT `user_id` FROM `textbooks` WHERE `id` = ".$itemId;
        if (intval(mysqli_query($con, $insert)) == $theUser['id']){
            if($_POST['status'] == "sold") {
                $status = "sold";
            }
            if($_POST['status'] == "unsold") {
                $status = "unsold";
            }

            $insert = "UPDATE `textbooks` SET `status` = '$status'".(($status == "sold")?", `sale_time` = CURRENT_TIMESTAMP":'')." WHERE `id` = ".$itemId;
            mysqli_query($con, $insert);
            $result["misc"] = "success";
        }
        else {
            $result["misc"] = 'Wrong user';
        }
    }
    else {
        $result["misc"] = 'Not logged in';
    }
    mysqli_close($con);
    die(json_encode($result));
}

function verifyLink() {
    global $getAnotherLinkInstructions;
    global $con;
    $email = check_input($_GET['email']);
    $hash = check_input($_GET['h']);
    $user = mysqli_query($con, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($user) == 0) {
        printMessage('Hmmmm... That is funny. The email in your link is not registered with any textbooks. M'.$getAnotherLinkInstructions, 'warning');
    }
    $user = mysqli_fetch_array($user);
    checkHash($con, onValidate, $user['id'], $hash);
}

function onValidate($localCon, $user, $textbookId, $textbookTitle) {
    mysqli_query($localCon, 'UPDATE textbooks SET status = "sold" WHERE `user_id` = '.$user.' AND `id` = '.$textbookId);
    printMessage('Your textbook <i>'.$textbookTitle.'</i> was marked as sold. Congratulations!');
}

function getUser($localCon, $localEmail, $localName, $localNewsletter) {
    $findUserResult = findUser($localCon, $localEmail, $localNewsletter);
    if($findUserResult == false) {
        $userInsert = "INSERT INTO `users` (`email`, `name`, `joined`, `newsletter`) VALUES ('$localEmail', '$localName', CURRENT_TIMESTAMP, '$localNewsletter');";
        mysqli_query($localCon, $userInsert);
        $userId = mysqli_insert_id($localCon);
    }
    else {
        return $findUserResult['id'];
    }
}

function findUser($localCon, $identifier, $localNewsletter) {
    if(is_numeric($identifier)) {
        $userData = mysqli_query($localCon, "SELECT id, name, email, newsletter FROM users WHERE id = '$identifier'");
    }
    else {
        $userData = mysqli_query($localCon, "SELECT id, name, email, newsletter FROM users WHERE email = '$identifier'");
    }
    if (mysqli_num_rows($userData) != 0) {
        $userData = mysqli_fetch_array($userData);
        if ($userData['newsletter'] == 'unsubscribed' && $localNewsletter == 'subscribed') {
            mysqli_query($localCon, "UPDATE users SET newsletter = '$localNewsletter' WHERE email = '".$userData['email']."'");
        }
        return $userData;
    }
    else {
        return false;
    }
}

function get_ip() {
    //Just get the headers if we can or else use the SERVER global
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    }
    else {
        $headers = $_SERVER;
    }
    //Get the forwarded IP if it exists
    if (array_key_exists('X-Forwarded-For', $headers) && filter_var($headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $the_ip = $headers['X-Forwarded-For'];
    }
    elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $headers) && filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $the_ip = $headers['HTTP_X_FORWARDED_FOR'];
    }
    else {
        $the_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }
    return $the_ip;
}

function validateEmail($emailToValidate) {
    global $errors;
    require_once 'scripts/validate-email/is_email.php';
    require_once 'scripts/validate-email/meta.php';
    $emailresult = is_email($emailToValidate, true, true);
    $analysis = is_email_analysis($emailresult, ISEMAIL_META_ALL);
    if ($emailresult === ISEMAIL_VALID) {
        $errors['email'] = '';
    }
    else if ($emailresult < ISEMAIL_THRESHOLD) {
        if ($_POST['didcheck'] != "true") {
            $errors['email'] = "Are you sure that's your email? Click submit again to confirm.";
            $errors['didCheck'] = true;
        }
    }
    else {
        $errors['email'] = "That's not a valid e-mail address.";
    }
}

function check_input($data, $errorMessage = '', $error = '') {
    global $errors;
    global $con;
    $data = strip_tags($data);
    $data = trim($data);
    $data = mysqli_real_escape_string($con, $data);
    if ($errorMessage && strlen($data) == 0) {
        $errors[$error].= ' ' . $errorMessage . ' ';
    }
    return $data;
}

function randomCharacter($num) {
    // accepts 1 - 36
    switch($num) {
        case "1": $rand_value = "a"; break;
        case "2": $rand_value = "b"; break;
        case "3": $rand_value = "c"; break;
        case "4": $rand_value = "d"; break;
        case "5": $rand_value = "e"; break;
        case "6": $rand_value = "f"; break;
        case "7": $rand_value = "g"; break;
        case "8": $rand_value = "h"; break;
        case "9": $rand_value = "i"; break;
        case "10": $rand_value = "j"; break;
        case "11": $rand_value = "k"; break;
        case "12": $rand_value = "l"; break;
        case "13": $rand_value = "m"; break;
        case "14": $rand_value = "n"; break;
        case "15": $rand_value = "o"; break;
        case "16": $rand_value = "p"; break;
        case "17": $rand_value = "q"; break;
        case "18": $rand_value = "r"; break;
        case "19": $rand_value = "s"; break;
        case "20": $rand_value = "t"; break;
        case "21": $rand_value = "u"; break;
        case "22": $rand_value = "v"; break;
        case "23": $rand_value = "w"; break;
        case "24": $rand_value = "x"; break;
        case "25": $rand_value = "y"; break;
        case "26": $rand_value = "z"; break;
        case "27": $rand_value = "0"; break;
        case "28": $rand_value = "1"; break;
        case "29": $rand_value = "2"; break;
        case "30": $rand_value = "3"; break;
        case "31": $rand_value = "4"; break;
        case "32": $rand_value = "5"; break;
        case "33": $rand_value = "6"; break;
        case "34": $rand_value = "7"; break;
        case "35": $rand_value = "8"; break;
        case "36": $rand_value = "9"; break;
    }
    return $rand_value;
}

function get_rand_alphanumeric($length) {
    if ($length > 0) {
        $rand_id = "";
        for ($i = 1; $i <= $length; $i++) {
            mt_srand((double) microtime() * 1000000);
            $num = mt_rand(1, 36);
            $rand_id.= randomCharacter($num);
        }
    }
    return $rand_id;
}

function get_rand_numbers($length) {
    if ($length > 0) {
        $rand_id = "";
        for ($i = 1; $i <= $length; $i++) {
            mt_srand((double) microtime() * 1000000);
            $num = mt_rand(27, 36);
            $rand_id.= randomCharacter($num);
        }
    }
    return $rand_id;
}

function get_rand_letters($length) {
    if ($length > 0) {
        $rand_id = "";
        for ($i = 1; $i <= $length; $i++) {
            mt_srand((double) microtime() * 1000000);
            $num = mt_rand(1, 26);
            $rand_id.= randomCharacter($num);
        }
    }
    return $rand_id;
}

function createHash($userid, $textbook) {
    global $con;
    $hash = $userid.get_rand_alphanumeric(20);
    $hashInsert = "INSERT INTO  `hashes` (`user` ,  `hash`,  `textbook` ) VALUES ('$userid',  '$hash',  '$textbook')";
    mysqli_query($con, $hashInsert);
    return $hash;
}

function checkHash($localCon, $complete, $user, $hash) {
    global $con;
    global $getAnotherLinkInstructions;
    global $theUser;
    global $loggedIn;
    global $startTimestamp;
    $hashSearch = "SELECT * FROM `hashes` WHERE `hash` =  '$hash'";
    $hashArray = mysqli_query($localCon, $hashSearch);
    if (mysqli_num_rows($hashArray) == 1) {
        $hashArray = mysqli_fetch_array($hashArray);
        $textbookId = intval($hashArray['textbook']);
        if ($textbookId == -1) {
            if ($loggedIn == true){
                logout(3);
            }
            printMessage("You are now logged in");
            $theUser = findUser($con, $hashArray['user']);
            $theUser['loggedIn'] = true;
            $loggedIn = true;
            $theUser['session'] = $theUser['id'].get_rand_alphanumeric(20);
            setcookie("user-session", $theUser['session'], $startTimestamp + (60*60*24*45), "/");
            mysqli_query($localCon, "INSERT INTO `sessions` (`user`, `status`, `hash`, `ip_address`) VALUES (".$theUser['id'].", 1, '".$theUser['session']."', '".get_ip()."')");
        }
        else {
            $textbook = mysqli_query($localCon, 'SELECT title, status FROM `textbooks` WHERE `user_id` = '.$user. ' AND `id` = '.$textbookId);
            if (mysqli_num_rows($textbook) == 1) {
                $textbook = mysqli_fetch_array($textbook);
                if ($textbook['status'] == 'sold') {
                    printMessage('You already removed '.$textbook['title']. ', but now it is really gone. Thanks for using Bearcat Exchange. ');
                }
                else {
                    $diff = $startTimestamp - strtotime($hashArray['time']);
                    if ($diff < 60 * 60 * 24 * 30) {
                        $complete($localCon, $user, $textbookId, $textbook['title']);
                    }
                    else {
                        printMessage('That link was from ancient history. M'.$getAnotherLinkInstructions, 'warning');
                    }
                }
            }
            else {
                printMessage('Hmmmm... That is funny. Your link has something wrong with it. M'.$getAnotherLinkInstructions, 'warning');
            }
        }
    }
    else {
        printMessage('Your link did not work. Are you sure you copied it correctly from your email? If not, m'.$getAnotherLinkInstructions, 'warning');
    }
}

function printMessage($status, $priority = "info") {
    $_SESSION['status'] = $status;
    $_SESSION['priority'] = $priority;
}

function generateErrorText($localErrorCode, $makeDiv) {
    global $errorCodes;
    if($localErrorCode != 0){
        $resultMessage = ($makeDiv ? "<div id='server-messages'><p>" : "");
        $resultMessage .= (isset($errorCodes[$localErrorCode][1]) ? $errorCodes[$localErrorCode][1] : $programingErrorMessage) . ' ';
        $resultMessage .= (isset($errorCodes[$localErrorCode][0]) ? $errorCodes[$localErrorCode][0] : ('Error ' . $localErrorCode ));
        $resultMessage .= ($makeDiv ? '.</p></div>' : "");
        return $resultMessage;
    }
    else{
        return false;
    }
}

function timeSince ($sinceDate) {
    global $startTimestamp;
    $sinceTimestamp = strtotime($sinceDate);
    $secondsSince = $startTimestamp - $sinceTimestamp;
    $interval = floor($secondsSince / 60*60*24*10);
    if ($interval >= 1) {//Returns true if sinceDate is more than 10 days ago.
        if(date("Y") != date("Y", $sinceTimestamp)){//If during a different year include the year in the return date.
            return date("F j, Y", $sinceTimestamp);
        }
        else {
            return date("F j", $sinceTimestamp);
        }
    }
    $interval = floor($secondsSince / 60*60*24);
    if ($interval >= 1) {
        return ($interval >= 2)?$interval . " days ago":"1 day ago";
    }
    $interval = floor($secondsSince / 60*60);
    if ($interval >= 1) {
        return ($interval >= 2)?$interval . " hours ago":"1 hour ago";
    }
    $interval = floor($secondsSince / 60);
    if ($interval >= 1) {
        return ($interval >= 2)?$interval . " minutes ago":"1 minute ago";
    }
    return "Just now";//Less than one minute ago.
}

?>

<!DOCTYPE html>
<html lang="en" id='open-html'>
    <head>
        <title>Bearcat Exchange - Buy and sell textbooks at Binghamton</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="Avoid bookstore prices: Buy and sell textbooks faster and easier with our free website, created by and for Binghamton University students.">
        <meta name="viewport" content="initial-scale=1, width=device-width">
        <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,600,700' rel='stylesheet' type='text/css'>
<!--        <link href="scripts/normalize.css" rel="stylesheet" type="text/css"/>-->
        <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/themes/smoothness/jquery-ui.css" />
        <link href="style.css" rel="stylesheet" type="text/css"/>
        <link rel="icon" type="image/ico" href="favicon.ico"/>
        <meta property="og:image" content="http://bearcatexchange.com/images/facebook-logo.jpg" />
    </head>
    <body onload='<?php
            if(isset($_SESSION['status'])){
                echo "miscMessage(".'"'. $_SESSION['status'].'"'. ", ".'"'.$_SESSION['priority'].'"'.");";
                unset($_SESSION['status']);
            }
            echo "setJavaScriptData($errorCode, " . json_encode($theUser) . ");";
        ?>'>
        <script src="send-form.js" defer></script>
        <script src="scripts/modernizr.min.js" defer></script>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" defer></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js" defer></script>
        <script src="scripts/jquery.cookie.js" defer></script>
<!--        <script src="scripts/mustache.min.js" defer></script>-->
        <script src="//cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js" defer></script>
        <script src="index.js" defer></script>
        <div id='wrapper'>
            <!--Begin Nav Bar-->
            <div id="top-bar-background"></div>
            <a href='#'><img src="images/logo.svg" width="250" height="83" id="logo" alt="Bearcat Exchange"></a>
            <nav id="nav">
                <div id="nav-wrap">
                <a href='#'>
                    <div class="navbar-link" id="buyLink">
                        <h5>BUY<span class="mobile-hidden"> A TEXTBOOK</span></h5>
                    </div>
                </a>

                <a  href="#sell">
                    <div class="navbar-link" id="sellLink">
                        <h5>SELL<span class="mobile-hidden"> A TEXTBOOK</span></h5>
                    </div>
                </a>

                <a href="#account">
                    <div class="navbar-link" id="accountLink">
                        <h5>EDIT<span class="mobile-hidden"> YOUR LISTINGS</span></h5>
                    </div>
                </a>

                <a href="#faq">
                    <div class="navbar-link" id="faqLink">
                        <h5><span class="mobile-hidden">COMMON </span> QUESTIONS</h5>
                    </div>
                </a>

                <a href="#feedback">
                    <div class="navbar-link" id="feedbackLink">
                        <h5><span class="mobile-hidden">SEND US </span>FEEDBACK</h5>
                    </div>
                </a>
                </div>
            </nav>
            <!--End Nav Bar-->
            <!--Popup Information Window-->
            <div id='info-box-area'></div>
            <script type="text/x-handlebars-template" id='info-box'>
                <?php include 'info-box.html'; ?>
            </script>
            <script type="text/x-handlebars-template" id='info-box-edit'>
                <?php include 'info-box-edit.html'; ?>
            </script>
            <!--End Popup Information Window-->
            <!--Login Button-->
            <span id="clear" onclick='clearSearchBar();' ><img src="images/clear.svg"></span>
            <a class="top-right-button" id='facebook-link' href="https://facebook.com/bearcatexchange" target='_blank'><p>f</p></a>
            <a class="top-right-button" id='google-plus-link' href="https://plus.google.com/104887107850990243147" rel="publisher" target='_blank'><p>g+</p></a>
            <a class="top-right-button" id='logout' onclick="logout();" target='_blank'><p>Logout</p></a>
            <!--Begin page content area-->
            <input type="search" name="search" id="search-bar" aria-controls="textbooks" placeholder=" Search" autocorrect="off">
            <div id="content" class="content">
                <div id='buy-page-text'>
                    <div id='welcome-text' style='display : none'>
                        <h1>Savings Ahoy!</h1>
                        <h2>Welcome to the new best place to buy and sell textbooks at Binghamton.</h2>
                        <p>It's completely free, made by and for Binghamton students. You can buy and sell textbooks online with people in Binghamton, not Seattle, without giving the bookstore a cut. Click a textbook and start saving now.</p>
                    </div>
                    <h1 id='houston'>Houston, we have a problem</h1>
                    <?php
                        echo generateErrorText($errorCode, true);
                    ?><div id='noscript-warning' class='content'>
                        <p>Your browser is not running Javascript. Use <a target='_blank' href='https://www.mozilla.org/en-US/firefox/new/'>a different browser</a> or <a href="http://enable-javascript.com" target="_blank">enable JavaScript</a> in your browser's settings. Doing that is acutally pretty easy, just click the link. Once you are done, reload this page by pressing Ctrl (Windows) or Command (Mac) + R.</p>
                        <h3>But why do I need JavaScript?</h3>
                        <p>Javascript is the universal way websites modify their content. Without it, Bearcat Exchange can't do nessasary tasks like get the list of textbooks to display.</p>
                        <h3>If I enable JavaScript aren't you going to steal my identity or takeover my computer?</h3>
                        <p>No, even if we wanted to, Javascript is sandboxed. In English that means that it can only access elements of its own webpage, not your computer or other open tabs. The only way it can hurt you is if you give it sensitive information like your social security number. I mean, really, how stupid is that?</p>
                        <h3>What about my email? Can't JavaScript steal that?</h3>
                        <p>Same as your social security number websites cannot get your email unless you provide it. While Bearcat Exchange does offer you the option to provide your email, you do not have to provide it and JavaScript alone can not steal it. If you do choose to provide us with it, we will never send you spam, only updates about your listings or other things you opt-in to.</p>
                    </div>
                    <script>
                        document.getElementById('noscript-warning').style.display = 'none';
                        if(!document.getElementById('server-messages')){
                            document.getElementById('welcome-text').style.display = 'inline';
                            document.getElementById('houston').style.display = 'none';
                        }
                    </script>
                </div>
                <div id="pages">
                    <div id='buy-text'>
                        <table id="textbooks" class="items" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th class="textbookHeader" title='Alphabetize textbooks'>Textbook</th>
                                    <th class="authorHeader" title="Alphabetize by author">Author</th>
                                    <th class="courseHeader" title="Sort by course">Course</th>
                                    <th class="priceHeader" title="Lowest price first">Price</th>
                                    <th class="timePostedHeader" title="Most recent first">Time Posted</th>
                                    <th class="commentsHeader"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = mysqli_query($con, "SELECT `id` , `title`,  `author` ,  `price` ,  `time`,  `course` ,  `comments` FROM `textbooks` WHERE status = 'unsold' ORDER BY `id` DESC");
                                if (mysqli_num_rows($result) > 0) {
                                    $numOfRows = mysqli_num_rows($result);
                                    $even = false;
                                    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                ?>

                                <tr item="<?php echo $row['id']; ?>" class="<?php echo ($even)?'even':'odd'; ?>">
                                    <td class="title"><?php echo $row['title']; ?></td>
                                    <td class="author"><?php echo $row["author"]; ?></td>
                                    <td class="course"><?php echo $row["course"]; ?></td>
                                    <td class="price">$<?php echo $row["price"]; ?></td>
                                    <td class="time" timestamp='<?php echo $row["time"]; ?>'><?php echo timeSince($row["time"]); ?></td>
                                    <td class="comments"><?php echo $row["comments"]; ?></td>
                                </tr>
                            <?php
                                        $even = !$even;
                                    }
                                }
                                ?></tbody>
                        </table>
                        <div class="odd" id="search-message">There are currently no textbooks to display. Please come back later.</div>
                    </div>
                    <div id='extra-page'></div>
                    <div id='sell-text'><?php include 'sell-text.html'; ?></div>
                    <div id='account-text'>
                        <?php if(!$loggedIn) { ?>
                        <form id="login-form" class="page-form" name="login" method="POST" action="index.php" onsubmit="return submitLoginForm()">
                            <h1>Edit Your Listings</h1><h2>Edit your listings or mark them as sold</h2>
                            <div><div id="login-noscript-warning" class='form-message-wrapper form-noscript-warning' style='display:block;'>We are currently experiencing technical difficulties and may not be able to list your item. Try <a href=".">reloading this page</a> or check back later.</div></div>
                            <script>
                                document.getElementById('login-noscript-warning').style.display = 'none';
                            </script>
                            <div class="login-form-input">
                                <div class='email-container'>
                                    <p class="input-text"><label for="login-email">Enter the same email you listed your item with to get started <span class="required">*</span></label></p>
                                    <p class="form-error"><label for="login-email"  id='login-email-message'></label></p>
                                    <input type="email" name="email" id='login-email' maxlength="254" cookie='email'>
                                    <br><br>
                                </div>
                                <div id="login-form-message" class="page-form-message"><div id="login-form-message-wrapper" class='form-message-wrapper'></div></div>
                                <br>
                                <input type="hidden" name="request" id="requestId" value="login"/>
                                <input id='login-submit' type="submit" value="VERIFY">
                            </div>
                        </form>
                        <?php } else { ?>
                        <form id="account-form" name="account" method="POST" action="index.php" onsubmit="return submitAccountForm()">
                            <h1>Edit Your Listings</h1><h2>These are all of the items you listed with <?php echo $theUser['email'] . ", " .  $theUser['name'] . ". "; ?></h2>
                            <div><div id="account-noscript-warning" class='form-message-wrapper form-noscript-warning' style='display:block;'>We are currently experiencing technical difficulties and may not be able to list your item. Try <a href=".">reloading this page</a> or check back later.</div></div>
                            <script>
                                document.getElementById('account-noscript-warning').style.display = 'none';
                            </script>
                            <table id="owned-items" class="items" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="statusHeader" title='Mark as sold'>Sold</th>
                                        <th class="textbookHeader" title='Alphabetize textbooks'>Textbook</th>
                                        <th class="authorHeader" title="Alphabetize by author">Author</th>
                                        <th class="courseHeader" title="Sort by course">Course</th>
                                        <th class="priceHeader" title="Lowest price first">Price</th>
                                        <th class="timePostedHeader" title="Most recent first">Time Posted</th>
                                        <th class="commentsHeader never"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                      $result = mysqli_query($con, "SELECT `id` , `title`,  `author` ,  `price` ,  `time`,  `course` ,  `comments`,  `status` FROM `textbooks` WHERE  `id` > 0 AND user_id = ".$theUser['id']." ORDER BY `id` DESC");
                                      if (mysqli_num_rows($result) > 0) {
                                          $numOfRows = mysqli_num_rows($result);
                                          $even = false;
                                          while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                                    ?><tr item="<?php echo $row['id']; ?>" class="<?php echo (($even)?'even':'odd') . ' ' . (($row["status"] == "sold")?'sold':''); ?>">
                                        <td class="status"><input type="checkbox" name="status" value='sold' <?php if($row["status"] == "sold") echo "checked"; ?>><div></div></td>
                                        <td class="title"><?php echo $row['title']; ?></td>
                                        <td class="author"><?php echo $row["author"]; ?></td>
                                        <td class="course"><?php echo $row["course"]; ?></td>
                                        <td class="price">$<?php echo $row["price"]; ?></td>
                                        <td class="time" timestamp='<?php echo $row["time"]; ?>'><?php echo timeSince($row["time"]); ?></td>
                                        <td class="comments"><?php echo $row["comments"]; ?></td>
                                    </tr><?php
                                              $even = !$even;
                                          }
                                      }
                                    ?>
                                </tbody>
                            </table>
                            <br>
                            <input type="hidden" name="request" id="requestId" value="account"/>
                        </form>
                        <?php } ?>
                    </div>
                    <div id='faq-text'><?php include 'faq-text.html'; ?></div>
                </div>
            </div>
            <!--End page content area-->
            <!--Begin Copyright-->
            <footer id="other-nav">
                <p id="copyright">&#169; 2016</p>
                <p>Nicholas Ferrara &amp; Rohit Kapur</p><p>Independent Student Website</p>
                <hr>
                <p id='legal-link'><a href='#legal'>Terms and Privacy</a></p>
            </footer>
            <!-- End Copyright-->
            <div id='alert-box-area'>
            <!--[if lt IE 11]><div class="alert-message info"> <div class="box-icon"></div> <p>Wow, <strong>your browser is from ancient history!</strong> Lucky for you, though, you don't have to be a caveman forever. You can always <strong><a href="http://browsehappy.com/?locale=en" target="_blank">upgrade to a modern browser</a></strong> to improve your experience. And really, it makes it easier to program the site, so do your friendly developers a favor here.</p><span onclick="closeAlertBox();" class="close">&times;</span></div><![endif]-->
            </div>
<!--
            <div class="alert-message success"> <div class="box-icon"></div> <p>Success</p><a href="" class="close">&times;</a></div>
            <div class="alert-message warning"> <div class="box-icon"></div> <p>Warning</p><a href="" class="close">&times;</a></div>
            <div class="alert-message error"> <div class="box-icon"></div> <p>Alert</p><a href="" class="close">&times;</a></div>
-->
        </div>
    </body>
</html>
<?php mysqli_close($con); ?>
