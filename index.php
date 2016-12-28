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
            <form method="get" class="js-form-fetch" data-fetch-type="page" data-fetch-page="1">
                <fieldset>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" placeholder="eg: jacknumber" class="form-control" required value="jacknumber_graph">
                        <button type="submit" class="btn btn-primary">Fetch</button>
                    </div>
                </fieldset>
            </form>
            <button class="btn js-stoploop">Stop</button>
        </div>
    </div>

    <div class="alert alert-danger js-error-panel hidden" role="alert"></div>
    <div class="result-ajax"></div>
    <div class="result-ajax">
        <!-- Scrapping -->
        <div class="js-state-scrap text-center">
            <div class="h4">
                <span class="glyphicon glyphicon-repeat loader"></span> Scrapping page <span class="js-current-page">x</span> / <span class="js-total-page">x</span>
            </div>
        </div>
        <!-- Success -->
        <div class="js-state-success text-center">
            <div class="h4">
                <span class="glyphicon glyphicon-ok text-success"></span> Finish
            </div>
            <div class="js-error-message">
                <span class="js-bookmarks-scrapped">x</span> bookmarks scrapped
            </div>
        </div>
        <!-- Error -->
        <div class="js-state-error text-center">
            <div class="h4">
                <span class="glyphicon glyphicon-remove text-danger"></span> Error
            </div>
            <div>
                An error occured.
            </div>
        </div>
        <!-- Stopped -->
        <div class="js-state-stop text-center">
            <div class="h4">
                <span class="glyphicon glyphicon-indent-left text-warning"></span> Interrupted
            </div>
            <div>You have stopped the process but you can still download the <span class="js-bookmarks-scrapped">x</span> scrapped bookmarks:</div>
        </div>
        <!-- Buttons -->
        <div class="text-center download-buttons js-download-buttons">
            <button class="btn btn-primary js-btn-download" data-fetch-type="html1" rel="tooltip" data-placement="top" title="Firefox, Pocket">HTML Type 1</button>
            <button class="btn btn-primary js-btn-download hidden" data-fetch-type="html2" rel="tooltip" data-placement="top" title="">HTML Type 2</button>
            <button class="btn btn-primary js-btn-download hidden" data-fetch-type="json" rel="tooltip" data-placement="top" title="">JSON</button>
            <br><a href="https://github.com/JackNUMBER/scrap-delicious"><small>Need more help?</a></small>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script>
$(function () {
    $("[rel='tooltip']").tooltip();

    var fetch_form = $('.js-form-fetch'),
        error_panel = $('.js-error-panel'),
        loader = '<span class="glyphicon glyphicon-repeat loader"></span>',
        total_pages,
        scrapped_bookmarks = 0,
        loop_state,
        user;

    function fetchPage(username, page){
        var current_page = page ? page : 1;
        error_panel.addClass('hidden');

        $.ajax({
            url: 'ajax.php?username=' + username + '&page=' + current_page,
            dataType: 'json',
            success: function(result) {
                console.log('success', result);
                fetchSuccess(result);
            },
            error: function(result) {
                console.log('error', result);
                fetchError(result);
            }
        });
    }

    function toggleAbleForm() {
        console.log('toggleAbleForm()', fetch_form.find('fieldset'));
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

    function fetchSuccess(result) {
        if (result.page >= total_pages) {
            loop_state = false;
            $('.result-ajax').html('<span class="glyphicon glyphicon-ok text-success"></span> Finish').append('<br><span class="js-bookmarks-scrapped">x</span> bookmarks scrapped');
            toggleAbleForm();
            updateData(result);
        } else if (result.message == 'ok'){
            updateData(result);
        } else if (result.message == 'error') {
            fetchError(result);
            $('.result-ajax').html('');
        }

        if (loop_state) {
            $('.result-ajax').html(loader + ' Scrapping page <span class="current-scrap">' + (parseInt(result.page) + 1) +'</span> / ' + total_pages);
            fetchPage(user, parseInt(result.page) + 1);
        } else {
            console.log('loop_state', loop_state);
            toggleAbleForm();
            // $('.result-ajax').html('<span class="glyphicon glyphicon-indent-left"></span> Interrupted');
        }
    }

    function fetchError(result) {
        loop_state = false;
        error_panel.html(result.errors.join('<br>'));
        error_panel.removeClass('hidden');
    }

    function updateData(result) {
        if (result.page == 1) {
            total_pages = result.total_pages;
        }
        scrapped_bookmarks += result.bookmarks.length
        $('.js-bookmarks-scrapped').text(scrapped_bookmarks);
    }

    function stopLoop() {
        console.log('set to False');
        loop_state = false;
    }

    function updateDisplay(result) {
        switch(loop_state) {
            case false:
                break;
            case 'initial':
                break;
            case 'scrap':
                break;
            case 'finish':
                break;
            case 'stop':
                break;
                break;
            case 'error':
                break;
            default:
        }
    }

    fetch_form.submit(function(e) {
        event.preventDefault();

        user = $(this).find('#username').val();

        loop_state = true;
        toggleAbleForm();

        fetchPage(user);

        $('.result-ajax').html(loader + ' Scrapping page <span class="current-scrap">1</span> / x').addClass('text-center h4');
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
