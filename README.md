:warning: Until **July 15, 2020** the Delicious website is down, this tool will not working anymore. 

---

# Backup your favorites from De.licio.us
This code help you to export your Delicious bookmarks in different file formats.

## Why
During a long time Delicious was blocking the data exportation (to keep us traped maybe...) then I needed a solution, so I built it. ~~Now you can do it [here](https://del.icio.us/settings/bookmarks/export) (login needed).~~ _(nope, the website is down)_

## Working demo
https://scrap-delicious.herokuapp.com

## How it works
This code will parse page's HTML one by one to retrieve your data and build a file with the extracted data.
* Put the .php files on a web server and run index.php
* ( Currently [Simple HTML DOM Parser](https://sourceforge.net/projects/simplehtmldom/files/) is already included in the repo )
* On the page, enter your username and click on the button to run the script
* **Fetch all pages can take a while**

**I'm not comfortable with code, how can I do?** Use the working online demo ;)

## Download formats
You can download 2 differents output:
* **Type 1** is built with `<ul><li>`s and used by browsers and main bookmarks manager.
* **Type 2** is built with `<DL><DT>`s and used by Instapaper or Pocket.

