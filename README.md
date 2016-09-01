## What is this? ##
Catalogue is a film and television show catalogue listing website based upon the Silverstripe 3.0 CMS framework.

## Why? ##
I found my friends and I were purchasing the same movies and so to stop from over purchasing tv and movies, I built a catalogue to house all of our library and thus everyone could look at a media title first to find out a) if someone owns it and b) whom owns it.

## How? ##
Once signed into the Silverstripe framework, a user is given a simple form to fill out. This then saves the title to the database, of which then can be looked at further by connecting to the IMDB api. This pulls back a metadata about the title.

## Example SQL ##

```sql
INSERT INTO `catalogue` (`ID`, `ClassName`, `Created`, `LastEdited`, `Video_title`, `Video_type`, `Genre`, `Seasons`, `Status`, `Source`, `Quality`, `Owner`, `Comments`, `Wanted_by`, `Last_updated`) VALUES (1, 'Catalogue', '2014-03-18 22:03:08', '2014-03-18 22:03:08', 'Breaking Bad', 'TV', 'Drama | Crime', 'Season 1 | Season 2 | Season 3 | Season 4 | Season 5', 'Downloaded', 'HDTV', '720p', '1', 'later seasons HDTV 720p', NULL, '2014-03-18 22:03:08', 'Drugs , Bryan Cranston', `breakingBad.jpg`, 'tt0903747', '2008-2013';
```

## Todo/requests ##
  - General
	  - [ ] build listing script for people to create .csv listing of movies to add.
	  - [x]  check for duplicate movies added
  - Search
      - [x] Add tagging field to group titles if related e.g. for movie trilogies like "batman", "james bond", "xmen"
      - [x] build search functionality
        - [x]  pagination
        - [x]  filtering
	  - [ ] Added field to link to other user profile catalogues
	  - fix genre not filtering results
  - IMDB metadata specific
      - [ ]  check if IMDB metadata is old
      - [x]  fix season links to IMDB
      - [x]  add IMDB ID field to insert/update form
      - [x]  and build cross reference checks to IMDB for metadata on movies that aren't labelled correctly
      - [x]  build ajax response to populate values from IMDB to database
  - Visual bugs
      - [x] even out table column widths
      - [x] hide field text that is no necessary on movies e.g. "season(s):"
      - [x] build latest updates section
  - Insert Form
      - [x] allow editing of Source fields in edit mode of a title already in catalogue
	  - [ ] get tagit "tags" for Seasons and Genre from DB so list doesn't become obsolete 

## Change log ##
    all changes listed under changelog.md