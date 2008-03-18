<?php
/**
 * @package	OCW Tool		
 * @author David Hutchful <dkhutch@umich.edu>
 * @date 1 September 2007
 * @copyright Copyright (c) 2006, University of Michigan
 */

class Coobject extends Model 
{
	public function __construct()
	{
		parent::Model();
	}

	/**
     * Get objects for a given material 
     *
     * @access  public
     * @param   int	material id		
     * @param   int	object name 
     * @param   string	action type 
     * @param   string	details 
     * @return  array
     */
	public function coobjects($mid, $oname='', $action_type='', $details='*')
	{
		$objects = array();
		$where = "material_id=$mid";

		$action_type = ($action_type == 'Any') ? '' : $action_type;

		if ($action_type=='AskRCO') {
				$replacements = $this->replacements($mid,'','', $action_type='Ask');
				if ($replacements != null) {
						$where .= " AND id IN (";
						$robj = array();
						foreach($replacements as $o) {
										if (!in_array($o['object_id'],$robj)) { 
												$where .= ((sizeof($robj)) ? ', ':'') . $o['object_id'];
														array_push($robj, $o['object_id']); 
										}
						}
						$where .= ")";
				} 
				$action_type == '';

		} else {
			if ($action_type <> '') { 
				switch ($action_type) {
					case 'Ask': $idx = 'ask'; $ans = 'yes'; break;
					case 'Done': $idx = 'done'; $ans = '1'; break;
					default: $idx = 'action_type'; $ans = $action_type;
				}
				$where .= " AND $idx='$ans'";
			}
		}

		$this->db->select($details)->from('objects')->where($where);
		$q = $this->db->get();

		if ($q->num_rows() > 0) {
			foreach($q->result_array() as $row) {
				if ($oname <> '') {
					if ($oname == $row['name']) {
						$row['comments'] = $this->comments($row['id'],'user_id,comments,modified_on');
						$row['questions'] = $this->questions($row['id'],'id,user_id,question,answer,modified_on');
						$row['copyright'] = $this->copyright($row['id']);
						$row['log'] = $this->logs($row['id'],'user_id,log,modified_on');
						array_push($objects, $row);
					}
				} else {
					$row['comments'] = $this->comments($row['id'],'user_id,comments,modified_on');
					$row['questions'] = $this->questions($row['id'],'id,user_id,question,answer,modified_on');
					$row['copyright'] = $this->copyright($row['id']);
					$row['log'] = $this->logs($row['id'],'user_id,log,modified_on');
					array_push($objects, $row);
				}
			}
		} 

		return (sizeof($objects) > 0) ? $objects : null;
	}


	/**
     * Get objects replacements for a given material 
     *
     * @access  public
     * @param   int	material id		
     * @param   int	object id		
     * @param   int	replacement id 
     * @param   string	action type 
     * @param   string	details 
     * @return  array
     */
	public function replacements($mid,$oid='', $rid='', $action_type='', $details='*')
	{
		$objects = array();
		$where = array('material_id' => $mid);
		if ($oid <> '') { $where['object_id'] = $oid; }
		if ($rid <> '') { $where['id'] = $rid; }

		$action_type = ($action_type == 'Any') ? '' : $action_type;
		if ($action_type <> '') { 
			switch ($action_type) {
				case 'Ask': $idx = 'ask'; $ans = 'yes'; break;
				default: $idx = 'action_type'; $ans = $action_type;
			}
			$where[$idx] = $ans; 
		}
		$this->db->select($details)->from('object_replacements')->where($where);
		$q = $this->db->get();

		if ($q->num_rows() > 0) {
			foreach($q->result_array() as $row) {
				$row['comments'] = $this->comments($row['id'],'user_id,comments,modified_on','replacement');
				$row['questions'] = $this->questions($row['id'],'id,user_id,question,answer,modified_on','replacement');
				$row['copyright'] = $this->copyright($row['id'],'*','replacement');
				$row['log'] = $this->logs($row['id'],'user_id,log,modified_on','replacement');
				array_push($objects, $row);
			}
		} 

		return (sizeof($objects) > 0) ? $objects : null;
	}


