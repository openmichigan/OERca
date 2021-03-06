<?php
/**
 * Controller for Content Object types List
 *
 * @package	OER Tool		
 * @author  Michael Bleed <mbleed@umich.edu>
 * @date    april 2nd 2009
 */

class Cotypes extends Controller {

	/**
	 * Default constructor
	 */
	public function __construct()
	{
		parent::Controller();	
		$this->load->model('coobject');
	}
	
	
	/**
	 * Loads a view that lists content objects by type
	 * defaults to id = 0 which does not exist, so we initially load an empty dataset
	*/
	public function index($cotype = 0)
	{
			$co_types = $this->coobject->coobject_types();
			$cos =  $this->coobject->coobjects_by_type($cotype);
			$count = sizeof($cos);
			$data = array('cos'=>$cos,'count'=>$count,'co_types'=>$co_types, 'co_type_selected'=>$cotype);
    		$this->load->view('cotypes', $data);
	}
}
?>