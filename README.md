# What is this?
Catalogue is a movie and tv show catalogue website based upon the Silverstripe CMS framework.

# Why?
I found my friends and I were purchasing the same movies and so to stop from over purchasing tv and movies, I built a catalogue to house all of our library and thus everyone could look at a media title first to know if anyone has it.

# How?
Once signed into the Silverstripe framework, a user is given a simple form to fill out. This then saves the title to the database, of which then can be looked at further by connecting to the IMDB api. This pulls back a heap of metadata about the title.

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
      - []  build ajax response to populate values from IMDB to database
  - [ ]  visual bugs
      - [x]  even out table column widths
      - [x]  hide field text that is no necessary on movies e.g. "season(s):"
