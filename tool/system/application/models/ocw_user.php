<?php
/**
  * Provides access to instructor and dScribe information 
  *
  * @package	OCW Tool		
  * @author David Hutchful <dkhutch@umich.edu>
  * @date 1 September 2007
  * @copyright Copyright (c) 2006, University of Michigan
  */

class OCW_user extends Model 
{
  public function __construct()
  {
    parent::Model();
  }

    /**
     * Retrieves all records and all fields (or those passed in the $fields string)
     * from the table user. It is possible (optional) to pass the wonted fields, 
     * the query limit, and the query WHERE clause.
     *
     * @param string of fields wanted $fields
     * @param array $limit
     * @param string $where
     * @return query string
     */
	function getUsers($fields=null, $limit=null, $where=null)
	{	
    $users = array();

		($fields != null) ? $this->db->select($fields) :'';
		
    $this->db->from('users');
		
		($where != null) ? $this->db->where($where) :'';
		
		($limit != null ? $this->db->limit($limit['start'], $limit['end']) : '');
        
		$q = $this->db->get();

    if ($q->num_rows() > 0) {
      foreach($q->result_array() as $row) { $users[] = $row; }
    }

    return (sizeof($users) > 0) ? $users : null;
	}
	
	// ------------------------------------------------------------------------
  /**
    * Get a user's courses 
    *
    * @access  public
    * @param   int user id
    * @param (optional) string role
    * @return  array
    */
  public function get_courses($uid, $role = NULL)
  {
    $courses = array();
    
    $sql = "SELECT ocw_courses.*, 
      ocw_curriculums.name AS cname, 
      ocw_schools.name AS sname
      FROM ocw_courses, ocw_curriculums, ocw_schools, ocw_acl
      WHERE ocw_curriculums.id = ocw_courses.curriculum_id
      AND ocw_schools.id = ocw_curriculums.school_id
      AND ocw_acl.course_id = ocw_courses.id
      AND ocw_acl.user_id = '$uid'
      ORDER BY start_date DESC";
      
    $q = $this->db->query($sql);

    if ($q->num_rows() > 0) {
      foreach($q->result_array() as $row) { 
        $courses[$row['sname']][$row['cname']][] = $row; 
      }
    }
    
    // get the courses that have NULL curriculum ids
    $sql_no_curr_id = "SELECT ocw_courses.*,
      ocw_courses.curriculum_id AS cname,
      ocw_courses.curriculum_id AS sname
      FROM ocw_courses, ocw_acl
      WHERE ocw_courses.curriculum_id IS NULL
      AND ocw_acl.course_id = ocw_courses.id
      AND ocw_acl.user_id = '$uid'
      ORDER BY ocw_courses.start_date DESC";
    
    $q_no_curr_id = $this->db->query($sql_no_curr_id);
    
    if ($q_no_curr_id->num_rows() > 0) {
      foreach ($q_no_curr_id->result_array() as $row) {
        $courses['No School Specified']['No Curriculum Specified'][]
         = $row;
      }
    }
    
    return (sizeof($courses) > 0) ? $courses : null;
  }


  /**
    * Get dscribes for a course 
    *
    * @access  public
    * @param   int course id
    * @return  array
    */
  public function dscribes($cid)
  {
    $dscribes = array();

    $this->db->select('ocw_users.*');
    $this->db->from('acl')->where("course_id='$cid' AND ocw_acl.role = 'dscribe1'");
    $this->db->join('users','acl.user_id=users.id');

    $q = $this->db->get();

    if ($q->num_rows() > 0) {
      foreach($q->result_array() as $row) { $dscribes[] = $row; }
    }

    return (sizeof($dscribes) > 0) ? $dscribes : null;
  }

	/**
     * Add a new user 
     *
     * @access  public
     * @param   array details
     * @return  string
     */
	public function add_user($details)
	{
		$email= $details['email'];
		#$details['password']= $this->freakauth_light->_encode($email);
		$details['password']= $this->freakauth_light->_encode('ocwpass');
		$this->db->insert('users',$details);
		return $this->exists($email);
	}

