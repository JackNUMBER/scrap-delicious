<?php
const BASE_URL = 'https://del.icio.us';

class ScrapDelicious
{
    private $ajax;
    private $output;
    public $username;
    public $page;
    public $total_pages;
    public $warnings = [];
    public $errors = [];
    public $bookmarks = [];
    public $source_url = [];

    public function __construct()
    {
        include('simple_html_dom.php');
    }

    public function init()
    {
        if (isset($_GET['username'])) {
            $this->username = strtolower($_GET['username']);
        }

        if (isset($_GET['page'])) {
            $this->page = $_GET['page'];
        }

        if (isset($_GET['output'])) {
            $this->output = $_GET['output'];
        }

        if (isset($_GET['ajax'])) {
            $this->ajax = $_GET['ajax'];
        }

        $args = '';
        if ($this->page && $this->page != 'all') {
            $args = [];
            $args[] = 'page=' . $this->page;
        }
        if (!empty($args)) {
            $args = '/?' . implode('&', $args);
        }

        $this->source_url = BASE_URL . '/' . $this->username . $args;

        if ($this->username) {
            if ($this->ajax) {
                $this->fetchDataAjax();
            } else {
                $this->fetchData();
            }
        }
    }

    public function fetchData()
    {
        if ($this->page == 'all') {

            for ($i = 1; $i <= $_GET['total']; $i++) {
                $source_test = $this->source_url . '?page=' . $i;
                $this->bookmarks = array_merge($this->bookmarks, $this->getPageData($source_test));
            }
        } else {
            $headers = get_headers($this->source_url);
            $http_code = substr($headers[0], 9, 3);

            if ($http_code != 200) {
                $this->errors[] = 'The user <b>' . $this->username . '</b> doesn\'t exists or has been deleted.';
                return;
            }

            $this->bookmarks = array_merge($this->bookmarks, $this->getPageData($this->source_url));
        }

        // var_dump($this->bookmarks);
    }

    private function getPageData($url)
    {
        $page_data = [];

        $source = file_get_html($url);

        $items = $source->find('.articleThumbBlockOuter');
        $this->total_pages = $source->find('.pagination', 0)->children((count($source->find('.pagination li')) - 2))->plaintext;

        foreach ($items as $item) {
            $description = null;
            if ($item->find('.thumbTBriefTxt', 0)->children(2)) {
                $description = $item->find('.thumbTBriefTxt', 0)->children(2)->plaintext;
            }

            $date = $item->find('.articleInfoPan', 0)->children(2)->plaintext;
            $date = str_replace('This link recently saved by ' . $this->username . ' on ', '', $date);
            $date = new DateTime($date);
            $date = $date->getTimestamp();

            $tags = [];
            foreach ($item->find('.tagName li') as $tag) {
                $tags[] = $tag->plaintext;
            }

            $page_data[] = [
                'title'       => $item->find('h3', 0)->plaintext,
                'url'         => $item->find('.articleInfoPan', 0)->children(0)->find('a', 0)->href,
                'domain'      => $item->find('.articleInfoPan', 0)->children(1)->plaintext,
                'date'        => $date,
                'description' => $description,
                'tags'        => $tags,
            ];
        }

        return $page_data;
    }
}

$scrap = new ScrapDelicious();
$scrap->init();
$warnings = $scrap->warnings;
$errors = $scrap->errors;
$bookmarks = $scrap->bookmarks;
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootswatch/3.3.7/flatly/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        .container, .bookmark {margin-top: 20px;}
    </style>
</head>
<body>
<div class="container">
    <?php
        if ($warnings) {
            echo '<div class="alert alert-warning" role="alert">';
            echo implode('<br>', $warnings);
            echo '</div>';
        }
    ?>

    <?php
        if ($errors) {
            echo '<div class="alert alert-danger" role="alert">';
            echo implode('<br>', $errors);
            echo '</div>';
        }
    ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Scrap Delicious</h3>
        </div>
        <div class="panel-body form-inline">
            <form method="get">
                <div class="form-group">
                  <label for="username">Username</label>
                  <input type="text" name="username" id="username" placeholder="eg: jacknumber" class="form-control" value="<?php echo $scrap->username ?>">
                  <button type="submit" class="btn btn-primary">Fetch</button>
                </div>
            </form>
        </div>
    </div>
    <?php if (!$scrap->username) { return; } ?>


    <nav class="navbar navbar-default">
        <ul class="nav navbar-nav">
            <li><a href="<?php echo $scrap->source_url ?>" target="_blank">Source</a></li>
            <li><a href="?page=all&total=<?php echo $scrap->total_pages ?>">Fetch all pages</a></li>
            <li><a href="#" class="btn-download" data-type="html1">Get HTML type 1</a></li>
        </ul>
    </nav>

    <p>
        Total pages: <?php echo $scrap->total_pages ?>
        <?php if ($scrap->page) {?>
         / Current: <b><?php echo $scrap->page ?></b>
        <?php } ?>
        <br>
        Bookmarks scrapped: <b><?php echo count($bookmarks) ?></b>
    </p>


    <?php foreach ($bookmarks as $bookmark) { ?>
        <p class="bookmark">
            <span class="h5"><span class="glyphicon glyphicon-bookmark text-info"></span>
            <b><a href="<?php echo $bookmark['url'] ?>" title="<?php echo $bookmark['url'] ?>"><?php echo $bookmark['title'] ?></a></b>
            <small><?php echo $bookmark['domain']?></small></span><br>
            <?php if ($bookmark['description']) { ?>
                <?php echo $bookmark['description'] ?><br>
            <?php } ?>
            &nbsp;
            <?php foreach ($bookmark['tags'] as $tag) { ?>
                <span class="label label-default"><?php echo $tag ?></span>

            <?php } ?>
        </p>
    <?php } ?>
</div>
<form action="download.php" method="post" class="form-download">
    <input type="hidden" name="username" value="<?php echo $scrap->username ?>">
    <input type="hidden" name="type" class="input-type">
    <input type="hidden" name="bookmarks" value="<?php echo htmlentities(serialize($bookmarks)) ?>">
</form>
<script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
<script>
$(document).ready(function() {
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