	public function num_objects($mid,	$action_type='')
	{
		$action_type = ($action_type == 'Any') ? '' : $action_type;
		
		if ($action_type == 'AskRCO') {
				$table = 'object_replacements';
				$where['Ask'] = 'yes'; 
		} else {
				if ($action_type <> '') { 
						switch ($action_type) {
								case 'Ask': $idx = 'ask'; $ans = 'yes'; break;
								case 'Done': $idx = 'done'; $ans = '1'; break;
								default: $idx = 'action_type'; $ans = $action_type;
						}
						$where[$idx] = $ans; 
				}
				$table = 'objects';
		}		

		$where['material_id'] = $mid;				
		$this->db->select("COUNT(*) AS c")->from($table)->where($where);		
		$q = $this->db->get();
		$row = $q->result_array();
		return $row[0]['c'];
	}

	public function object_stats($mid)
	{
		$stats = array();
		$stats['total'] = $this->num_objects($mid);
		$stats['ask'] = 0;
		$stats['cleared'] = 0;
		
		$q = $this->db->query("SELECT COUNT(*) AS c FROM ocw_objects WHERE material_id=$mid AND ask='yes'");
		$row = $q->result_array();
		$stats['ask'] = $row[0]['c'];
		
		$q = $this->db->query("SELECT COUNT(*) AS c FROM ocw_object_replacements WHERE material_id=$mid AND ask='yes'");
		$row = $q->result_array();
		$stats['ask'] += $row[0]['c'];

		$q = $this->db->query("SELECT COUNT(*) AS c FROM ocw_objects WHERE material_id=$mid AND done='1'");
		$row = $q->result_array();
		$stats['cleared'] = $row[0]['c'];
		
		$q = $this->db->query("SELECT COUNT(*) AS c FROM ocw_object_replacements WHERE material_id=$mid AND ask_status='done'");
		$row = $q->result_array();
		$stats['cleared'] += $row[0]['c'];

		$q = $this->db->query("SELECT action_type, COUNT(*) AS c FROM ocw_objects WHERE material_id=$mid GROUP BY action_type");
		if ($q->num_rows() > 0) {
			foreach($q->result_array() as $row) { $stats[$row['action_type']] = $row['c']; }
		} 
	
		return $stats;
	}

	/**
     * Get comments  for an ip objects 
     *
     * @access  public
     * @param   int	oid ip object id		
     * @param   string details fields to return	
     * @param   string type either original object or replacement 
     * @return  array
     */
	public function comments($oid, $details='*', $type='original')
	{
		$comments = array();
		$table = ($type == 'original') ? 'object_comments' : 'object_replacement_comments';
		$this->db->select($details)->from($table)->where('object_id',$oid)->orderby('modified_on DESC');
		$q = $this->db->get();

		if ($q->num_rows() > 0) {
			foreach($q->result_array() as $row) {
				array_push($comments, $row);
			}
		} 

		return (sizeof($comments) > 0) ? $comments : null;
	}

	/**
     * Get questions  for an ip objects 
     *
     * @access  public
     * @param   int	oid ip object id		
     * @param   string details fields to return	
     * @param   string type either original object or replacement 
     * @return  array
     */
	public function questions($oid, $details='*', $type='original')
	{
		$questions = array();
		$table = ($type == 'original') ? 'object_questions' : 'object_replacement_questions';
		$this->db->select($details)->from($table)->where('object_id',$oid)->orderby('modified_on DESC');
		$q = $this->db->get();

		if ($q->num_rows() > 0) {
			foreach($q->result_array() as $row) {
				array_push($questions, $row);
			}
		} 

		return (sizeof($questions) > 0) ? $questions : null;
	}

	/**
     * Get copyright info  for an ip objects 
     *
     * @access  public
     * @param   int	oid ip object id		
     * @param   string details fields to return	
     * @param   string type either original object or replacement 
     * @return  array
     */
	public function copyright($oid, $details='*', $type='original')
	{
		$cp = array();
		$table = ($type == 'original') ? 'object_copyright' : 'object_replacement_copyright';
		$this->db->select($details)->from($table)->where('object_id',$oid);
		$q = $this->db->get();

		if ($q->num_rows() > 0) {
			foreach($q->result_array() as $row) {
				array_push($cp, $row);
			}
		} 

		return (sizeof($cp) > 0) ? $cp[0] : null;
	}


