<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/flatly/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .container, .bookmark {margin-top: 20px;}
        .loader {color: #c0c0c0;animation: loader 1s linear infinite;-webkit-transform: translateZ(0);}
        .btn-primary .loader {color: #fff;margin: 0;}
        .result-ajax .glyphicon {vertical-align: top;margin-right: 5px;}
        .download-buttons {margin-top: 15px;}
        @keyframes loader {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Scrap Delicious</h3>
        </div>
        <div class="panel-body form-inline">
            <form method="get" class="js-form-fetch">
                <fieldset>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" placeholder="eg: jacknumber" class="form-control" required value="jacknumber_graph">
                        <button type="submit" class="btn btn-primary">Fetch</button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>

    <div class="alert alert-danger js-error-panel hidden" role="alert"></div>
    <div class="result-ajax">
        <!-- Scrapping -->
        <div class="js-state-scrap hidden text-center">
            <div class="h4">
                <span class="glyphicon glyphicon-repeat loader"></span> Scrapping page <span class="js-current-page">1</span> / <span class="js-total-page">x</span>
            </div>
            <div>
                <button class="btn js-stoploop">Stop process</button><br>
                <small class="js-stop-process-label hidden">Finsishing the current task</small>

            </div>
        </div>
        <!-- Success -->
        <div class="js-state-success hidden text-center">
            <div class="h4">
                <span class="glyphicon glyphicon-ok text-success"></span> Finish
            </div>
            <div>
                <span class="js-bookmarks-scrapped">x</span> bookmarks scrapped.
            </div>
            <div>
                You can download your bookmarks:
            </div>
        </div>
        <!-- Error -->
        <div class="js-state-error hidden text-center">
            <div class="h4">
                <span class="glyphicon glyphicon-remove text-danger"></span> Error
            </div>
            <div class="js-error-message">
                An error occured.
            </div>
        </div>
        <!-- Stopped -->
        <div class="js-state-stop hidden text-center">
            <div class="h4">
                <span class="glyphicon glyphicon-indent-left text-warning"></span> Interrupted
            </div>
            <div>You have stopped the process but you can still download the <span class="js-bookmarks-scrapped">x</span> scrapped bookmarks:</div>
        </div>
        <!-- Buttons -->
        <div class="download-buttons js-state-download js-download-buttons hidden text-center">
            <button class="btn btn-primary js-btn-download" data-fetch-type="html1" rel="tooltip" data-placement="top" title="Firefox, Pocket">HTML Type 1</button>
            <button class="btn btn-primary js-btn-download hidden" data-fetch-type="html2" rel="tooltip" data-placement="top" title="">HTML Type 2</button>
            <button class="btn btn-primary js-btn-download hidden" data-fetch-type="json" rel="tooltip" data-placement="top" title="">JSON</button>
            <br><a href="https://github.com/JackNUMBER/scrap-delicious"><small>Need help?</a></small>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script>
$(function () {
    $('[rel="tooltip"]').tooltip();

    var fetch_form = $('.js-form-fetch'),
        loader = '<span class="glyphicon glyphicon-repeat loader"></span>',
        total_pages,
        scrapped_bookmarks = 0,
        loop_state,
        user;

    function fetchPage(username, page){
        var current_page = page ? page : 1;

        $.ajax({
            url: 'ajax.php?username=' + username + '&page=' + current_page,
            dataType: 'json',
            success: function(result) {
                console.log('success', result);
                fetchSuccess(result);
            },
            error: function(result) {
                console.log('error', result);
                loop_state = 'error';
                fetchError(result);
            }
        });
    }

    function fetchSuccess(result) {
        if (result.message == 'ok') {
            if (loop_state == 'initial') {
                total_pages = result.total_pages;
                $('.js-total-page').text(total_pages);
            }

            scrapped_bookmarks += result.bookmarks.length;
            $('.js-bookmarks-scrapped').text(scrapped_bookmarks);

            if (loop_state != 'stop') {
                if (result.page >= total_pages) {
                    // end
                    loop_state = 'finish';

                } else {
                    // continue
                    loop_state = 'scrap';
                    $('.js-current-page').text(parseInt(result.page) + 1);
                    fetchPage(user, parseInt(result.page) + 1);
                }
            }

            updateDisplay(result);
        } else if (result.message == 'error') {
            loop_state = 'error';
            fetchError(result);
        }
    }

    function fetchError(result) {
        if (result.errors) {
            $('.js-error-message').html(result.errors.join('<br>'));
        }

        updateDisplay(result);
    }

    function toggleAbleForm() {
        console.log('toggleAbleForm()');
        if (fetch_form.find('fieldset').attr('disabled')) {
            fetch_form.find('fieldset').removeAttr('disabled');
            fetch_form.find('input').removeAttr('disabled');
            fetch_form.find('button').removeAttr('disabled');
        } else {
            fetch_form.find('fieldset').attr('disabled', true);
            fetch_form.find('input').attr('disabled', true);
            fetch_form.find('button').attr('disabled', true);
        }
    }

    function stopLoop() {
        loop_state = 'stop';
        $('.js-stop-process-label').removeClass('hidden');
    }

    function updateDisplay(result) {
        $('[class*="js-state-"').addClass('hidden');
        switch(loop_state) {
            case false:
                break;
            case 'initial':
                $('.js-state-scrap').removeClass('hidden');
                break;
            case 'scrap':
                $('.js-state-scrap').removeClass('hidden');
                break;
            case 'finish':
                $('.js-state-success').removeClass('hidden');
                $('.js-download-buttons').removeClass('hidden');
                toggleAbleForm();
                break;
            case 'stop':
                $('.js-state-stop').removeClass('hidden');
                $('.js-download-buttons').removeClass('hidden');
                toggleAbleForm();
                break;
            case 'error':
                $('.js-state-error').removeClass('hidden');
                toggleAbleForm();
                break;
            default:
        }
    }

    fetch_form.submit(function(e) {
        event.preventDefault();

        user = $(this).find('#username').val();

        loop_state = 'initial';
        toggleAbleForm();

        // init scrap
        scrapped_bookmarks = 0;
        $('.js-current-page').text('1');
        $('.js-total-page').text('x');
        $('.js-stop-process-label').addClass('hidden');

        updateDisplay();
        fetchPage(user);
    });

    $('.js-stoploop').on('click', function() {
        stopLoop();
    });


    $('.navbar .btn-download').on('click', function(e) {
        event.preventDefault();
        var type = $(this).attr('data-type');
        $('.form-download .input-type').val(type);
        $('.form-download').submit();
    });
});
</script>
</body>
</html>
