#Backup your favorites from Delicious
This code help you to export your Delicious bookmarks in different file formats.

##Why
Because Delicious blocks the data exportation (to keep our customer of course...) then we need to find solution.

##Working demo
https://scrap-delicious.herokuapp.com

##How it works
This code will parse page's HTML content to retrieve your data and let you download them.
* Put the .php files on a web server and run index.php
* ( Currently [Simple HTML DOM Parser](https://sourceforge.net/projects/simplehtmldom/files/) is already included in the repo )
* On the page, enter your username and click on the button to run the script
* :warning: Fetch all pages can take a while

**I'm not comfortable with code, how can I do?** Use the working online demo ;)

##Download formats
You can download 2 differents HTML:
* **Type 1** is built with `<ul><li>`s and used by browsers and main bookmarks manager.
* **Type 2** is built with `<DL><DT>`s and used by Instapaper or Pocket.
