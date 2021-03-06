## What is this?
Catalogue is a film and television show catalogue listing website based upon the Silverstripe 3.x CMS framework.

## Why?
I found my friends and I were purchasing the same movies and so to stop from over purchasing tv and movies, I built a catalogue to house all of our library and thus everyone could look at a media title first to find out a) if someone owns it and b) whom owns it.

## How?
Once signed into the Silverstripe framework, a user is given a simple form to fill out. This then saves the title to the database, of which then can be looked at further by connecting to the IMDB api. This pulls back a metadata about the title.

## Requirements
1. Composer
2. SilverStripe 3.x
3. MySQL

## Installation
1. Create a new GitHub repository for your project
2. Create your project in the appropriate directory `composer create-project jareddreyer/catalogue mywebsitenamehere`
3. Update `_ss_environment.php` to reflect your environment details
4. `composer install` to compile your PHP dependencies with Composer 
6. `/dev/build?flush=all` to build your SilverStripe environment 

## Example SQL ##

```sql
INSERT INTO Catalogue (`ID`, `ClassName`, `Created`, `LastEdited`, `Video_title`, `Video_type`, `imdbID`, `Genre`, `Keywords`, `Seasons`, `Status`, `Source`, `Quality`, `Owner`, `Comments`, `Poster`)
VALUES (1, 'Catalogue', '2014-03-18 22:03:08', '2014-03-18 22:03:08', 'Breaking Bad', 'TV',  'tt0903747', 'Drama | Crime', 'Drugs , Bryan Cranston', 'Season 1 | Season 2 | Season 3 | Season 4 | Season 5', 'Downloaded', 'HDTV', '720p', '1', 'later seasons HDTV 720p', 'breakingBad.jpg');
```
## Config ##
2 configurations constants need to be set in ```/mysite/_config.php```  
Location to save Poster images:
```php
define('POSTERSDIR', ASSETS_PATH.'/Posters/');
```
Location to save JSON metadata:
```php
define('JSONDIR', ASSETS_PATH.'/Metadata/');
```
## Todo/requests ##
  - General
	  - [ ] build listing script for people to create .csv listing of movies to add (working on it).
	  - [ ] build mobile app with barcode scanner (for physical movie/tv copies) - Leon to build
	  - [x] ~~check for duplicate movies added~~
	  - [x] ~~Recently added section~~
	  - [x] ~~Recently updated section~~
	  - [ ] minify JPList CSS
	  - [ ] minify JPList javascript
	  - [ ] refactor CSS overrides
  - Search
      - [x] ~~Add tagging field to group titles if related e.g. for movie trilogies like "batman", "james bond", "xmen"~~
      - [x] ~~build search functionality~~
        - [x]  ~~pagination~~
        - [x]  ~~filtering~~
	  - [x] ~~Added field to link to other user profile catalogues~~
	  - [x] ~~fix genre not filtering results~~
  - IMDB metadata specific
      - [ ]  check if IMDB metadata is old
	    - [x] ~~save JSON result to local server~~
      - [x]  ~~fix season links to IMDB~~
      - [x]  ~~add IMDB ID field to insert/update form~~
      - [x]  ~~and build cross reference checks to IMDB for metadata on movies that aren't labelled correctly~~
      - [x]  ~~build ajax response to populate values from IMDB to database~~
  - Visual bugs
      - [x] ~~even out table column widths (OBSOLETE)~~
      - [x] ~~hide field text that is no necessary on movies e.g. "season(s):"~~
      - [x] ~~tidy up comments from database on the profile page~~
  - Insert Form
      - [x] ~~add trilogy field so titles can be related as a trilogy~~
      - [x] ~~allow editing of Source fields in edit mode of a title already in catalogue~~
	  - [x] ~~get tagit "tags" for Seasons and Genre from DB so list doesn't become obsolete~~ 

## Issues log ##
- ~~layout of login form is disrupted by recently added section~~ [*RESOLVED*]
- if user is not logged in they can still use routing to browse to catalogue-maintenance/$id and edit a title which then breaks the owner field in the catalogue table
- ~~code base would not work with 3.4 Silverstripe framework (issue with routing)~~ [*RESOLVED*]
- Member name on catalogue not changing to owner of catalogue
- Count of catalogue is not done by video type, so reports incorrect if they have no films and 1 series on the film catalogue section and vice versa.
- ~~comments on the video profile page aren't being exploded properly, so delimiters are showing~~ [*RESOLVED*]

## Change log ##
    all changes listed under changelog.md