	/**
     * Get log  for an ip objects 
     *
     * @access  public
     * @param   int	oid ip object id		
     * @param   string details fields to return	
     * @param   string type either original object or replacement 
     * @return  array
     */
	public function logs($oid, $details='*', $type='original')
	{
		$log = array();
		$table = ($type == 'original') ? 'object_log' : 'object_replacement_log';
		$this->db->select($details)->from($table)->where('object_id',$oid)->orderby('modified_on DESC');
		$q = $this->db->get();

		if ($q->num_rows() > 0) {
			foreach($q->result_array() as $row) {
				array_push($log, $row);
			}
		} 

		return (sizeof($log) > 0) ? $log : null;
	}

	/**
     * Add an object comment
     *
     * @access  public
     * @param   int object id
     * @param   int user id
     * @param   array data 
     * @param   string type either original object or replacement 
     * @return  void
     */
	public function add_comment($oid, $uid, $data, $type='original')
	{
		$data['object_id'] = $oid;
		$data['user_id'] = $uid;
		$data['created_on'] = date('Y-m-d h:i:s');
		$data['modified_on'] = date('Y-m-d h:i:s');
		$table = ($type == 'original') ? 'object_comments' : 'object_replacement_comments';
		$this->db->insert($table,$data);
	}

	/**
     * Add a question 
     *
     * @access  public
     * @param   int object id
     * @param   int user id
     * @param   array data 
     * @param   string type either original object or replacement 
     * @return  void
     */
	public function add_question($oid, $uid, $data, $type='original')
	{
		$data['object_id'] = $oid;
		$data['user_id'] = $uid;
		$data['created_on'] = date('Y-m-d h:i:s');
		$data['modified_on'] = date('Y-m-d h:i:s');
		$table = ($type == 'original') ? 'object_questions' : 'object_replacement_questions';
		$this->db->insert($table,$data);
	}

	/**
     * update a question 
     *
     * @access  public
     * @param   int object id
     * @param   int user id
     * @param   array data 
     * @param   string type either original object or replacement 
     * @return  void
     */
	public function update_question($oid, $qid, $data, $type='original')
	{
	  $table = ($type == 'original') ? 'object_questions' : 'object_replacement_questions';
	  $this->db->update($table,$data,"id=$qid AND object_id=$oid");
	}

	/**
     * Add an object copyright
     *
     * @access  public
     * @param   int object id
     * @param   array data 
     * @param   string type either original object or replacement 
     * @return  void
     */
	public function add_copyright($oid, $data, $type='original')
	{
		$data['object_id'] = $oid;
		$table = ($type == 'original') ? 'object_copyright' : 'object_replacement_copyright';
		$this->db->insert($table,$data);
	}

	 /**
     * update copyright 
     *
     * @access  public
     * @param   int object id
     * @param   array data 
     * @param   string type either original object or replacement 
     * @return  void
     */
	public function update_copyright($oid, $data, $type='original')
	{
	  $table = ($type == 'original') ? 'object_copyright' : 'object_replacement_copyright';
	  $this->db->update($table,$data,"object_id=$oid");
	}

	 /**
     * check to see if a copyright record already exists 
     *
     * @access  public
     * @param   int object id
     * @param   string type either original object or replacement 
     * @return  boolean
     */
	public function copyright_exists($oid,$type='original')
	{
	  $table = ($type == 'original') ? 'ocw_object_copyright' : 'ocw_object_replacement_copyright';
		$q = $this->db->query("SELECT COUNT(*) AS n FROM $table WHERE object_id=$oid"); 
		$row = $q->result_array();
		return ($row[0]['n'] > 0) ? true : false;
	}

	/**
     * Add an object log
     *
     * @access  public
     * @param   int object id
     * @param   int user id
     * @param   array data 
     * @param   string type either original object or replacement 
     * @return  void
     */
	public function add_log($oid, $uid, $data, $type='original')
	{
		$data['object_id'] = $oid;
		$data['user_id'] = $uid;
		$data['created_on'] = date('Y-m-d h:i:s');
		$data['modified_on'] = date('Y-m-d h:i:s');
		$table = ($type == 'original') ? 'object_log' : 'object_replacement_log';
		$this->db->insert($table,$data);
	}


