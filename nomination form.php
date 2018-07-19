<?php
include_once("custom/custom_functions.php");

define(HASH_KEY, "EB#XLkm6D5N5");
global $nommineeA, $awardsArr, $awardsLast, $required, $error, $testExclude;
//variables to find rpc server and location of API
//$site = "request.example.org.uk";
//$location = "/web_services/server.php";

$testExclude = $test;
?>
<style type='text/css'> 
    .errorField {
        border-right: 4px solid #ba0c2f;
    }
    .wordsLeft, .highlighted {
        color: #ba0c2f;
    }
    h2.sectionTitle1 {
        font-size: 30px;
    }
</style>

<?php
if (!checkAwardcode($award)) {
    echo "<p> Form error: unknown award. </p>";
    return;
}

$awardsArr = getAwardDetails($award);

$req_text = "Required";
//if ($_REQUEST['award'] == "pdfreport") {
if ($_REQUEST['award'] == "pdfreport" || $_REQUEST['award'] == "pdfreportunlisted") {
    $required[urlReport] = $req_text;
} elseif ($_REQUEST['award'] == "service_provider") {
    
} else {
    $required[nominee_name] = $req_text;
    $required[nominee_job_title] = $req_text;
    $required[nominee_organisation] = $req_text;

    $required[nominee_phone] = $req_text;
    $required[nominee_email] = $req_text;
}

$required[nominator_name] = $req_text;
$required[nominator_job_title] = $req_text;
$required[nominator_organisation] = $req_text;

$required[nominator_phone] = $req_text;
$required[nominator_email] = $req_text;

$required[checkbox1] = $req_text;
$required[checkbox2] = $req_text;

if ($_REQUEST['award'] == "pdfreport" || $_REQUEST['award'] == "pdfreportunlisted") {

    $textFields = array(
        'nominator_name',
        'nominator_job_title',
        'nominator_organisation',
        'nominator_phone',
        'hear_about'
    );
    $email_fields = array(
        'nominator_email'
    );
} else {
    $textFields = array(
        'nominee_name',
        'nominee_job_title',
        'nominee_organisation',
        'nominee_phone',
        'nominator_name',
        'nominator_job_title',
        'nominator_organisation',
        'nominator_phone',
        'hear_about'
    );

    $email_fields = array(
        'nominee_email'
    );
}


