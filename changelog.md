# 2016 #
# August 31
# Added 
  - Added 4 new fields to the catalogue table
    - "Year" (release date of title from IMDB) - read/display only
    - "imdbID" - IMDBs ID, used on the OMDB API lookup
    - "Poster" - allows title to be saved as a file friendly name so can load poster locally instead of from API
    - "Keywords" - this allows for searching on titles that are part of trilogies or part of a canon-universe e.g. "James Bond", "Marvel" (Up to user on how they want to tag i.e. users can tag via "Actor")
  - Added look up and saving of title posters on insert form
  - Added thumbnails to catalogue listing
# Removed 
  - removed table layout for listing
# Changed 
  - Comments now allow for multiple comments and contain a prefix of user and datetime
  - Video title is the only mandatory field, once filled out a look up to IMDB is made for metadata and also control of other fields i.e. Title=Star Wars, Type=Film, Source=Bluray/DVD/SCR/CAM/VOD/WEB	
  - listing now an unordered list for both Films and Series (tv)
  - Catalogue listings are now broken into profiles by user e.g. /films/ takes you to your profile, /films/x (where x is member ID) will take you to that users profile catalogue
  - Catalogue is now "Films" &  "Series" as opposed to "Movies" & "TV", "Series" leaves any ambiguities for Television or Web