	/**
     * Add an object
     *
     * @access  public
     * @param   int course id
     * @param   int material id
     * @param   int user id
     * @param   array data 
     * @return  void
     */
	public function add($cid, $mid, $uid, $data, $files)
	{
		// check for slides and get any data embedded in the file
		if (is_array($files['userfile_0'])) {
				$filename = $files['userfile_0']['name'];
				$tmpname = $files['userfile_0']['tmp_name'];
				$data = $this->prep_data($cid, $mid, $data, $filename, $tmpname);
				if ($data=='slide') { return true; }
		}
		
		$data['material_id'] = $mid;
		$data['modified_by'] = $uid;
		$data['done'] = '0';
		$data['status'] = 'in progress';
		$data['name'] = $this->get_next_name($cid, $mid);

		$name = $data['name'];
		$comment = $data['comment'];
		$question = $data['question'];

		$copy = array('status' => $data['copystatus'],
								  'holder' => $data['copyholder'],
								  'url' => $data['copyurl'],
								  'notice' => $data['copynotice']);
		unset($data['comment']);
		unset($data['co_request']);
		unset($data['question']);
		unset($data['copystatus']);
		unset($data['copyholder']);
		unset($data['copyurl']);
		unset($data['copynotice']);

		// add new object
		$this->db->insert('objects',$data);
		$oid = $this->db->insert_id();

		// add  questions and comments
		if ($question <> '') {
			$this->add_question($oid, getUserProperty('id'), array('question'=>$question));
		}
		if ($comment <> '') {
			$this->add_comment($oid, getUserProperty('id'), array('comments'=>$comment));
		}
		
	 if ($copy['status']<>'' or $copy['holder']<>'' or
			 $copy['notice']<>'' or $copy['url']<>''){
			 $this->add_copyright($oid,$copy);
		}

		// add files
		if (is_array($files['userfile_0'])) {
			$type = $files['userfile_0']['type'];
			$tmpname = $files['userfile_0']['tmp_name'];
			$path = $this->prep_path($name);

			  $ext = '';
  			switch (strtolower($type))
        	{
                case 'image/gif':  $ext= '.gif'; break;
                case 'jpg':
                case 'image/jpeg': $ext= '.jpg'; break;
                case 'image/png':  $ext= '.png'; break;
                default: $ext='.png';
        	}

			// move file to new location
			if (is_uploaded_file($tmpname)) {
					move_uploaded_file($tmpname, $path.'/'.$name.'_grab'.$ext);
			} else {
					copy($tmpname, $path.'/'.$name.'_grab'.$ext);
					unlink($tmpname);
			}
		}
	
		return $oid;
	}

	/**
     * Add a replacement object
     *
     * @access  public
     * @param   int course id
     * @param   int material id
     * @param   int object id
     * @param   array data 
     * @return  void
     */
	public function add_replacement($cid, $mid, $objid, $data, $files)
	{
		// check for slides and get any data embedded in the file
		if (is_array($files['userfile_0'])) {
				$filename = $files['userfile_0']['name'];
				$tmpname = $files['userfile_0']['tmp_name'];
				$data = $this->prep_data($cid, $mid, $data, $filename, $tmpname);
				if ($data=='slide') { return true; }
		}
					
		$data['material_id'] = $mid;
		$data['object_id'] = $objid;
		$data['name'] = "c$cid.m$mid.o$objid";

		$name = $data['name'];
		$comment = $data['comment'];
		$question = $data['question'];

		$copy = array('status' => $data['copystatus'],
								  'holder' => $data['copyholder'],
								  'url' => $data['copyurl'],
								  'notice' => $data['copynotice']);
		unset($data['comment']);
		unset($data['question']);
		unset($data['copystatus']);
		unset($data['copyholder']);
		unset($data['copyurl']);
		unset($data['copynotice']);

		// add new object
		$this->db->insert('object_replacements',$data);
		$oid = $this->db->insert_id();

		// add  questions and comments
		if ($question <> '') {
			$this->add_question($oid, getUserProperty('id'), array('question'=>$question),'replacement');
		}
		if ($comment <> '') {
			$this->add_comment($oid, getUserProperty('id'), array('comments'=>$comment),'replacement');
		}
		
	 if ($copy['status']<>'' or $copy['holder']<>'' or
			 $copy['notice']<>'' or $copy['url']<>''){
			 $this->add_copyright($oid,$copy,'replacement');
		}

		// add files
		if (is_array($files['userfile_0'])) {
			$type = $files['userfile_0']['type'];
			$tmpname = $files['userfile_0']['tmp_name'];

			$path = $this->prep_path($name);

			$ext = '';
  		switch (strtolower($type))
       	{
               case 'image/gif':  $ext= '.gif'; break;
               case 'jpg':
               case 'image/jpeg': $ext= '.jpg'; break;
               case 'image/png':  $ext= '.png'; break;
               default: $ext='.png';
       	}

			// move file to new location
			if (is_uploaded_file($tmpname)) {
					move_uploaded_file($tmpname, $path.'/'.$name.'_rep'.$ext);
			} else {
					copy($tmpname, $path.'/'.$name.'_rep'.$ext);
					unlink($tmpname);
			}
		}
	
		return $oid;
	}


