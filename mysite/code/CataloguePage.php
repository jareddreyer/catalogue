<?php
class CataloguePage extends Page
{
    
}

class CataloguePage_Controller extends Page_Controller 
{
    
    private static $allowed_actions = array('Form', 'edit');
    
    public function Form()
    {
        $action = Controller::curr()->getRequest()->param('Action');
        $id = (int)Controller::curr()->getRequest()->param('ID');
        $automap = ($id)? $automap = Catalogue::get()->byID($id) : false;
      
        $submitCaption = ($automap) ? 'Update' : 'Add'; 
        
        // Create fields
        $fields = FieldList::create(
            TextField::create('Video_title', 'Video Title'),
            DropDownField::create('Video_type', 'Type of Video', array(
                'movie' => 'Movie',
                'TV' => 'TV',
                'web' => 'Web Series',
            )),
            TextField::create('Genre', 'Genre')->setDescription('Select a genre by typing a keyword e.g. Comedy'),
            TextField::create('Seasons', 'Seasons')->setDescription('Select a Season or type Seasons owned e.g. Season 1'),
            DropDownField::create('Status', 'Current Status of download', array(
                'Downloaded' => 'Downloaded - file complete',
                'Downloading' => 'Dowloading - in progress',
                'Wanted' => 'Wanted - need a copy of',
                'No Torrents' => 'No Torrents - cannot find video',
            )),
            DropDownField::create('Source', 'Source of download', array(
                'Bluray' => 'Bluray',
                'DVD' => 'DVD',
                'HDTV' => 'High Def TV',
                'TV' => 'Standard Def TV',
                'HDweb' => 'High Def Web file',
                'SDweb' => 'Standard Def Web file'
            )),
            DropDownField::create('Quality', 'Resolution of download (quality)', array(
                '1080p' => '1080p',
                '720p' => '720p',
                '420p' => '420p',
                '320p' => '320p'
            )),
            HiddenField::create('Owner', '', Member::currentUserID()),
            TextField::create('Comments'),
            TextField::create('Wanted_by', 'Wanted By'),
            HiddenField::create('ID', 'ID')->setValue($id) 
            
        );

        $actions = FieldList::create( 
             FormAction::create('submit', $submitCaption) 
        );

        $validator = RequiredFields::create('Video_title');
        $form = Form::create($this, 'Form', $fields, $actions, $validator);

        if ($automap) $form->loadDataFrom($automap);

        return $form;
    }

    public function submit($data, $form)
    { 
        $automap = Catalogue::create();
        $form->saveInto($automap);
        $automap->ID = $data['ID'];
        $automap->Last_updated = SS_Datetime::now()->format('Y-m-d H:i:s');

        $id = $automap->write();
        Session::set("video", $data['Video_title']);
        
        if ($id)
        { 
             Session::setFormMessage($form->FormName(), $data['Video_title'], 'good', " - has been saved to the catalogue.");
        } else {
            Session::setFormMessage($form->FormName(), 'Something went wrong...', 'bad');

        }    
           
        $this->redirect($this->Link() . "edit/$id"); 

    }
    
} 

