# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 

## [Released] August 31, 2016
### Added
- Added 4 new fields to the catalogue table
	- "Year" (release date of title from IMDB) - read/display only
	- "imdbID" - IMDBs ID, used on the OMDB API lookup
	- "Poster" - allows title to be saved as a file friendly name so can load poster locally instead of from API
	- "Keywords" - this allows for searching on titles that are part of trilogies or part of a canon-universe e.g. "James Bond", "Marvel" (Up to user on how they want to tag i.e. users can tag via "Actor")
- Added look up and saving of title posters on insert form
- Added thumbnails to catalogue listing

### Removed
- removed table layout for listing

### Changed
- Comments now allow for multiple comments and contain a prefix of user and datetime
- Video title is the only mandatory field, once filled out a look up to IMDB is made for metadata and also control of other fields i.e. Title=Star Wars, Type=Film, Source=Bluray/DVD/SCR/CAM/VOD/WEB	
- listing now an unordered list for both Films and Series (tv)
- Catalogue listings are now broken into profiles by user e.g. /films/ takes you to your profile, /films/x (where x is member ID) will take you to that users profile catalogue
- Catalogue is now "Films" &  "Series" as opposed to "Movies" & "TV", "Series" leaves any ambiguities for Television or Web

## [Released] September 2, 2016

### Added
- added recently added screen to home page
- added select drop down to browse all catalogue profiles

### Removed
- last_updated, pointless column, seeing as  silverstripe adds its own lastEdited column

### Changed
- added constant for uploads dir path in /mysite/_config.php

## [Released] September 6, 2016

### Added
- metadata from OMDB api is now stored on local server, saves looking up every time. (still need to add date check so it periodically updates for tv shows where details may change)
- added constant for json uploads dir path in /mysite/_config.php

### Changed
- refactored code for listing array objects to the view (removed redundant preg_replace calls) and added method ```__convertAndCleanList()``` to master page controller.
- fixed issue where genres had spaces in the data-path lookup of jplist
- fiddled with css of recently added section
- numerous comment changes throughout controllers
- added "exclude(id=1) to member dataobject look up, this should remove "default admin" from drop down navigation for profiles, all site owners should create their own username before inserting media to catalogue.

## [Released] September 16, 2016

### Added
- Recently updated section on homepage (data related from 'LastEdited' field in Catalogue class.
- Security_login.ss added so homepage.ss view does not affect login screen
- homepage.css added to control various styles on homepage e.g. recently added & updated sections

### Changed
- removed div table based layout for recently added, changed to CSS solution scroller aka Netflix carousel (note: JS is still used to do actual scrolling)

## [Released] September 16, 2016
### Changed
- Fixed season links, was dropping IMDB ID, so links were not working at all.