  // add content objects with data embedded in the file metadata
  public function add_zip($cid, $mid, $uid, $files)
  {
    if (is_array($files['userfile']) and $files['userfile']['error']==0) {
        $zipfile = $files['userfile']['tmp_name'];
        $files = $this->ocw_utils->unzip($zipfile, property('app_co_upload_path')); 
				$replace_info = $orig_info = array();

        if ($files !== false) {
            foreach($files as $newfile) {
							if (preg_match('/\.jpg$/',$newfile)) {
									if (preg_match('/Slide\d+|\-pres\.\d+/',$newfile)) { // find slides

											$this->add_slide($cid,$mid,$newfile);

									} else {
											$objecttype = (preg_match('/.*?(\d+)R\.jpg/',$newfile)) ? 'RCO' : 'CO';				
	                    $filedata = array('userfile_0'=>array());
	                    $filedata['userfile_0']['name'] = basename($newfile);
                      $filedata['userfile_0']['type'] = 'image/jpeg';
                      $filedata['userfile_0']['tmp_name'] = $newfile;
																							
											if (!preg_match('/^\./',basename($newfile))) {
                   				if ($objecttype=='CO') {
		                     		$oid = $this->add($cid, $mid, $uid, array(), $filedata);
														$repfile = preg_replace('/\.jpg$/','R.jpg',$newfile);

														# go through and see if any replacement items are waiting to be inserted
														if (in_array($repfile, array_keys($replace_info))) {
																// replacement exists and has been processed so just add it!
																$filedata = $replace_info[$repfile];
		                     				$rid = $this->add_replacement($cid, $mid, $oid, array(), $filedata);
																unset($replace_info[$repfile]);
												
														} else { # place in queue just in case the replacement comes along later
																$orig_info[$newfile] = $oid;
														}

												} elseif ($objecttype=='RCO') {
														// this is a replacement - we have to make sure the original has been added
														// first before we add this. Otherwise, add it to a queue
														$origfile = preg_replace('/R.jpg$/','.jpg',$newfile);

														# go through and see if any original item has been inserted alredy 
														if (in_array($origfile, array_keys($orig_info))) {
																// original exists and has been processed so just add replacement 
																$oid = $orig_info[$origfile];
		                     				$rid = $this->add_replacement($cid, $mid, $oid, array(), $filedata);
																unset($orig_info[$origfile]);
												
														} else { # place in queue just in case the original comes along later
																$replace_info[$newfile] = $filedata;
														}
												}		
	                   }
									}
							} else {
									// ignore: file is not a jpeg -- we need to know how to handle other filetypes.
							}
						 }		
				 } else {
				     // zip file did not contain any jpg files
				 }
       
    } else {
			exit('Cannot upload file: an error occurred while uploading file. Please contact administrator.');
    }
  }

