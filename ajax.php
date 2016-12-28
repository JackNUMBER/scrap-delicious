<?php
const BASE_URL = 'https://del.icio.us';
$return = [];

class ScrapDeliciousPage
{
    public $username;
    public $page;
    public $total_pages;
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
            $this->fetchData();
        } else {
            $this->errors[] = 'Username missing.';
            return;
        }
    }

    public function fetchData()
    {
        if ($this->page == 'all') {
            // old all pages scrap method
            for ($i = 1; $i <= $_GET['total']; $i++) {
                $source_test = $this->source_url . '?page=' . $i;
                $this->bookmarks = array_merge($this->bookmarks, $this->getPageData($source_test));
            }
        } else {
            if ($this->page == 1) {
                // test target website
                $headers_site = @get_headers(BASE_URL);
                if (strpos($headers_site[0],'200') === false) {
                    $this->errors[] = 'Something went wrong with the Delicious website (' . BASE_URL .').';
                    return;
                }

                // test target page
                $headers_page = @get_headers($this->source_url);
                if (strpos($headers_page[0],'200') === false) {
                    $this->errors[] = 'The user <b>' . $this->username . '</b> doesn\'t exists or has been deleted.';
                    return;
                }
            }

            $this->bookmarks = array_merge($this->bookmarks, $this->getPageData($this->source_url));
        }
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

$scrap = new ScrapDeliciousPage();
$scrap->init();
$errors = $scrap->errors;

if (!empty($errors)) {
    $return['message'] = 'error';
    $return['errors'] = $errors;
    echo json_encode($return);
    return;
}

$return['bookmarks'] = [];
$return['page'] = $scrap->page;

if ($scrap->page == 1) {
    $return['total_pages'] = $scrap->total_pages;
}

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
