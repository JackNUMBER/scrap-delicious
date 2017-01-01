<?php
session_start();
$bookmarks = isset($_SESSION["scrappedBookmarks"]) && is_array($_SESSION["scrappedBookmarks"]) ? $_SESSION["scrappedBookmarks"] : [];

switch ($_GET['output']) {
    case 'html1':
        $out = html1($bookmarks);
        $filename = 'delicious-bookmarks.html';
        break;

    case 'html2':
        $out = html2($bookmarks);
        $filename = 'delicious-bookmarks.html';
        break;

    case 'json':
        $out = json($bookmarks);
        $filename = 'delicious-bookmarks.json';
        break;

    default:
        $out = html1($bookmarks);
        $filename = 'delicious-bookmarks.html';
        break;
}

function html1($bookmarks) {
    $html = [];
    $html[] = '<!DOCTYPE html>';
    $html[] = '<html>';
    $html[] = '<head>';
    $html[] = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
    $html[] = '<title>Delicious Export - by Jack NUMBER</title>';
    $html[] = '</head>';
    $html[] = '<body>';
    $html[] = '<h1>Delicious ' . $_GET['username'] . '</h1>';
    $html[] = '<ul>';
    foreach ($bookmarks as $bookmark) {
        $html[] = '<li><a href="' . $bookmark['url'] . '" time_added="' . $bookmark['date'] . '" tags="' . implode(',', $bookmark['tags']) . '">' . $bookmark['title'] . '</a>';
    }
    $html[] = '</ul>';
    $html[] = '</body>';
    $html[] = '</html>';

    return $html;
}

function html2($bookmarks) {
    $html = [];
    $html[] = '<!DOCTYPE NETSCAPE-Bookmark-file-1>';
    $html[] = '<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">';
    $html[] = '<TITLE>Delicious Export - by Jack NUMBER</TITLE>';
    $html[] = '<H1>Delicious ' . $_GET['username'] . '</H1>';
    $html[] = '<DL><p>';
    foreach ($bookmarks as $bookmark) {
        $html[] = '<DT><A HREF="' . $bookmark['url'] . '" ADD_DATE="' . $bookmark['date'] . '" LAST_MODIFIED="' . $bookmark['date'] . '" TAGS="' . implode(',', $bookmark['tags']) . '">' . $bookmark['title'] . '</A>';
        $html[] = '<DD>' . $bookmark['description'];
    }
    $html[] = '</DL>';

    return $html;
}

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=" . $filename);
header("Content-Type: application/octet-stream; ");
header("Content-Transfer-Encoding: binary");

echo implode("\n", $out);
?>
