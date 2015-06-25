# What is this?
Catalogue is a movie and tv show catalogue listing website based upon the Silverstripe CMS framework.

# Why?
I found my friends and I were purchasing the same movies and so to stop from over purchasing tv and movies, I built a catalogue to house all of our library and thus everyone could look at a media title first to find out a) if someone owns it and b) whom owns it.

# How?
Once signed into the Silverstripe framework, a user is given a simple form to fill out. This then saves the title to the database, of which then can be looked at further by connecting to the IMDB api. This pulls back a metadata about the title.

# Example SQL

```sql
INSERT INTO `catalogue` (`ID`, `ClassName`, `Created`, `LastEdited`, `Video_title`, `Video_type`, `Genre`, `Seasons`, `Status`, `Source`, `Quality`, `Owner`, `Comments`, `Wanted_by`, `Last_updated`) VALUES (1, 'Catalogue', '2014-03-18 22:03:08', '2014-03-18 22:03:08', 'Breaking Bad', 'TV', 'Drama | Crime', 'Season 1 | Season 2 | Season 3 | Season 4 | Season 5', 'Downloaded', 'HDTV', '720p', '1', 'later seasons HDTV 720p', NULL, '2014-03-18 22:03:08');
```

test
#Change log todo/requests

  - [ ] Add tagging field to group movies for movie trilogies like "batman", "james bond", "xmen"
  - [x] build latest updates section
  - [x]  fix season links to IMDB
  - [ ]  build listing script for people to create .csv listing of movies to add.
  - [ ]  build search functionality
      - [ ]  pagination
      - [ ]  filtering
  - [ ]  check for duplicate movies added
  - [ ]  check if imdb metadata is old
      - [ ]  check if metadata exists already before fetching from IMDB
  - [ ]  add imdb ID field to insert/update form
      - [ ]  and build cross reference checks to imdb for metadata on movies that aren't labeled correctly
      - [ ]  build ajax response to populate values from IMDB to database
  - [ ]  visual bugs
      - [x]  even out table column widths
      - [x]  hide field text that is no necessary on movies e.g. "season(s):"