  /**
    * remove material based on information given
    * 
	  * TODO: remove materials and related objects from harddisk
    */
  public function remove_object($cid, $mid, $oid)
  {
		# remove material objects and related info
		$this->db->select('id')->from('object_replacements')->where("object_id='$oid'");

		$replacements = $this->db->get();

		if ($replacements->num_rows() > 0) {
				foreach($replacements->result_array() as $row) {
								$this->remove_replacement($row['id'],$row['name']);
				}
		}

		# remove object and it's related info 
		$this->db->delete('object_questions', array('object_id'=>$oid));
		$this->db->delete('object_comments', array('object_id'=>$oid));
		$this->db->delete('object_copyright', array('object_id'=>$oid));
		$this->db->delete('object_log', array('object_id'=>$oid));
		$this->db->delete('objects', array('id'=>$oid));

		$c = ereg_replace("\.",'/',"c$cid.m$mid.o$oid");
		rmdir(property('app_uploads_path').$c);

		return true;
  }

  /**
    * remove material based on information given
    * 
	  * TODO: remove materials and related objects from harddisk
    */
  public function remove_replacement($rid, $name)
  {
			# remove replacement objects and related info 
			$this->db->delete('object_replacement_questions', array('object_id'=>$rid));
			$this->db->delete('object_replacement_comments', array('object_id'=>$rid));
			$this->db->delete('object_replacement_copyright', array('object_id'=>$rid));
			$this->db->delete('object_replacement_log', array('object_id'=>$rid));
			$this->db->delete('object_replacements', array('id'=>$rid));
	   
			$imgpath = ereg_replace("\.",'/',$name);
	   	$p_imgpath = property('app_uploads_path').$imgpath.'/'.$name.'_rep.png';
	   	$j_imgpath = property('app_uploads_path').$imgpath.'/'.$name.'_rep.jpg';

			if (file_exists($p_imgpath)) { unlink($p_imgpath); } 
			elseif (file_exists($j_imgpath)) { unlink($j_imgpath); }

			return true;
  }

	/**
     * Update objects for a given material 
     *
     * @access  public
     * @param   int	oid object ip id		
     * @param   array	data
     * @return  void
     */
	public function update($oid, $data)
	{
		$this->db->update('objects',$data,"id=$oid");
	}

	/**
     * Update replacement objects for a given material 
     *
     * @access  public
     * @param   int	replacement id		
     * @param   array	data
     * @return  void
     */
	public function update_replacement($oid, $data)
	{
		$this->db->update('object_replacements',$data,"id=$oid");
	}


	/**
     * Update object replacement image
     *
     * @access  public
     * @param   int	oid object ip id		
     * @param   array	data
     * @return  void
     */
	public function update_rep_image($cid, $mid, $oid, $files)
	{
		// check for slides and get any data embedded in the file
		$data = array();
		if (is_array($files['userfile_0'])) {
				$filename = $files['userfile_0']['name'];
				$tmpname = $files['userfile_0']['tmp_name'];
				$data = $this->prep_data($cid, $mid, array(), $filename, $tmpname);
				if ($data=='slide') { return true; }
		}

		$comment = $data['comment'];
		$question = $data['question'];
		$copy = array('status' => $data['copystatus'],
								  'holder' => $data['copyholder'],
								  'url' => $data['copyurl'],
								  'notice' => $data['copynotice']);
		unset($data['comment']);
		unset($data['question']);
		unset($data['copystatus']);
		unset($data['copyholder']);
		unset($data['copyurl']);
		unset($data['copynotice']);
		
		// don't want to overwrite old values with empty strings
		foreach($data as $k => $v) { if ($v=='') { unset($data[$k]); }}

		// update new object if need be
		if (sizeof($data) > 0) {
				$data['material_id'] = $mid;
				$data['object_id'] = $objid;
				$data['id'] = $oid;
				$this->update_replacement($oid, $data);
		}

		// add  questions and comments
		if ($question <> '') {
			$this->add_question($oid, getUserProperty('id'), array('question'=>$question),'replacement');
		}
		if ($comment <> '') {
			$this->add_comment($oid, getUserProperty('id'), array('comments'=>$comment),'replacement');
		}

	 if ($copy['status']<>'' or $copy['holder']<>'' or
			 $copy['notice']<>'' or $copy['url']<>''){
			 $this->add_copyright($oid,$copy,'replacement');
		}
		
		// add files
		$name = "c$cid.m$mid.o$oid";

		if (is_array($files['userfile_0'])) {
			$fname = $files['userfile_0']['name'];
			$type = $files['userfile_0']['type'];
			$tmpname = $files['userfile_0']['tmp_name'];

			$path = $this->prep_path($name);

			$ext = '';
  			switch (strtolower($type))
        	{
                case 'image/gif':  $ext= '.gif'; break;
                case 'jpg':
                case 'image/jpeg': $ext= '.jpg'; break;
                case 'image/png':  $ext= '.png'; break;
                default: $ext='.png';
        	}

			// move file to new location
			move_uploaded_file($tmpname, $path.'/'.$name.'_rep'.$ext);
		}
	}

