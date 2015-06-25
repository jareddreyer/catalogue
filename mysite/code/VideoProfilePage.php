<?php
class VideoProfilePage extends Page
{
    private static $allowed_children = array();
    
}

class VideoProfilePage_Controller extends Page_Controller
{
    public function profile()
    {
        $this->getIMDBMetadata();
        
        $id = (int)Controller::curr()->getRequest()->param('ID');
        
        $sqlQuery = "SELECT catalogue.*, member.ID as MID, member.Email, member.FirstName, member.Surname 
                     FROM catalogue 
                     LEFT JOIN member ON catalogue.Owner = member.ID 
                     WHERE catalogue.ID = '{$id}'";

        $records = DB::query($sqlQuery);
        
        if ($records)
        {
            $set = new ArrayList();

            foreach ($records as $record)
            {
                $record['lastupdatedreadable'] = parent::humanTiming($record['Last_updated']);
                $record['seasonLinks'] = $this->seasonLinks($record['Seasons']);
               
                $set->push(new ArrayData($record));
            }
            
            return $set;
        }
        
    }

    public function createLinks (&$item, $key, $imdbID)
    { 
        return $item = '<a href="http://www.imdb.com/title/'.$imdbID.'/episodes?season='.$item.'">'. $item. '</a>';
    }    
    
    public function seasonLinks($string)
    {
        if($string != null)
        {
            $imdb = $this->getIMDBMetadata();
            
            $pattern = '/[^\d|]/';
            $numbers = preg_replace($pattern,'', $string);

            $clean = explode("|",$numbers);

            $newArray = $clean;
            array_walk($newArray, array($this, 'createLinks'), $imdbID = $imdb[0]->imdbID);
            $result = implode(' | ', $newArray);
         
            return $result;    
                
        }
    }
    
    public function getIMDBMetadata()
    {
        $id = (int)Controller::curr()->getRequest()->param('ID');
        
        $sqlQueryTitle = "SELECT Video_title
                          FROM catalogue
                          WHERE catalogue.ID = '{$id}'";

        $recordsVideoTitle = DB::query($sqlQueryTitle);
        
        if ($recordsVideoTitle)
        {
            $set2 = new ArrayList();
            foreach ($recordsVideoTitle as $title)
            {
                $url = "http://www.omdbapi.com/?t=" .  urlencode($title['Video_title']);
                $json = file_get_contents($url);
                $data = json_decode($json);
                
                if($data->{'Response'} == "False")
                {
                    $title['error'] = "video was not found in IMDB";
                    $title['errorType'] = "bad";
                    $set2->push(new ArrayData($title));
                     return $set2; 
                } else {
                    if($data->{'Poster'} != "N/A") 
                    {
						$cleanTitleName = preg_replace('/[^A-Za-z0-9\-]/', '', $title['Video_title']);
						
                        file_put_contents("c:\\inetpub\\catalogue\\assets\\Uploads\\{$cleanTitleName}.jpg", file_get_contents($data->{'Poster'}));
						$title['VideoPoster'] = "./assets/Uploads/{$cleanTitleName}.jpg";
                    } else {
                         $title['VideoPoster'] = "./assets/Uploads/blank.jpg";
                    }
                    
                    $title['errorType'] = "hide";
                    
                    $title['Year'] = $data->{'Year'};
                    $title['Director'] = $data->{'Director'};
                    $title['Actors'] = $data->{'Actors'};
                    $title['Plot'] = $data->{'Plot'};
                    $title['Runtime'] = $data->{'Runtime'};
                    $title['imdbID'] = $data->{'imdbID'};
                    
                    $set2->push(new ArrayData($title));                    
                }
                
            }
            return $set2;
        }  
    }
}
