#Backup your favorites from Delicious
This code help you to export your Delicious bookmarks in different file formats (only one at the moment :grimacing:).

##Why
Because Delicious blocks the data exportation (to keep our customer of course...) then we need to find solution.

##Working demo
https://scrap-delicious.herokuapp.com

##How it works
This code will scrap (parse page's HTML content) to retrieve your data and let you download them.
* Put the 3 .php files on a web server and run index.php
* ( Currently [Simple HTML DOM Parser](https://sourceforge.net/projects/simplehtmldom/files/) is already included in the repo )
* On the page, enter your username and click on the button to run the script
* You can parse a specific page adding the `page` param to the url (i.e. add /?page=3 to your url)
* :warning: Fetch all pages can take a while, be patient.

**I'm not comfortable with code, how can I do?** Use the working online demo ;)

##How to import
I'm working on different file formats, currently the _HTML Type 1_ is the most common format. You can import it in browsers, raindrop (click Pocket on the import screen).