	/**
     * Get Object types 
     *
     * @access  public
     * @return  array
     */
	public function object_subtypes()
	{
		$types = array();
		$sql = 'SELECT ocw_object_subtypes.*, ocw_object_types.type 
				  FROM ocw_object_subtypes, ocw_object_types
				 WHERE ocw_object_subtypes.type_id = ocw_object_types.id
				 ORDER BY ocw_object_types.type, ocw_object_subtypes.name';
		$q = $this->db->query($sql);

		if ($q->num_rows() > 0) {
			foreach($q->result_array() as $row) { $types[$row['type']][] = $row; }
		} 

		return (sizeof($types) > 0) ? $types : null;
	}

	public function prev_next($cid, $mid, $oid)
	{
		$next = '';
		$prev = '';

		$this->db->select('id, name')->from('objects')->where('material_id',$mid)->orderby('id');
		$q = $this->db->get();
		$num = $q->num_rows();
		$thisnum = $count = 0;
	
		if ($num > 0) {
			foreach($q->result_array() as $row) {
				$count++;
				if ($row['id'] == ($oid - 1)) {
					$prev = '<a href="'.site_url("materials/object_info/$cid/$mid/{$row['name']}").'">&laquo;&nbsp;Previous</a>';
				}
				if ($row['id'] == ($oid + 1)) {
					$next = '<a href="'.site_url("materials/object_info/$cid/$mid/{$row['name']}").'">Next&nbsp;&raquo;</a>';
				}
				if ($row['id'] == $oid) { $thisnum = $count; }
			}
		}
		
		$prev = ($prev=='') ? '&laquo;&nbsp;Previous' : $prev;
		$next = ($next=='') ? 'Next&nbsp;&raquo;' : $next;
		$mid = ($num > 1) ? "$thisnum of $num" : '';
		return $prev.'&nbsp;&nbsp;-&nbsp;'.$mid.'&nbsp;-&nbsp;&nbsp;'.$next; 
	}

	private function get_next_name($cid, $mid)
	{
		$name = "c$cid.m$mid.o";
		$q = $this->db->query('SELECT MAX(id) + 1 AS nextid FROM ocw_objects'); 
		$row = $q->result_array();
		return $name.$row[0]['nextid'];
	}


	public function prep_path($name)
	{
		list($c,$m,$o) = split("\.", $name);
		$path = property('app_uploads_path').$c;
		if (!is_dir($path)) { mkdir($path); chmod($path, 0777); }
		$path .= '/'.$m;
		if (!is_dir($path)) { mkdir($path); chmod($path, 0777); }
		if ($o <> 'slide') {
				$path .= '/'.$o;
				if (!is_dir($path)) { mkdir($path); chmod($path, 0777); }
		}
		return $path;
	}
	
	public function prep_data($cid,$mid,$data,$filename,$pathtofile)
	{
			if (preg_match('/Slide\d+|\-pres\.\d+/',$filename)) { // find slides
					$this->add_slide($cid,$mid,$pathtofile);
					return 'slide';
			} else {
					$filedata = $this->get_xmp_data($pathtofile);
					foreach($filedata as $k => $v) { // passed values supercede embedded ones
										if (isset($data[$k])) {
												$data[$k] = ($data[$k]=='') ? $v : $data[$k];
										} else {
												$data[$k] = $v;
										}
					}
			}
			return $data;
	}
	// add a slide
	public function add_slide($cid, $mid, $slidefile)
	{
			list($loc, $ext) = $this->get_slide_info($slidefile);
			$pid = "c$cid.m$mid.slide";
			$newpath = $this->prep_path($pid); 
			$newpath = $newpath."/c$cid.m$mid.slide_$loc.$ext";
			@copy($slidefile, $newpath); 
			@chmod($newpath,'0777');
			unlink($slidefile);
	}

