<?php
require_once('../config.php');
include 'tool-config.php';

use \Tsugi\Core\LTIX;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();

$menu = false; // We are not using a menu

// Start of the output
$OUTPUT->header();

include("tool-header.html");

$OUTPUT->bodyStart();

$OUTPUT->topNav($menu);

if (!$USER->instructor) {
    header('Location: ' . addSession('student-home.php'));
}

    $context = array();
    $providers  = $LAUNCH->ltiRawParameter('lis_course_section_sourcedid','none');
    $context_id = $LAUNCH->ltiRawParameter('context_id','none');

    $context['providers'] = array();
    $context['provider'] = 'none';
    
    if ($providers != $context_id) {
        // So we might have some providers to show
        $list = explode('+', $providers);
            
        if (count($list) == 1) {
            $context['provider'] = $list[0];
        } else {
            $context['providers'] = $list;
        }
    }
    
    // $context['course_title'] = $app['tsugi']->context->title;
    $context['email'] = $USER->email;
    $context['user'] = $USER->displayname;
    $context['submit'] = addSession( str_replace("\\","/",$CFG->getCurrentFileUrl('process.php')) );
    
    if ($tool['debug']) {
        echo '<pre>'; print_r($context); echo '</pre>';
    }
?>
    <section>
        <div class="row">
            <div class="col-xs-12">
                <h3>What you need to know when migrating to BrightSpace</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <p>Somethings will work fine:</p>
                <ol>
                    <li>Lessons</li>
                    <li>...</li>
                    <li>...</li>
                    <li>...</li>
                </ol>

                <p>Need more info 
                    <span class="glyphicon glyphicon-question-sign"></span>
                    <a href="#" title="More info" target="_blank">more info</a>
                </p>
            </div>
            <div class="col-md-6">
                <p>Somethings aren't available in Brightspace</p>
                <ol>                
                    <li>...</li>
                    <li>...</li>
                    <li>...</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-md-9">
                <h4>What is going to happen when I start this process</h4>
                <ul style="margin-left: 60px;">                
                    <li>Completing the form below and clicking the "Get Started" button will start exporting <i>this</i> site.<br/>
                        We will send you (and everyone in the notification list) that the migration process has started.</li>
                    <li>After export we will run some scripts to transform the exported files so that Brightspace can use them and re-create your content.</li>
                    <li>Import the site content into Brightspace.</li>
                    <li>The Final Report will be sent out detailing the process steps and the Brightspace course information.</li>
                </ul>
            </div>
        </div>
    </section>
    <section>
        <div class="row">
            <div class="col-md-9">
                <h4>Let's begin ...</h4>
                <form class="form-inline text-left" method="post" target="_self" id="metadata">
                    <input type="hidden" name="type"  id="type" value="remove"/>

		            <div class="row" style="margin-top: 1em;">
                        <div class="col-md-3 col-xs-12 col-sm-11">
                            <label for="organizer">My Email</label>
                        </div>
                        <div class="col-md-8 col-xs-12 col-sm-11 col-md-offset-0">
                            <input type="text" name="organizer" id="organizer" disabled="true" class="form-control disabled" value="<?= $context['email'] ?> (<?= $context['user'] ?>)"/>
                            <br><small style="color:#aaa">Will receive progress and final report.</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-xs-12 col-sm-11">
                            <label for="notifications">Email Notifications</label>
                        </div>
                        <div class="col-md-8 col-xs-12 col-sm-11 col-md-offset-0">
                            <textarea class="form-control" name="notifications" id="notifications" placeholder="Additional email addresses that you might want to notify of this migration"></textarea>
                        </div>
                    </div>
                    <div class="row terms">
                        <div class="col-md-3 col-xs-12 col-sm-11">
                            <label for="terms">My Responsibilities</label>
                        </div>
                        <div class="col-md-8 col-xs-12 col-sm-11 col-md-offset-0">
                            <label class="checkbox-inline" style="padding-left: 0px; font-size: 0.9em;">
                                By clicking the "Get Started" button the migration process will start,<br/> 
                                you will get notified of the progress and in the unlikely event that there is a problem will be contacted 
                                so that together we can find a solution that will work for you.
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-xs-12 col-sm-11">&nbsp;</div>
                        <div class="col-md-8 col-xs-12 col-sm-11 col-md-offset-0">
                            <button id="btnAccept" class="btn btn-success" type="button">
                                <i class="fa fa-check"></i>
                                Get Started
                            </button>
                            <span id="info" class="text-info" style="display:none;"><small>This might take a couple of seconds.</small></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12" id="message"></div>
                    </div>
                </form>
            </div>
        </div>
    </section>
<?php

$OUTPUT->footerStart();

?>
<!-- Our main javascript file for tool functions -->
<script>
    $(function() {
        var timeout = null;

        function hideHelp() {
            clearTimeout(timeout);
            $('#info').hide();
        }
        function showError(a) {
            $('#' + a).html('<i class="fa fa-exclamation"></i> Error').addClass('disabled').attr('disabled', true);
            $('#message').html('<p class="bg-danger">An error occurred while performing this action, please contact <a href="mailto:help@vula.uct.ac.za?subject=Vula - Please help with: Lecture Recording Setup">help@vula.uct.ac.za</a><br/> or call 021-650-5500 weekdays 8:30 - 17:00.</p>');
        }
        function doPost(a, text, type) {
            $('#' + a).html('<i class="fa fa-cog fa-spin"></i>' + text).addClass('disabled').attr('disabled', true);
            timeout = setTimeout(function(){ $('#info').show(); }, 1200);

            // var contributor = $('#presenters').val().trim().replace(/\r?\n/g, ', ');
            // var notification = $('#notifications').val().trim().replace(/\r?\n/g, ', ');

            // var data = { 
            //     "type": type,
            //     "terms": ($('#terms').is(':checked') ? "accept" : "rejected"),
            //     "visibility": ($('#coursePublic').is(':checked') ? "Public" : "Vula site only"),
            //     "subject": '',
            //     "contributor": (contributor.endsWith(', ') ? contributor.substring(0, contributor.length-2) : contributor),
            //     "course": $('#provider').val(),
            //     "notification": (notification.endsWith(', ') ? notification.substring(0, notification.length-2) : notification)
            // }

            // var jqxhr = $.post('<?= $context['submit'] ?>', data, function(result) {
            //     hideHelp();
            //     console.log(result['done'] +' '+ (result['done'] === 1));
            //     if (result['done'] === 1) {
            //         $('#' + a).html('<i class="fa fa-check"></i> Refreshing page ...');

            //         // post refresh    
            //         setTimeout(function() { parent.postMessage(JSON.stringify({ subject: "lti.pageRefresh" }), "*"); }, 3000);                    
            //     } else {
            //         showError(a);
            //     }
            // }, 'json')
            // .fail(function() {
            //     hideHelp();
            //     showError(a);
            // })
            // .always(function() {
            //     hideHelp();
            // });
        }

        $('#btnAccept').click( function(event){
            event.preventDefault();
            doPost('btnAccept', 'Starting migration...', 'create');
        });
    });
</script>
<?php

$OUTPUT->footerEnd();