  /**
    * Add a dScribe to a course 
    *
    * @access  public
    * @param   mixed	data 
    * @return  string | boolean
    */
  public function add_dscribe($cid, $data)
  {
		if ($data['name']=='') { return "Please specify name."; }
		if ($data['email']=='') { return "Please specify an email address."; }
		if ($data['user_name']=='') { return "Please specify a username."; }

		if (($u = $this->get_user($data['user_name'])) !== false) { 
				// user exists just add them to the course
				$d = array('user_id'=>$u['id'], 'course_id'=>$cid, 'role'=>$data['role']);
				$this->db->insert('acl',$d);
		} else {
				# try to add user first
				if ($this->exists($data['email'])) { 
						return "A user with this email already exists. Please pick a new one."; }

				# prep new user data
				unset($data['task']); unset($data['add_dscribe']);

				if ($this->add_user($data)) {
						$u = $this->get_user($data['user_name']);	
						if ($u) {	
							$d = array('user_id'=>$u['id'], 'course_id'=>$cid, 'role'=>$data['role']);
							$this->db->insert('acl',$d);
						}
				} else {
						return "Could not add new user. Please contact administrator";
				}
		}
		return true;
  }

  /**
    * remove a dScribe from a course 
    *
    * @access  public
    * @param   int	course id 
    * @param   int	dscribe id 
    * @return  string | boolean
    */
  public function remove_dscribe($cid, $did)
  {
		$d = array('user_id'=>$did, 'course_id'=>$cid, 'role'=>'dscribe1');
		$this->db->delete('acl',$d);
		return true;
	}

  /**
    * Get user info based on username 
    *
    * @access  public
    * @param   string	username 
    * @return  string | boolean
    */
  public function get_user($uname)
  {
    $where = array('user_name'=>$uname);
    $query = $this->db->getwhere('users', $where); 
    return ($query->num_rows() > 0) ? $query->row_array() : false;
  }

  /**
    * Get user info based on id 
    *
    * @access  public
    * @param   int	id 
    * @return  string | boolean
    */
  public function get_user_by_id($uid)
  {
    $where = array('id'=>$uid);
    $query = $this->db->getwhere('users', $where); 
    return ($query->num_rows() > 0) ? $query->row_array() : false;
  }

	/**
     * Check to see if a user already exists 
     *
     * @access  public
     * @param   string	email 
     * @return  string | boolean
     */
	public function exists($email)
	{
		$where = array('email'=>$email);
		$query = $this->db->getwhere('users', $where); 
		return ($query->num_rows() > 0) ? $query->row_array() : false;
		
	}
	
	/**
     * Check to see if a user already exists by uniqname
     *
     * @access  public
     * @param   string user_name 
     * @return  string | boolean
     */
	public function existsByUserName($user_name)
	{
		$where = array('user_name'=>$user_name);
		$query = $this->db->getwhere('users', $where);
		return ($query->num_rows() > 0) ? $query->row_array() : false;
	}

  /**
    * get user name 
    *
    * @access  public
    * @param   int	usid 
    * @return  string username 
    */
  public function username($uid)
  {
		if ($uid==null || $uid=='') {
				return false;
		} else {
    	$where = array('id'=>$uid);
    	$this->db->select('user_name')->from('users')->where($where); 
    	$query = $this->db->get();
    	$u = $query->row_array();
    	return ($query->num_rows() > 0) ? $u['user_name'] : false;
		}
  }

  /** bdr - silly fix for displaying long user_name on questions form 
    * system/application/views/default/content/materials/co/_edit_orig_status.php
    */
  public function goofyname($uid)
  {
                if ($uid==null || $uid=='') {
                                return false;
                } else {
        $where = array('id'=>$uid);
        $this->db->select('name')->from('users')->where($where);
        $query = $this->db->get();
        $u = $query->row_array();
        return ($query->num_rows() > 0) ? $u['name'] : false;
                }
  }

  /**
    * Check to see if a user has a particular role 
    *
    * @access  public
    * @param   int	uid user id		
    * @param   int	cid course id		
    * @param   string	role (instructor|dscribe1|dscribe2)		
    * @return  boolean
    */
  public function has_role($uid, $cid, $role)
  {
    $where = array('user_id'=>$uid,'course_id'=>$cid,'role'=>$role);
    $query = $this->db->getwhere('acl', $where); 
    return ($query->num_rows() > 0) ? true : false;
  }