	private function get_slide_info($file)
	{
		preg_match('/\.(\w+)$/', $file, $matches);
		$ext = $matches[1];

		if (preg_match('/Slide(\d+)\.\w+/',$file,$matches)) { // powerpoint 
				return array(intval($matches[1]), $ext);

		} elseif (preg_match('/\-pres\.(\d+)\.\w+/',$file,$matches)) { // keynote 
				return array(intval($matches[1]), $ext);
		} else { // return any number found
				$i = preg_match('/(\d+)/',$file,$matches); 
				return array(intval($matches[1]), $ext);
		}
	}
	
	public function get_xmp_data($newfile)
	{	
		$data = array();
	  $xmp_data = $this->ocw_utils->xmp_data($newfile);
		
		// TODO: need a more dynamic way of getting this hash
    $subtypes = array('2D'=>'1','3D'=>'2','IIllustrative'=>'12',
                      'Cartoon' => '11', 'Comp' => '9', 'Map' => '10',
                      'Medical' => '8', 'PIllustrative' => '4', 'Patient' => '3',
                      'Specimen' => '5', 'Art' => '17', 'Artifact' => '21',
                      'Chemical' => '13', 'Diagram' => '19', 'Equation' => '15',
                      'Gene' => '14', 'Logo' => '18', 'Radiology' => '6',
											 'Microscopy' => '7');

	 	$copy_status = array(''=>'unknown', 'True'=>'copyrighted',
												'False'=>'public domain');

		$yesno = array('N'=>'no', 'Y'=>'yes');

    if (isset($xmp_data['objecttype']) ) {
				# get data from xmp
        $data['ask'] = (isset($xmp_data['ask'])) ? $yesno[$xmp_data['ask']] : 'no'; 
				$data['location'] = preg_replace('/.*?(\d+)(R)?\.jpg/',"$1",$newfile);
        $data['question'] = (isset($xmp_data['question'])) ? $xmp_data['question'] : ''; 
        $data['citation'] = (isset($xmp_data['citation'])) ? $xmp_data['citation'] : 'none'; 
        $data['comment'] = (isset($xmp_data['comments'])) ? $xmp_data['comments'] : ''; 
        $data['contributor'] = (isset($xmp_data['contributor'])) ? $xmp_data['contributor'] : ''; 
        $data['description'] = (isset($xmp_data['description'])) ? $xmp_data['description'] : ''; 
        $data['tags'] = (isset($xmp_data['keywords'])) ? $xmp_data['keywords'] : ''; 
        $data['copystatus'] = (isset($xmp_data['copystatus'])) ? $copy_status[$xmp_data['copystatus']] : ''; 
        $data['copyurl'] = (isset($xmp_data['copyurl'])) ? $xmp_data['copyurl'] : ''; 
        $data['copynotice'] = (isset($xmp_data['copynotice'])) ? $xmp_data['copynotice'] : ''; 
        $data['copyholder'] = (isset($xmp_data['copyholder'])) ? $xmp_data['copyholder'] : ''; 
				if ($xmp_data['objecttype']<>'RCO') {
	          $data['subtype_id'] = $subtypes[$xmp_data['subtype']]; 
	          $data['action_type'] = (isset($xmp_data['action'])) ? $xmp_data['action'] : ''; 
				}			
		} else {
				$data['ask'] = 'no';
				if (preg_match('/.*?(\d+)(R)?\.jpg/',$newfile)) { 
					$data['location'] = preg_replace('/.*?(\d+)(R)?\.jpg/',"$1",$newfile);
				} else {
					$data['location'] = '';
				}
	      $data['citation'] = 'none'; 
	      $data['question'] = ''; 
	      $data['comment'] = ''; 
	      $data['copystatus'] =  ''; 
	      $data['copyurl'] = ''; 
	      $data['copynotice'] = ''; 
	      $data['copyholder'] =  '';
		}
  	
		return $data;
	}
	
}
?>