//Sanatize fields
if ($_REQUEST) {
    foreach ($_REQUEST as $key => $value) {
        if (!is_array($value)) {
            $_REQUEST[$key] = filter_var($value, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        }
    }
}

foreach ($textFields as $key => $val) {
    if (is_array($val)) {
        foreach ($val as $subkey) {
            $_REQUEST[$key][$subkey] = filter_var($_REQUEST[$key][$subkey], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
            //echo "\$_REQUEST[$key][$subkey]" . $_REQUEST[$key][$subkey] . "<br/>";
        }
    } else {
        $_REQUEST[$val] = filter_var($_REQUEST[$val], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        //print "\$_REQUEST[$val]: " . $_REQUEST[$val] . " <br>";
    }
}
foreach ($email_fields as $val) {
    $_REQUEST[$val] = filter_var($_REQUEST[$val], FILTER_SANITIZE_EMAIL);
//  print "\$_REQUEST[$val]: " . $_REQUEST[$val] . " <br>";
}


if (1 == 2 && isset($_REQUEST['sendNomination'])) {
    if ($_SERVER[REMOTE_ADDR] == '12.345.6.789') {
        echo "var_dump(\$_REQUEST);";
        var_dump($_REQUEST);
        var_dump($_FILES);
//die();
    }
}

if (isset($_REQUEST['uploadNomination'])) {
    $errorFlag = false;
    $referenceNo = "#" . time();
    if (trim($_FILES['fFile']['name']) <> "") {
        list($errorFlag, $eMessage, $fileName, $path_of_uploaded_file) = getFile(1);
        $error[] = "<p><span class='important'>$eMessage</span></p>";
    } else {
        $errorFlag = true;
        $error[] = "<div class=\"panel background-black\"><h3><a href=\"#anchor\">Please select a file to upload and complete your nomination</a></h3></div>";
    }
    if ($errorFlag) {
        $finished = false;
    } else {
        $body .= "<b>Please find enclosed a nomination for the example awards ceremony.</b><br><p>Reference number: $referenceNo</p>";
        $body .= "</table></body></html>";


        if (trim($_FILES['fFile']['name']) <> "") {
            $from = "auto@example.org.uk";
            $to = "admin@example.org.uk";
            $subject = "Submitted: $awardsArr[desc]nomination" . $_REQUEST[nominator_email];
            sendEmail($from, $to, $subject, $body, $fileName, $path_of_uploaded_file);

            $to = "nominations@example.org.uk";
            sendEmail($from, $to, $subject, $body, $fileName, $path_of_uploaded_file);
        } else {
            $from = "award_nomination@example.org.uk";
            $to = "nominations@example.org.uk";
            $subject = "Submitted: $awardsArr[desc]nomination " . $_REQUEST[nominator_email];
            send_mail($from, $to, $subject, 1, $body);
        }

        $finished = true;
    }
}


if (isset($_REQUEST['sendNomination'])) {
    //echo "inside sendNomination <br/>";
    $class = array();
    $totalAchievements = 0;
    foreach ($required as $key => $val) {
        //print "$key == $val <br>";
        if (is_array($val)) {
            
        } else {
            if (ts($_REQUEST[$key])) {
                if (strstr($key, 'email'))
                    if (!filter_var($_REQUEST[$key], FILTER_VALIDATE_EMAIL)) {
                        $error[] = "<span class='errorMessage'>Email address not valid</span>";
                        //print "\$_REQUEST[$key]: " . $_REQUEST[$key] . " <br>";
                    }
            } else {
                if ($_REQUEST['award'] == "pdfreport" && $key == "urlReport") {

                    if (trim($_REQUEST["urlReport"]) <> "" || trim($_FILES['fFile']['name']) <> "") {
                        continue;
                    }
                }
                $error[] = "<p><span class='important'>$key Please review the form and complete the missing fields.</span></p>";
            }
        }
    }



    if (count($error) == 0) {

//		var_dump($_REQUEST);
        $referenceNo = "#" . time();

        $styleSpan = "style=\"text-align: center; background-color: #CCC; \" ";
        $labelLongField = "style=\"vertical-align: top; width:300px;\" ";
        $body = "<html><head><title>Nomination form Example Awards</title></head><body>";
        $body .= "<table>";
        $body .= "<tr><td><b>Reference:</b></td><td>$referenceNo</td></tr>";
        $body .= "<tr><td><b>Award:</b></td><td>$awardsArr[desc] ($_REQUEST[award])</td></tr>";
        if ($_REQUEST['award'] == "pdfreport" || $_REQUEST['award'] == "service_provider") {
            
        } else {

            $body .= "<tr><td colspan=2 $styleSpan ><b>Nominee's details</b><br></td></tr>";
            if ($_REQUEST['award'] == "teamyear") {
                $body .= "<tr><td><b>Name of the Team Leader:</b></td><td>$_REQUEST[nominee_name]</td></tr>";
            } else {
                $body .= "<tr><td><b>Name:</b></td><td>$_REQUEST[nominee_name]</td></tr>";
            }
            $body .= "<tr><td><b>Job title:</b></td><td>$_REQUEST[nominee_job_title]</td></tr>";
            $body .= "<tr><td><b>Organisation:</b></td><td>$_REQUEST[nominee_organisation]</td></tr>";

            $body .= "<tr><td><b>Phone:</b></td><td>$_REQUEST[nominee_phone]</td></tr>";
            $body .= "<tr><td><b>E-mail:</b></td><td>$_REQUEST[nominee_email]</td></tr>";
            $body .= "<tr><td colspan=2>&nbsp;</td></tr>";
        }
        $body .= "<tr><td colspan=2 $styleSpan><b>Nominator's details</b><br></td></tr>";
        $body .= "<tr><td><b>Name:</b></td><td>$_REQUEST[nominator_name]</td></tr>";
        $body .= "<tr><td><b>Job title:</b></td><td>$_REQUEST[nominator_job_title]</td></tr>";
        $body .= "<tr><td><b>Organisation:</b></td><td>$_REQUEST[nominator_organisation]</td></tr>";

        $body .= "<tr><td><b>Phone:</b></td><td> $_REQUEST[nominator_phone] </td></tr>";
        $body .= "<tr><td><b>E-mail:</b></td><td> $_REQUEST[nominator_email] </td></tr>";
        $body .= "<tr><td><b>Hear about us:</b></td><td> $_REQUEST[hear_about] </td></tr>";
        

        $body .= "<tr><td colspan=2>&nbsp;</td></tr>";

        $body .= "</table>";
        $body .= "<table>";
        $body .= "<tr><td colspan=2>&nbsp;</td></tr>";

        if ($award == "pdfreport" || $award == "pdfreportunlisted") {
            $body .= "<tr><td colspan=2 $styleSpan><b>Report</b><br></td></tr>";
            if ($_FILES['fFile']['name'] != "") {
                list($error, $eMessage, $fileName, $path_of_uploaded_file) = getFile();
                //$body .= "<tr><td><b>file:</b></td><td>$error, $eMessage, $fileName, $path_of_uploaded_file</td></tr>";
            } else {
                $body .= "<tr><td><b>File:</b></td><td>No file attached</td></tr>";
            }
            $body .= "<tr><td><b>URL report:</b></td><td><a href=\"$_REQUEST[urlReport]\">$_REQUEST[urlReport]</a></td></tr>";
            $body .= "<tr><td colspan=2>&nbsp;</td></tr>";
            $body .= "</table></body></html>";

            if (!$error) { //&& $valid
                if (trim($_FILES['fFile']['name']) <> "") {
                    $from = "award_nomination@example.org.uk";
                    $to = "admin@example.org.uk";
                    $subject = "Submitted: " . $_REQUEST[nominator_email];
                    sendEmail($from, $to, $subject, $body, $fileName, $path_of_uploaded_file);

                    $to = "nominations@example.org.uk";
                    sendEmail($from, $to, $subject, $body, $fileName, $path_of_uploaded_file);
                } else {
                    $from = "award_nomination@example.org.uk";
                    $to = "nominations@example.org.uk";
                    $subject = "Submitted: " . $_REQUEST[nominator_email];
                    send_mail($from, $to, $subject, 1, $body);
                }
            } else {
                $from = "award_nomination@example.org.uk";
                $to = "admin@example.org.uk";
                $subject = "ERROR - Nomination form - Example awards";
                send_mail($from, $to, $subject, 1, $body);
            }
        } else {
            $body .= "<tr><td colspan=2 $styleSpan><b>The entry questions</b><br></td></tr>";
            foreach ($awardsArr as $q => $qDesc) {
                if ($q == "desc")
                    continue;
                $data = trim($_REQUEST[$q]);
                $body .= "<tr><td colspan=2><b>$qDesc</b></td></tr>";
                $body .= "<tr><td colspan=2>$data</td></tr>";
            }
            $body .= "<tr><td colspan=2>&nbsp;</td></tr>";
            $body .= "</table></body></html>";
            //echo "send email<br/>";
            //echo $body;

            $displayform = 0;

            $from = "award_nomination@example.org.uk";
            $to = "nominations@example.org.uk";
            $subject = "Submitted: " . $_REQUEST[nominator_email];
            send_mail($from, $to, $subject, 1, $body);

            $to = $_REQUEST[nominator_email];
            $subject = "Your Example awards nomination has been submitted ";
            send_mail($from, $to, $subject, 1, $body);
        }
        $finished = true;
    } else {
        $finished = false;
    }
}

$thisId = $modx->documentIdentifier;
$arrTvs = array("tvMsgAfterSubmit", "tvTopContent");
$arrValues = $modx->getTemplateVarOutput($arrTvs, $thisId);

if ($finished) {
    print $arrValues['tvMsgAfterSubmit'];

    //print out reference number
    echo "<p>Reference number: $referenceNo</p>";
} // end if($finished
else {

    if (count($error) > 0) {
        print "<div class='panel'><div class='panelContent'>" .
                "<p>$error[0]</p></div></div>";
    }
    getForm($award);
}

function stripslashes_deep($value) {
    $value = is_array($value) ?
            array_map('stripslashes_deep', $value) :
            stripslashes($value);

    return $value;
}

// // // // // // // // // // // // // // // // // // // // // // // // // // //

function ts($in) {
    if (strlen(trim($in)) > 0)
        return true;
    else
        return false;
}

// // // // // // // // // // // // // // // // // // // // // // // // // // // 
function getForm($award) {
    global $nommineeA, $awardsArr, $awardsLast, $required, $error, $testExclude;
    ?>
    <script type="text/javascript" >
        jQuery(document).ready(function ($) {
            //hide sections
            $('.nominationSection').addClass('m-hide st-hide lt-hide f-hide');
            //show first
            $('#nsection1').removeClass('m-hide st-hide lt-hide f-hide');
            //show buttons
            $('.btnNext, .btnBack').removeClass('m-hide st-hide lt-hide f-hide');
            $('.btnBack, .btnNext').click(function () {
                var ok = true;
                if ($(this).hasClass('btnNext')) {
                    var container = $(this).closest('.nominationSection');
                    ok = validateFields(container);
                    if (ok == false) {
                        return false;
                    } else {
                        if (container.attr('id') == "nsection1") {
                            //sendEmail();
                        }
                    }
                }
                var section = "#n" + $(this).attr('id');
                $('.nominationSection').addClass('m-hide st-hide lt-hide f-hide');
                $(section).removeClass('m-hide st-hide lt-hide f-hide');
                $('html, body').animate({scrollTop: 0}, 'slow');
                return false;
            })
            $(".wordCount").on('keyup', function () {
                var words = this.value.match(/\S+/g).length;
                var wordsString = this.value;
                var punctuationless = wordsString.replace(/[.,\/#!$%\^&\*;:{}=\-_`~'()]/g, "");
                var words = punctuationless.match(/\S+/g).length;
                if (words >= 200) { //Don't do anything
                    $(this).closest('fieldset').find('.wordsLeft').text("Words left: 0");
                } else {
                    var left = 200 - words;
                    $(this).closest('fieldset').find('.wordsLeft').text("Words left: " + left);
                }
            });
            $("form").find('.required').each(function () {
                if ($(this).hasClass('wordCount')) {
                    field = $(this);
                    words = checkWordCount(field);
                    if (words >= 200) { //Don't do anything
                        $(field).closest('fieldset').find('.wordsLeft').text("Words left: 0");
                    } else {
                        var left = 200 - words;
                        $(field).closest('fieldset').find('.wordsLeft').text("Words left: " + left);
                    }
                }
            });
            $("#nominationForm").submit(function (event) {
                var container = $('#sendNomination').closest('.nominationSection');
                if (validateFields(container)) {
                    //$("#nominationForm").submit();
                    return;
                } else {
                    return false;
                }
                event.preventDefault();
            });
            $("#uploadForm").submit(function () {
                var container = $('#uploadNomination');
                if (validateUpload(container)) {
                    //$("#nominationForm").submit();
                    return;
                } else {
                    return false;
                }
                event.preventDefault();
            });
            $(document).on("keypress", ":input:not(textarea)", function (event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                }
            });
            function sendEmail() {
                var urlReg = "/custom/ajax/sendmail.php";
                //formData = {pAction: 'brokenLink', url: url404, referer: urlReferer};
                formData = jQuery('form').serialize();
                var jqxhr = jQuery.getJSON(urlReg,
                        formData,
                        function (data) {

                        }).fail(function () {
                    jQuery('#message').html("An error occurred");
                });
            }

            function validateUpload(container) {
                var retVal = true;
                var errorFound = false;
                var errorMessage0 = "";
                var errorMessage1 = "";
                return true;

            }

            function checkWordCount(field) {
                fieldVal = field.val();
                if (fieldVal.length) {
                    var words = fieldVal.match(/\S+/g).length;
                    var wordsString = fieldVal;
                    var punctuationless = wordsString.replace(/[.,\/#!$%\^&\*;:{}=\-_`~'()]/g, "");
                    var words = punctuationless.match(/\S+/g).length;
                } else {
                    words = 0;
                }
                return words;
            }
            function validateFields(container) {
                var retVal = true;
                var errorFound = false;
                var errorMessage0 = "";
                var errorMessage1 = "";
                container.find('.required').each(function () {
                    //alert($(this).is(':checked') + " "+$(this).attr('name')+ ": "+  $(this).val());

                    if ($(this).attr('type') == "checkbox" && !$(this).is(':checked')) {
                        //alert("Please complete the required fields before you continue. Thank you.");
                        errorMessage0 = "Please complete the required fields before you continue. Thank you.";
                        $(this).closest('fieldset').addClass("errorField");
                        retVal = false;
                        errorFound = true;
                        //return false;
                    } else {
                        if ($.trim($(this).val()) == "" && !$(this).hasClass('wordCount')) {
                            //alert("Please complete the required fields before you continue. Thank you.");
                            errorMessage0 = "Please complete the required fields before you continue. Thank you.";
                            $(this).closest('fieldset').addClass("errorField");
                            retVal = false;
                            errorFound = true;
                            //return false;
                        } else {
                            $(this).closest('fieldset').removeClass("errorField");
                            if ($(this).hasClass('wordCount')) {
                                field = $(this);
                                words = checkWordCount(field);
                                if (words >= 200) {
                                    $(field).closest('fieldset').find('.wordsLeft').text("Words left: 0");
                                    $(field).closest('fieldset').removeClass("errorField");
                                } else {
                                    var left = 200 - words;
                                    $(field).closest('fieldset').find('.wordsLeft').text("Words left: " + left);
                                    $(field).closest('fieldset').addClass("errorField");
                                    errorMessage1 = "A minimum of 200 words is required in the highlighted questions.";
                                    errorFound = true;
                                    retVal = false;
                                }
                            }
                            if ($(this).hasClass('fEmail')) {
                                retVal = validEmail($(this).val());
                                if (!retVal) {
                                    alert("Email address not valid.");
                                }
                            }
                        }
                    }
                });
                if (errorFound) {
                    var errorMessage = errorMessage0 + "\n" + errorMessage1;
                    alert(errorMessage);
                }
                return retVal;
            }

            function validEmail(email) {
                var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/igm;
                if (email == "" || !re.test(email)) {
                    return false;
                } else {
                    return true;
                }
            }

        });
    </script>

    <?php
    switch ($award) {
        case "actoryear":
            $introText = "{{awards 2018 - example actor of the year}}";
            break;

        case "teamyear":
            $introText = "{{awards 2018 - example team of the year}}";
            break;

        case "service_provider":
            $introText = "{{awards 2018 - example service provider of the year}}";
            break;

        case "projectyear":
            $introText = "{{awards 2018 - example project of the year}}";
            break;

        case "gpyear":
            $introText = "{{awards 2018 - example professional of the year}}";
            break;

        case "fishyear":
            $introText = "{{awards 2018 - example fishmonger of the year}}";
            break;

        case "pdfreport":
            $introText = "{{awards 2018 - example PDF report of the year}}";
            break;
    }
    /*
      if ($_SERVER[REMOTE_ADDR] == '89.197.8.210' || $testExclude==1) {

      }else {
      echo "$introText";
      echo "<p><strong>The nomination form will be available soon.</strong></p>";
      return;
      }
     */

    if ($award == "pdfreport") {

        echo "$introText";
        ?>
        <form id="nominationForm" name="nominationForm" class="genericForm" method="POST" enctype="multipart/form-data" action="[~[*id*]~]">
            <?php
            echo "<div id='nsection1' class='nominationSection'  >";
            echo "<input type='hidden' name='award' id='award' value='$award' >";
            echo "<input type='hidden' name='awardName' id='awardName' value='$awardsArr[desc]' >";

            echo "<h2 class='sectionTitle1' >Nomination form </h2>";
            echo "<h2 class='sectionTitle' >Your details</h2>";
            getFormNominator();
            echo "<p></p>";
            echo "<h2 class='sectionTitle'>Privacy and Terms</h2>";
            getPrivacypdfreport();
            echo "<p></p>";
            getFormQuestions($award);
            print "<div class=\"submitArea\">
            <p class=\"alignRight\">
            <input type=\"submit\" class=\"submitButton\" id='sendNomination' name='sendNomination' value=\"Submit nomination\" />" .
                    "</p>
            </div>";

            echo "</div>";
        } elseif ($award == "service_provider") {
            echo "<div id='nsection1' class='nominationSection'  >";
            echo "$introText";
            ?>
            <form id="nominationForm" name="nominationForm" class="genericForm" method="POST" enctype="multipart/form-data" action="[~[*id*]~]">
                <?php
                echo "<input type='hidden' name='award' id='award' value='$award' >";
                echo "<input type='hidden' name='awardName' id='awardName' value='$awardsArr[desc]' >";

                echo "<h2 class='sectionTitle1' >Nomination form </h2>";
                echo "<h2 class='sectionTitle' >Your details</h2>";
                getFormNominator();
                echo "<p></p>";
                echo "<h2 class='sectionTitle'>Privacy and Terms</h2>";
                getPrivacy();
                echo "<p></p>";
                getFormQuestions($award);

                print "<div class=\"submitArea\">
            <p class=\"alignRight\">
            <input type=\"submit\" class=\"submitButton\" id='sendNomination' name='sendNomination' value=\"Submit nomination\" />" .
                        "</p>
            </div>";

                echo "</div>";
            } else {
                // Nominee

                echo "$introText";
                ?>
                <form id="nominationForm" name="nominationForm" class="genericForm" method="POST" enctype="multipart/form-data" action="[~[*id*]~]">
                    <?php
                    echo "<div id='nsection1' class='nominationSection'>";
                    echo "<input type='hidden' name='award' id='award' value='$award' >";
                    echo "<input type='hidden' name='awardName' id='awardName' value='$awardsArr[desc]' >";


                    echo "<h2 class='sectionTitle1' >Nomination form </h2>";
                    echo "<h2 class='sectionTitle' >Your details</h2>";
                    getFormNominator();
                    echo "<p></p>";
                    echo "<h2 class='sectionTitle'>Privacy and Terms</h2>";
                    getPrivacy();
                    echo "<p></p>";
                    if ($award == "service_provider") {
                        echo "<h2 class='sectionTitle' >Your client details?</h2>";
                    } else {
                        echo "<h2 class='sectionTitle' >Who are you nominating?</h2>";
                    }
                    getFormNominee($award);
                    echo "<p>";
                    getFormQuestions($award);
                    print "<div class=\"submitArea\">
            <p class=\"alignRight\">
            <input type=\"submit\" class=\"submitButton\" id='sendNomination' name='sendNomination' value=\"Submit nomination\" />" .
                            "</p>
            </div>";

                    echo "</div>";
                }
                ?>
            </form>
            <?php
        }

        function topNav($step) {
            $strongS[$step] = "<strong>";
            $strongE[$step] = "</strong>";
            echo "<div class=row>";
            echo "<div class='column two alignCenter'>$strongS[1] Nominee details $strongE[1]</div>";
            echo "<div class='column one alignCenter'><div style='width:100%;line-height:10px; border-bottom: 1px solid #000;'>&nbsp;</div></div>";
            echo "<div class='column two alignCenter'>$strongS[2] The entry $strongE[2]</div>";
            echo "<div class='column one alignCenter'><div style='width:100%;line-height:10px; border-bottom: 1px solid #000;'>&nbsp;</div></div>";
            echo "<div class='column two alignCenter last'>$strongS[3] Your details $strongE[3]</div>";
            echo "</div>";
        }

        function getPrivacy() {
            echo "<fieldset>";
            echo "<p>We will use your contact details to communicate with you about your nomination and the awards evening. Please ensure that you have the permission of your nominee/s to propose them. If your nominee/s are shortlisted, we will use the contact details that you have provided to seek their consent to participate in the next stage.</p><br>";
            echo "<p>See our <a href=\"#\" target=\"_blank\">privacy statement</a> for more about how Example Organisation uses and protects personal data.<br>";
            echo "Read the <a href=\"#\" target=\"_blank\">terms and conditions</a> for Awards nominations before you submit your entry.</p><br>";
            echo "<input type=\"checkbox\" name=\"checkbox1\" id=\"checkbox1\" value=\"Accepted\" class=\"required\"><label for=\"checkbox1\"> I accept the terms and conditions for Example Awards nominations.</label>";
            echo "<input type=\"checkbox\" name=\"checkbox2\" id=\"checkbox2\" value=\"Accepted\" class=\"required\"><label for=\"checkbox2\"> I have the permission of those that I am nominating to put them forward for an award.</label>";
            echo "</fieldset>";
        }

        function getPrivacypdfreport() {
            echo "<fieldset>";

            echo "<p>We will use your contact details to communicate with you about your nomination and the awards evening.</p><br>";
            echo "<p>See our <a href=\"#\" target=\"_blank\">privacy statement</a> for more about how Example Organisation uses and protects personal data.<br>";
            echo "Read the <a href=\"#\" target=\"_blank\">terms and conditions</a> for Awards nominations before you submit your entry.</p><br>";
            echo "<input type=\"checkbox\" name=\"checkbox1\" id=\"checkbox1\" value=\"Accepted\" class=\"required\"><label for=\"checkbox1\"> I accept the terms and conditions for Example Awards nominations.</label>";
            echo "</fieldset>";
        }

        function getFormNominator() {
            echo "<fieldset>";
            echo "<label for='nominator_name'>Name <sup><span class=smallText>*</span></sup></label>";
//      print "<p class=''> ";
//      print $required['nominator_name'];
//      print $error['nominator_name'];
//      print "</p>";
            echo "<p>";
            echo "<input name='nominator_name' id='nominator_name' class='required' type='text' size='50' value=\"$_REQUEST[nominator_name]\" />";
            echo "</p>";
            echo "</fieldset>";

            echo "<fieldset>";
            echo "<label for='nominator_job_title'>Job title <sup><span class=smallText>*</span></sup></label>";
            echo "<p>";
            echo "<input name='nominator_job_title' id='nominator_job_title' class='required' type='text' size='50' value=\"$_REQUEST[nominator_job_title]\" />";
            echo "</p>";
            echo "</fieldset>";

            echo "<fieldset>";
            echo "<label for='nominee_organisation'>Organisation <sup><span class=smallText>*</span></sup></label>";
            echo "<p>";
            echo "<input name='nominator_organisation' id='nominator_organisation' class='required' type='text' size='50' value=\"$_REQUEST[nominator_organisation]\" />";
            echo "</p>";
            echo "</fieldset>";

//    echo "<fieldset>";
//    echo "<label for='nominee_address'>Address <sup><span class=smallText>*</span></sup></label>";
//    echo "<p>";
//    echo "<textarea name='nominator_address' id='nominator_address' class='required' type='text' rows='4' cols='38'>$_REQUEST[nominator_address]</textarea>";
//    echo "</p>";
//    echo "</fieldset>";
//
//    echo "<fieldset>";
//    echo "<label for='nominator_postcode'>Postcode <sup><span class=smallText>*</span></sup></label>";
//    echo "<p>";
//    echo "<input name='nominator_postcode' id='nominator_postcode' class='required' type='text' size='10' maxlength ='10' value=\"$_REQUEST[nominator_postcode]\" />";
//    echo "</p>";
//    echo "</fieldset>";

            echo "<fieldset>";
            echo "<label for='nominator_phone'>Phone <sup><span class=smallText>*</span></sup></label>";
            echo "<p>";
            echo "<input name='nominator_phone' id='nominator_phone' class='required' type='text' size='25' value=\"$_REQUEST[nominator_phone]\" />";
            echo "</p>";
            echo "</fieldset>";

            echo "<fieldset>";
            echo "<label for='nominator_email'>Email <sup><span class=smallText>*</span></sup></label>";
            echo "<p>";
            echo "<input name='nominator_email' id='nominator_email' class='required fEmail' type='text' size='50' value=\"$_REQUEST[nominator_email]\" />";
            echo "</p>";
            echo "</fieldset>";

            echo "<fieldset>";
            echo "<label for='hear_about'>How did you hear about the Awards?</label>";
            echo "<div class='fieldWrapper'>";
            echo "<textarea name='hear_about' id='hear_about'  type='text' rows='2' cols='38'>$_REQUEST[hear_about]</textarea>";
            echo "</div>";
            echo "</fieldset>";
        }

        function getFormNominee($award) {
            if ($award == "teamyear") {
                $nameLabel = "Name of the team leader";
            } else {
                $nameLabel = "Name";
            }
            echo "<fieldset>";
            echo "<label for='nominee_name'>$nameLabel <sup><span class=smallText>*</span></sup></label>";
            echo "<p>";
            echo "<input name='nominee_name' id='nominee_name' class='required' type='text' size='50' value=\"$_REQUEST[nominee_name]\" />";
            echo "</p>";
            echo "</fieldset>";

            echo "<fieldset>";
            echo "<label for='nominee_job_title'>Job title <sup><span class=smallText>*</span></sup></label>";
            echo "<p>";
            echo "<input name='nominee_job_title' id='nominee_job_title' class='required' type='text' size='50' value=\"$_REQUEST[nominee_job_title]\" />";
            echo "</p>";
            echo "</fieldset>";

            echo "<fieldset>";
            echo "<label for='nominee_organisation'>Organisation <sup><span class=smallText>*</span></sup></label>";
            echo "<p>";
            echo "<input name='nominee_organisation' id='nominee_organisation' class='required' type='text' size='50' value=\"$_REQUEST[nominee_organisation]\" />";
            echo "</p>";
            echo "</fieldset>";

            echo "<fieldset>";
            echo "<label for='nominee_email'>Email <sup><span class=smallText>*</span></sup></label>";
            echo "<p>";
            echo "<input name='nominee_email' id='nominee_email' class='required fEmail' type='text' size='50' value=\"$_REQUEST[nominee_email]\" />";
            echo "</p>";
            echo "</fieldset>";

//    echo "<fieldset>";
//    echo "<label for='nominee_address'>Address <sup><span class=smallText>*</span></sup></label>";
//    echo "<p>";
//    echo "<textarea name='nominee_address' id='nominee_address' class='required' type='text' rows='4' cols='38'>$_REQUEST[nominee_address]</textarea>";
//    echo "</p>";
//    echo "</fieldset>";
//
//    echo "<fieldset>";
//    echo "<label for='nominee_postcode'>Postcode <sup><span class=smallText>*</span></sup></label>";
//    echo "<p>";
//    echo "<input name='nominee_postcode' id='nominee_postcode' class='required' type='text' size='10' maxlength='10' value=\"$_REQUEST[nominee_postcode]\" />";
//    echo "</p>";
//    echo "</fieldset>";

            echo "<fieldset>";
            echo "<label for='nominee_phone'>Phone <sup><span class=smallText>*</span></sup></label>";
            echo "<p>";
            echo "<input name='nominee_phone' id='nominee_phone' class='required' type='text' size='25' value=\"$_REQUEST[nominee_phone]\" />";
            echo "</p>";
            echo "</fieldset>";
        }

        function getFormQuestions($award) {
//    $nomineeName = "<fieldset>";
//    $nomineeName .= "<label for='nominee_name'>Nominee name <sup><span class=smallText>*</span></sup></label>";
//    $nomineeName .= "<p>";
//    $nomineeName .= "<input name='nominee_name' id='nominee_name' class='required' type='text' size='50' value=\"$_REQUEST[nominee_name]\" />";
//    $nomineeName .= "</p>";
//    $nomineeName .= "</fieldset>";

            $questionArr = getAwardDetails($award);
            //var_dump($questionArr);


            $highlightedText2 = "<p class='highlighted'>The online form contains three pages which include the questions, "
                    . "contact for nominator and contact for nominees. Please read through the form before you "
                    . "begin completing to ensure you have all information required to submit your application. "
                    . "We suggest you compile and save your answers in an offline document (e.g. Word) and then "
                    . "paste them into this online form when you are ready to submit your application.</p> ";
            $steps = "";

            $maxWords = "<span class='smallText'>(Minimum of 200 words)</span> <span class='_wordsLeft smallText'>"
                    . "</span>";
            $textAreaClass = " class='wordCount required' ";
            $textRequiredClass = " class=' required' ";
            switch ($award) {
                case "actoryear":
                    echo $highlightedText;

                    echo "<h2 class='sectionTitle' >$steps Why do you think the nominee should win this award?</h2>";
                    echo $nomineeName;
                    echo "<fieldset>";
                    echo $questionArr['fullQ1'];

                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ1' id='fullQ1' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ1]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ2'];

                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ2' id='fullQ2' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ2]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ3'];



                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ3' id='fullQ3' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ3]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ4'];


                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ4' id='fullQ4' type='text' rows='4' >$_REQUEST[fullQ4]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";
                    break;

                case "teamyear":
                    echo $highlightedText;
                    echo "<h2 class='sectionTitle' >$steps Why do you think this team should win this award?</h2>";
                    echo $nomineeName;

                    echo "<fieldset>";
                    echo $questionArr['fullQ1'];

                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ1' id='fullQ1' type='text' rows='4' class='required' >$_REQUEST[fullQ1]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ2'];

                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ2' id='fullQ2' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ2]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ3'];

                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ3' id='fullQ3' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ3]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ4'];

                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ4' id='fullQ4' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ4]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ5'];

                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ5' id='fullQ5' type='text' rows='4' >$_REQUEST[fullQ5]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";
                    break;

                case "service_provider":
                    echo $highlightedText;
                    echo "<h2 class='sectionTitle' >Why do you think you or your company should win this award?</h2>";
                    echo $nomineeName;
                    echo "<fieldset>";
                    echo $questionArr['fullQ1'];

                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ1' id='fullQ1' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ1]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ2'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ2' id='fullQ2' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ2]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ3'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ3' id='fullQ3' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ3]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ4'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ4' id='fullQ4' type='text' rows='4' $textRequiredClass>$_REQUEST[fullQ4]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";


                    break;

                case "projectyear":
                    echo $highlightedText;
                    echo "<h2 class='sectionTitle' >$steps Why do you think this project should win this award?</h2>";
                    echo $nomineeName;
                    echo "<fieldset>";
                    echo $questionArr['fullQ1'];

                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ1' id='fullQ1' type='text' rows='4' $textAreaClass >$_REQUEST[fullQ1]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ2'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ2' id='fullQ2' type='text' rows='4' $textAreaClass >$_REQUEST[fullQ2]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ3'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ3' id='fullQ3' type='text' rows='4' $textAreaClass >$_REQUEST[fullQ3]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ4'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ4' id='fullQ4' type='text' rows='4' $textAreaClass >$_REQUEST[fullQ4]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ5'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ5' id='fullQ5' type='text' rows='4' $textAreaClass >$_REQUEST[fullQ5]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";


                    break;

                case "gpyear":
                    echo $highlightedText;
                    echo "<h2 class='sectionTitle' >$steps Why do you think the nominee should win this award?</h2>";
                    echo $nomineeName;
                    echo "<fieldset>";
                    echo $questionArr['fullQ1'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ1' id='fullQ1' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ1]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ2'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ2' id='fullQ2' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ2]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ3'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ3' id='fullQ3' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ3]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";
                    echo "<fieldset>";
                    echo $questionArr['fullQ4'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ4' id='fullQ4' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ4]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";


                    echo "<fieldset>";
                    echo $questionArr['fullQ5'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ5' id='fullQ5' type='text' rows='4' $textAreaClass>$_REQUEST[fullQ5]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";


                    break;

                case "fishyear":
                    echo $highlightedText;
                    echo "<h2 class='sectionTitle' >$steps Why do you think the nominee should win this award?</h2>";
                    echo $nomineeName;
                    echo "<fieldset>";
                    echo $questionArr['fullQ1'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ1' id='fullQ1' type='text' rows='4' $textAreaClass >$_REQUEST[fullQ1]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ2'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ2' id='fullQ2' type='text' rows='4' $textAreaClass >$_REQUEST[fullQ2]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    echo "<fieldset>";
                    echo $questionArr['fullQ3'];
                    echo "<div class='fieldWrapper'>";
                    echo "<textarea  name='fullQ3' id='fullQ3' type='text' rows='4'  >$_REQUEST[fullQ3]</textarea>";
                    echo "</div>";
                    echo "</fieldset>";

                    break;

                case "pdfreport":
                    echo "<h2 class='sectionTitle' >PDF report</h2>";
                    echo "<fieldset>";
                    echo "<label for='q1'>Upload a copy of the PDF report here</label>";
                    echo "<p>";
                    echo "<input class=\"formInput\" type=\"file\" id=\"fFile\" name=\"fFile\" />";
                    echo "</p>";
                    echo "</fieldset>";
                    echo "<fieldset>";
                    echo "<p> Or </p>";
                    echo "<label for='q2'>Provide the web address of where we can find a copy below </label>";
                    echo "<p>";
                    echo "<input type=\"text\" id=\"urlReport\" name=\"urlReport\" size='50' value=\"$_REQUEST[urlReport]\"/>";
                    echo "</p>";
                    echo "</fieldset>";
                    break;
            }
        }

        function getAwardDetails($award) {
            $maxWords = "<span class='smallText'>(200 words min; 500 words max) </span> <span class='_wordsLeft smallText'>"
                    . "</span>";
            switch ($award) {
                case "actoryear":
                    $retArr['desc'] = "Example actor of the Year";

                    $retArr['fullQ1'] = "<label for='q1'>Example question 1  $maxWords </label>"
                            . "<ul>
            <li><span class='smallText'>
            Example question subcategories</span></li>
            <li><span class='smallText'>Example question subcategories</span></li>
            </ul>";

                    $retArr['fullQ2'] = "<label for='q2'>Example question 2 $maxWords </label>"
                            . "<ul><li><span class='smallText'>Example question subcategories</span></li></ul>";

                    $retArr['fullQ3'] = "<label for='q3'>Example question 3 $maxWords</label>";
                    $retArr['fullQ3'] .= "<ul><li><span class='smallText'>
            Example question subcategories</span>
            </li></ul>";


                    $retArr['fullQ4'] = "<label for='q4'>Example question 4 </label>";
                    $retArr['fullQ4'] .= "<ul><li><span class='smallText'>
            Example question subcategories</span>
            </li>
            <li><span class='smallText'>
            Example question subcategories</span>
            </li></ul>";

                    break;



                case "teamyear":
                    $retArr['desc'] = "Team of the Year ";
                    $retArr['fullQ1'] = "<label for='q1'>Example question 1  $maxWords</label>";
                    $retArr['fullQ1'] .= "<ul><li><span class='smallText'>
            Example question subcategories/span>
            </li></ul>";
                    $retArr['fullQ2'] = "<label for='q2'>Example question 2  $maxWords</label>";
                    $retArr['fullQ2'] .= "<ul><li><span class='smallText'>
            Example question subcategories </span>
            </li></ul>";

                    $retArr['fullQ3'] = "<label for='q3'>Example question 3   $maxWords</label>";
                    $retArr['fullQ3'] .= "<ul><li><span class='smallText'>
            Example question subcategories 
			</span>
            </li></ul>";

                    $retArr['fullQ4'] = "<label for='q3'>Example question 4  $maxWords</label>";
                    $retArr['fullQ4'] .= "<ul><li><span class='smallText'>
            Example question subcategories </span>
            </li></ul>";

                    $retArr['fullQ5'] = "<label for='q3'>Example question 5 $maxWords</label>";

                    break;

                case "service_provider":
                    $retArr['desc'] = "Example Service Provider of the Year";

                    $retArr['fullQ1'] = "<label for='q1'>Example question 1   $maxWords</label>";
                    $retArr['fullQ1'] .= "<ul>
                <li><span class='smallText'>Example question subcategories</span> </li>
                <li><span class='smallText'>Example question subcategories</span> </li>
            </ul>";

                    $retArr['fullQ2'] = "<label for='q2'>Example question 2 $maxWords</label>";
                    $retArr['fullQ2'] .= "<ul><li><span class='smallText'>
             Example question subcategories </span>
            </li></ul>";

                    $retArr['fullQ3'] = "<label for='q3'>Example question 3  $maxWords</label>";
                    $retArr['fullQ3'] .= "";

                    $retArr['fullQ4'] = "<label for='q4'> Example question 4</label>";
                    $retArr['fullQ4'] .= "<ul><li><span class='smallText'>
             Example question subcategories </span>
            </li></ul>";

                    break;

                case "projectyear":
                    $retArr['desc'] = "Example Project of the Year";




                    $retArr['fullQ1'] = "<label for='q1'>Example question 1     $maxWords</label>";
                    $retArr['fullQ1'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories 
            </span>
            </li>
           <li><span class='smallText'>
            Example question subcategories
            </span>
            </li>
           <li><span class='smallText'>
           Example question subcategories
            </span>
            </li>
            </ul>";

                    $retArr['fullQ2'] = "<label for='q2'>Example question 2 $maxWords</label>";
                    $retArr['fullQ2'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>
            <li><span class='smallText'>
           Example question subcategories
            </span></li>
            </ul>";

                    $retArr['fullQ3'] = "<label for='q3'>Example question 3   $maxWords</label>";
                    $retArr['fullQ3'] .= "<ul>
            <li><span class='smallText'>
           Example question subcategories
            </span></li>

            </ul>";

                    $retArr['fullQ4'] = "<label for='q4'>Example question 4   $maxWords</label>";
                    $retArr['fullQ4'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>

            </ul>";

                    $retArr['fullQ5'] = "<label for='q5'>Example question 5  $maxWords</label>";
                    $retArr['fullQ5'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>

            </ul>";



                    break;

                case "gpyear":
                    $retArr['desc'] = "Example Professional of the year";
                 
                    $retArr['fullQ1'] = "<label for='q1'>Example question 1  $maxWords</label>";
                    $retArr['fullQ1'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories  
            </span></li>

            </ul>";

                    $retArr['fullQ2'] = "<label for='q2'>Example question 2    $maxWords</label>";
                    $retArr['fullQ2'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories  
            </span></li>
            </ul>";

                    $retArr['fullQ3'] = "<label for='q3'>Example question 3 $maxWords</label>";
                    $retArr['fullQ3'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories 
            </span></li>
            <li><span class='smallText'>
            Example question subcategories 
            </span></li>
            </ul>";

                    $retArr['fullQ4'] = "<label for='q4'>Example question 4   $maxWords</label>";
                    $retArr['fullQ4'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>
            
            </ul>";

                    $retArr['fullQ5'] = "<label for='q5'>Example question 5 $maxWords</label>";
                    $retArr['fullQ5'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>

            </ul>";

                    break;

                case "fishyear":
                    $retArr['desc'] = "Fishmonger of the year";
                  
                    $retArr['fullQ1'] = "<label for='q1'>Example question 1 $maxWords</label>";
                    $retArr['fullQ1'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>

            </ul>";
                    $retArr['fullQ2'] = "<label for='q2'>Example question 2  $maxWords</label>";
                    $retArr['fullQ2'] .= "<ul>
            <li><span class='smallText'>
            PExample question subcategories
            </span></li>
            <li><span class='smallText'>
            Example question subcategories 
            </span></li>

            </ul>";
                    $retArr['fullQ3'] = "<label for='q3'>Example question 3 $maxWords</label>";
                    $retArr['fullQ3'] .= "<ul>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>
            <li><span class='smallText'>
            Example question subcategories 
            </span></li>
            <li><span class='smallText'>
            Example question subcategories
            </span></li>

            </ul>";

                    break;

                case "pdfreport":
                    $retArr['desc'] = "Example PDF Report of the Year";
                    break;


                case "teamyear_unlisted":
                    $retArr['desc'] = "Example Team of the year - unlisted";
                    $retArr['q1'] = "Example question 1";
                    $retArr['q2'] = "Example question 2";
                    $retArr['q3'] = "Example question 3";
                    $retArr['q4'] = "Example question 4";
                    break;
                    
                    break;
                case "pdfreportunlisted":
                    $retArr['desc'] = "Best PDF report - Unlisted";
                    break;
            }

            return $retArr;
        }

        function confirm_email($labStyle, $class) {
            ?>
            <p>
                <label <?php echo $labStyle ?>>Confirm email:&nbsp;</label>
                <input class="<?php print $class[conemail]; ?>" type="text" name="confirm_email" value="<?php print $_REQUEST[confirm_email]; ?>">
            </p>
            <?php
        }

// end function way_heard()
// // // // // // // // // // // // // // // // // // // // // // // // // // // 

        function email($labStyle, $class) {
            ?>
            <p>
                <label <?php echo $labStyle ?> for="email">E-mail address:&nbsp;<em>*</em></label>
                <input type="text" class="<? print $class['email']; ?>" id="email"  name="email" value="<? print $_REQUEST['email']; ?>"/>
                <!--<span id="err_nm_email" class="error m-hide st-hide lt-hide f-hide">Enter a valid email address</span>-->
            </p>
            <?php
        }

// end function email()
// // // // // // // // // // // // // // // // // // // // // // // // // // // 

        function send_mail($from, $to, $subject, $status, $body) {
            //$from = "support@example.org.uk"; //"support@example.org.uk";
//  $from = "award_nomination@example.org.uk";
//  $to = "admin@example.org.uk";
//  $subject = "Nomination form - Example Awards 2018";
//  $to = "nominations@example.org.uk";
//  $headers = "MIME-Version: 1.0 \r\n";
//  $headers .= "Content-type: text/html; charset=iso-8859-1 \r\n";
//  //$headers .= "To: $to " . "\r\n";
//  $headers .= "From: $from " . "\r\n";
//
//  
//  mail($to, $subject, $message, $headers);


            $headers = "MIME-Version: 1.0 \r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1 \r\n";
            //$headers .= "To: $to " . "\r\n";
            $headers .= "From: $from " . "\r\n";
            mail($to, $subject, $body, $headers);
        }

        function checkAwardcode($award) {
            $listCodes = array("actoryear", "teamyear", "projectyear", "gpyear", "service_provider", "fishyear", "pdfreport");

            if (inArray(strtolower(trim($award)), $listCodes)) {
                return true;
            } else {
                return false;
            }
        }

// end function send_mail()


        function getFile($uploadPDF = 0) {
            global $_REQUEST, $_FILES;

            $error = false;
            $eMessage = "";
            //Get the uploaded file information
            $name_of_uploaded_file = basename($_FILES['fFile']['name']);

            //get the file extension of the file
            $type_of_uploaded_file = substr($name_of_uploaded_file, strrpos($name_of_uploaded_file, '.') + 1);


            $size_of_uploaded_file = $_FILES["fFile"]["size"] / 1024; //size in KBs
            if ($uploadPDF == 0) {
                //Settings
                $upload_folder = "../uploads/pdfreport/";
                //$upload_folder = "uploads/pdfreport/";
                $max_allowed_file_size = 8000; // size in KB
                $allowed_extensions = array("txt", "pdf", "doc");
            } else {
                //Settings
                $upload_folder = "../uploads/nominationform/";
                //$upload_folder = "uploads/pdfreport/";
                $max_allowed_file_size = 8000; // size in KB
                $allowed_extensions = array("pdf");
            }
//Validations
            if ($size_of_uploaded_file > $max_allowed_file_size) {
                $errors .= "\n Size of file should be less than $max_allowed_file_size";
                $error = true;
            }
            if (!$error) {

                //------ Validate the file extension -----
                $allowed_ext = false;
                for ($i = 0; $i < sizeof($allowed_extensions); $i++) {
                    if (strcasecmp($allowed_extensions[$i], $type_of_uploaded_file) == 0) {
                        $allowed_ext = true;
                    }
                }

                //$allowed_ext = true;
                if (!$allowed_ext) {
                    $eMessage .= "<div class=\"panel background-black\"><h3><a href=\"#anchor\">The uploaded file is not a supported file type. " .
                            " Only the following file types are supported: " . strtoupper(implode(',', $allowed_extensions)) . "</a></h3></div>";
                    $error = true;
                }
                if (!$error) {

                    //copy the temp. uploaded file to uploads folder
                    $newFileName = time() . "_$name_of_uploaded_file";
                    $path_of_uploaded_file = $upload_folder . $newFileName;
                    $tmp_path = $_FILES["fFile"]["tmp_name"];


                    if (is_uploaded_file($tmp_path)) {
                        if (!move_uploaded_file($tmp_path, $path_of_uploaded_file)) {
                            $eMessage .= '\n Error uploading file';
                            $error = true;
                        }
                    }
                }
            }

            return array($error, $eMessage, $newFileName, $path_of_uploaded_file);
        }

        function sendEmail($from, $to, $subject, $body, $filename, $path_of_uploaded_file) {

            //$body = strip_tags($body);
            $attachment = chunk_split(base64_encode(file_get_contents($path_of_uploaded_file)));
            $filename = basename($filename);

            $boundary = md5(date('r', time()));

            $headers = "From: $from\r\nReply-To: $from";
            $headers .= "\r\nMIME-Version: 1.0\r\nContent-Type: multipart/mixed; boundary=\"_1_$boundary\"";

            $message = "This is a multi-part message in MIME format.

--_1_$boundary
Content-Type: multipart/alternative; boundary=\"_2_$boundary\"

--_2_$boundary
Content-Type: text/html; charset=\"iso-8859-1\"
Content-Transfer-Encoding: 7bit

$body

--_2_$boundary--
--_1_$boundary
Content-Type: application/octet-stream; name=\"$filename\" 
Content-Transfer-Encoding: base64 
Content-Disposition: attachment 

$attachment
--_1_$boundary--";

            $flag = mail($to, $subject, $message, $headers);

            return $flag;
        }
        ?>