  /**
    * Check to see if a user is a dscribe 
    *
    * @access  public
    * @param   int	uid user id		
    * @param   int	cid course id		
    * @return  boolean
    */
  public function is_dscribe($uid, $cid)
  {
    $this->db->where('user_id="'.$uid.'" AND course_id="'.$cid.
      '" AND role LIKE "dscribe%"');
    $query = $this->db->get('acl'); 
    return ($query->num_rows() > 0) ? true : false;
  }
  
  /** 
    * Get a the courses for a user identified by their user_id (uid).
    * A simplified version of get_courses() above, which returns fewer
    * values.
    *
    * @param    int uid the numerical user id for a user
    * @return   mixed array with each element containing
    *           an array with keys:
    *           'id' = course id
    *           'number' = course number
    *           'title' = title of the course
    * 
    * again possibly duplicated functionality to get a very simple list of
    * a user's courses
    * TODO: get rid of this function if it is more efficient to use the
    * get_courses function 
    * TODO: get school name and subject code, figure out sql to provide
    * results for school name and subject code as NULL if the values are
    * not defined in the DB tables
    */
  public function get_courses_simple($uid)
  {
    $courses = array();
    
    $this->db->from('courses');
    $this->db->
      join('acl', 'acl.course_id = courses.id', 'inner')->
      join('users', 'users.id = acl.user_id', 'inner');
    $this->db->where('ocw_users.id', $uid);
    $this->db->select('ocw_courses.id, ocw_courses.number, 
      ocw_courses.title');
    $q = $this->db->get();
    if ($q->num_rows() > 0) {
      foreach ($q->result() as $row) {
        $courses[] = array(
          'id' => $row->id,
          'number' => $row->number, 
          'title' => $row->title
          );
      }
    }
    return((sizeof($courses) > 0) ? $courses : NULL);
  }
  
  
  /**
    * Get individuals with the specified relationship to the given uid 
    *
    * @access   public
    * @param    int 		uid		id of known user 
    * @param    string 	type	type of relationship (instructor, dscribe1, dscribe2)	
    * @param    int 		cid 	optional course id	
	  *
    * @return   array containing the information of the related users 
    */
  public function get_users_by_relationship($uid, $type, $cid='')
  {
    $users = array();
		$urole = getUserPropertyFromId($uid, 'role');
	
		if ($urole=='') {
				return 'Error: Cannot determine user\'s role';

		} elseif($urole==$type) {
				$table = 'users'; 
				$field = 'id AS uid'; 
				$where = array('role'=>$type);

		} elseif ($urole=='dscribe1') {
				$table = ($type=='instructor') ? 'acl' : 'dscribe2_dscribe1';
				$field = ($type=='instructor') ? 'user_id AS uid' : 'dscribe2_id AS uid';
				$where = ($type=='instructor') ? array('course_id'=>$cid, 'role'=>'instructor')
																			 : array('dscribe1_id'=>$uid);

		} elseif ($urole=='dscribe2') {
				// presently, dscribe2 have no formal relationships with instructors
				if ($type=='instructor' && $cid=='') { return 'Error: cannot define a relationship between instructor and dscribe2'; }

				$field = ($type=='instructor' && $cid<>'') ? 'user_id as uid' : 'dscribe1_id AS uid';
				$table = ($type=='instructor' && $cid<>'') ? 'acl' : 'dscribe2_dscribe1';
				$where = ($type=='instructor' && $cid<>'') ? array('course_id'=>$cid,'role'=>'instructor')
																									 : array('dscribe2_id'=>$uid);

		} elseif ($urole=='instructor') {
				// presently, dscribe2 have no formal relationships with instructors
				if ($type=='dscribe2') { return 'Error: cannot define a relationship between instructor and dscribe2'; }

				$table = 'acl';
				$field = 'user_id AS uid';
				$where =  array('course_id'=>$cid, 'role'=>'dscribe1');

		} else {
				return 'Error: the current user\'s role is not recognized.';
		}

		$this->db->select($field)->from($table)->where($where);
 		$q = $this->db->get();
    
		if ($q->num_rows() > 0) { foreach ($q->result() as $row) { array_push($users, $row->uid); } }

    // only return the array if we get results, else return false
    return (sizeof($users) > 0) ? $users : false;
 }
}
?>
