<?php
$bookmarks = unserialize($_POST['bookmarks']);

$html= [];
$html[] = '<!DOCTYPE html>';
$html[] = '<html>';
$html[] = '<head>';
$html[] = '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
$html[] = '<title>Delicious Export - by Jack NUMBER</title>';
$html[] = '</head>';
$html[] = '<body>';
$html[] = '<h1>Delicious ' . $_POST['username'] . '</h1>';
$html[] = '<ul>';
foreach ($bookmarks as $bookmark) {
    $html[] = '<li><a href="' . $bookmark['url'] . '" time_added="' . $bookmark['date'] . '" tags="' . implode(',', $bookmark['tags']) . '">' . $bookmark['title'] . '</a>';
}
$html[] = '</ul>';
$html[] = '</body>';
$html[] = '</html>';

header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-Disposition: attachment; filename=delicious-bookmarks.html");
header("Content-Type: application/octet-stream; ");
header("Content-Transfer-Encoding: binary");

echo implode("\n", $html);
?>
