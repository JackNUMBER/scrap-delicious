<?php
const BASE_URL = 'https://del.icio.us';
$return = [];

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
$errors = $scrap->errors;

if (!empty($errors)) {
    $return['message'] = 'error';
    $return['errors'] = $errors;
    echo json_encode($return);
    return;
}

$return['bookmarks'] = [];

foreach ($scrap->bookmarks as $bookmark) {
    $return['bookmarks'][] = [
        'title'       => $bookmark['title'],
        'url'         => $bookmark['url'],
        'domain'      => $bookmark['domain'],
        'date'        => $bookmark['date'],
        'description' => $bookmark['description'],
        'tags'        => $bookmark['tags'],
    ];
}

$return['message'] = 'ok';

echo json_encode($return);
?>
