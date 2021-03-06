<?php
/**
 * Home Class
 *
 * @package OER Tool
 * @author Ali Asad Lotia <lotia@umich.edu>
 * @date 10 February 2008
 * @copyright Copyright (c) 2006, University of Michigan
 */
class Home extends Controller {

 /**
  * The default constructor.
  */
  public function __construct()
  {
    parent::Controller();
    $this->load->model('ocw_user');
    $this->load->model('material');
    $this->load->library('oer_progbar');
    $this->load->library('oer_layout');
    $this->load->library('navtab');
    $this->load->library('oer_manage_nav');
  }
  
  
  /**
    * Users with roles other than dscribe1 are redirected to the 
    * default home pages for their role types. Users with dscribe1
    * roles are presented with visual summaries of the current
    * state of the content clearing process.
    * Progress bars showing how many content objects have been 
    * cleared; are in progress; and have not yet been 
    * started are displayed for each of the dscribe1's assigned
    * courses.
    */
  public function index()
  {
    $this->freakauth_light->check();

    $role = getUserProperty('role');

    if ($role == 'dscribe1') {
        redirect('dscribe1/home/', 'location');
      
    } elseif ($role == 'dscribe2') {
        redirect('dscribe2/home/', 'location');

    } elseif ($role == 'instructor') {
        redirect('instructor/home/', 'location');

    } elseif ($role == 'admin') {
        redirect('admin/home/', 'location');

		} else {
        redirect('guest/home/', 'location');
    }
  }

  
  /**
    * Generates a bar chart showing the state of the IP
    * clearance of the content objects in a course.
    *
    * @param    int total number of content objects
    * @param    int number of cleared content objects
    * @param    int number of content objects that have associated 
    *            questions
    * @param    int number of content objects that need to be checked
    * @param    int width of the progress bar (in pixels)
    * @param    int - font size for text to be displayed in box
    * @return   void
    */  
  public function make_bar($total,$done,$ask,$rem)
  {
    $this->oer_progbar->build_prog_bar($total,$done,$ask,$rem, 600, 20, 10);
    $this->oer_progbar->get_prog_bar();
  }


  /**
    * Generates a colored square representing the specifed
    * Content Object status
    *
    * @param    string the Content Object status (done, ask, rem)
    * @return   void
    */
  public function make_stat_key($status)
  {
    $this->oer_progbar->build_stat_key($status);
    $this->oer_progbar->get_stat_key();
  }

  /**
    * bdr - this is for Content Object status display on the listing 
    *       or course for a dscribe2, admin, etc.   
    *       (this calls the same routing as make_bar)
    */
  public function course_bar($total,$done,$ask,$rem)
  {
    $this->oer_progbar->build_prog_bar($total,$done,$ask,$rem, 150, 20, 8);
    $this->oer_progbar->get_prog_bar();
  }

  /**
    * bdr - this is for Content Object status display on the listing
    *       or course for a dscribe2, admin, etc.   
    *       (this calls the same routing as make_bar)
    */
  public function material_bar($total,$done,$ask,$rem,$dash)
  {
		# red,0=3, green,0=2
  	//if ($dash == 0 || $dash == 3) { //OERDEV-181 mbleed: removed this if statement, no longer hardcoding mtotal=1000000 as a workaround, no need for different calls
            $this->oer_progbar->build_prog_bar($total,$done,$ask,$rem, 150, 20, 8, $dash); 	//OERDEV-181 mbleed: added $dash var to progbar call so we can process CO=0 cases properly
            $this->oer_progbar->get_prog_bar();
    //} elseif($dash == 2) { 												
    //        $this->oer_progbar->build_prog_bar(1000000,0,0,0,150,20,8,);		
    //        $this->oer_progbar->get_prog_bar();							
    //}																		
  }

}
?>
