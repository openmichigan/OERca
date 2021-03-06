<?php
/**
 * Provides access to course information
 *
 * @package     OCW Tool
 * @author David Hutchful <dkhutch@umich.edu>
 * @date 1 September 2007
 * @copyright Copyright (c) 2006, University of Michigan
 */

class Course extends Model
{

  // Instance variables used when getting course info.
  private $courses;
  private $courses_users_roles;

  public function __construct()
  {
    parent::Model();
    $this->load->model('material');
    $this->courses = array();
    $this->courses_users_roles = array();
  }

  /**
   * add course
   *
   * @access  public
   * @return  array
   */
  public function new_course($details)
  {
    $this->db->insert('courses', $details);
    $this->db->select('*')->from('courses')->where($details);
    $q = $this->db->get();
    $course = $q->row_array();
    $curr_mysql_time = $this->ocw_utils->get_curr_mysql_time();
    if ($q->num_rows() == 1) {
      $filename = $this->generate_course_name($course['title'].$course['start_date'].
					      $course['end_date']);
      $dirname = property('app_uploads_path') . 'cdir_' . $filename;
      $this->oer_filename->mkdir($dirname);
      $this->db->insert('course_files',
			array('filename'=>$filename,
			      'modified_on'=>$curr_mysql_time,
			      'created_on'=>$curr_mysql_time,
			      'course_id'=>$course['id']));
    }

    return ($q->num_rows() == 1) ? $course : null;
  }

  /**
   * remove a course and all the associated data
   *
   * @access      public
   * @param               int     course id
   * @param       boolean
   */
  public function remove_course($cid)
  {
    // Prepare to delete everything associated with this course
    $d = array('course_id' => $cid);

    // Remove all the acl connections with this course (dscribe1, dscribe2, or instructor)
    $this->db->delete('acl', $d);

    // Remove all materials associated with this course
    $this->db->select('id')->from('materials')->where("course_id=$cid");
    $q = $this->db->get();
    foreach($q->result_array() as $row) {
      $this->material->remove_material($cid, $row['id']);
    }

    // Remove all the course files associated with this course
    $cdirname = $this->course_path($cid);
    $this->ocw_utils->remove_dir($cdirname);

    // remove course_files entry from db
    $this->db->delete('course_files', array('course_id'=>$cid));

    // remove course from db
    $this->db->delete('courses', array('id'=>$cid));
  }

  /**
   * Get users (acl entries) associated with a course
   *
   * @access  public
   *       @param   int course_id
   *       @param   string user role (optional)
   *       @return  array of ocw_acl entries
   */
  public function get_course_users($cid, $role = NULL) {
    $where = ($role == NULL) ? array('course_id' => $cid)
      : array('course_id' => $cid, 'role' => $role);
    $this->db->select('*')->from('acl')->where($where);
    $q = $this->db->get();
    if ($q->num_rows() > 0) {
      foreach($q->result_array() as $row) {
	$cacls[] = $row;
      }
    }
    return ($q->num_rows() > 0) ? $cacls : null;
  }

  /**
   * Complete (dscribe2_dscribe1) relationships when adding an acl entry
   *
   * @access  private
   * @param   array details (containing course_id, user_id, and role)
   * @return  boolean
   */
  private function complete_relationships($details)
  {
    if ($details['role'] != 'dscribe1' && $details['role'] != 'dscribe2')
      return true;
    if ($details['role'] == 'dscribe1') {
      // create a relationship with existing dscribe2s
      $d2s = $this->get_course_users($details['course_id'], 'dscribe2');
      if (count($d2s) != 0) {
	foreach($d2s as $d2) {
	  $this->ocw_user->set_relationship($details['user_id'], $d2['user_id']);
	}
      }
    } elseif ($details['role'] == 'dscribe2') {
      // create a relationship with existing dscribe1s
      $d1s = $this->get_course_users($details['course_id'], 'dscribe1');
      if (count($d1s) != 0) {
	foreach($d1s as $d1) {
	  $this->ocw_user->set_relationship($d1['user_id'], $details['user_id']);
	}
      }
    }
    return true;
  }

  /**
   * This function checks all the existing acl rows and verifies that
   * the role claimed in that row matches the roll of the user pointed
   * to by the user_id field
   * Inconsistencies are logged to the apache error log
   *
   * @access private
   * @return true
   */
  private function check_for_inconsistent_acls()
  {
    $this->db->select("*")->from('acl');
    $q = $this->db->get();
    if ($q->num_rows() > 0) {
      foreach($q->result_array() as $acl) {
	$this->db->select('id, role')->from('users')->where(array('id' => $acl['user_id']))->limit(1);
	$qu = $this->db->get();
	if ($qu->num_rows() != 1) {
	  $errmsg = "check_for_inconsistent_acls: Invalid acl entry "
	    . "(user_id " . $acl['user_id']
	    . ", course_id " . $acl['course_id']
	    . ", role " . $acl['role']
	    . "), no matching user entry found";
	  $this->ocw_utils->log_to_apache('warn', $errmsg);
	}
	foreach($qu->result_array() as $u) {
	  if ($u['role'] != $acl['role']) {
	    $errmsg = "check_for_inconsistent_acls: Invalid acl entry "
	      . "(user_id " . $acl['user_id']
	      . ", course_id " . $acl['course_id']
	      . ", role " . $acl['role']
	      . "), role doesn't match user entry role (" . $u['role'] . ")";
	    $this->ocw_utils->log_to_apache('warn', $errmsg);
	    // This goes to the CodeIgniter logs: log_message('error', $errmsg);
	    // This goes to the php_error.log: error_log($errmsg, 0);
	  }
	}
      }
    }
    return true;
  }

  /**
   * add user with the role to the course
   *
   * @access  public
   * @param   array details (containing course_id, user_id, and role)
   * @return  boolean
   */
  public function add_user($details)
  {
    $this->complete_relationships($details);
    $this->db->insert('acl', $details);
    $this->check_for_inconsistent_acls();
    return true;
  }

  /**
   * remove a user from a course
   *
   * @access  public
   * @param   int      course id
   * @param   int      user id
   * @param   string user role
   * @return  string | boolean
   */
  public function remove_user($cid, $uid, $role)
  {
    // Don't allow deletion of the last user in any particular
    // role (dscribe1, dscribe2, or instructor) from a course
    $d1s = $this->get_course_users($cid, 'dscribe1');
    $d2s = $this->get_course_users($cid, 'dscribe2');
    $instrs = $this->get_course_users($cid, 'instructor');

    if ($role == 'instructor' && count($instrs) <= 1)
      return "Cannot remove the only instructor from a course";
    if ($role == 'dscribe2' && count($d2s) <= 1)
      return "Cannot remove the only dscribe2 from a course";
    if ($role == 'dscribe1' && count($d1s) <= 1)
      return "Cannot remove the only dscribe1 from a course";
    if ((count($d2s) + count($d1s) + count($instrs)) <= 1)
      return "Cannot remove the only user for a course";

    $d = array('user_id'=>$uid, 'course_id'=>$cid, 'role'=>$role);
    $this->db->delete('acl',$d);
    return true;
  }

  /**
   * Get course
   *
   * @access  public
   * @param   int     cid course id
   * @param   string  details
   * @return  string
   */
  public function get_course($cid, $details='*')
  {
    $this->db->select($details)->from('courses')->where('id',$cid);
    $q = $this->db->get();
    $course = $q->row_array();
    return ($q->num_rows() > 0) ? $course : null;
  }

  /**
   * Get course by title
   *
   * @access  public
   * @param   string title
   * @return  string
   */
  public function get_course_by_title($title, $details='*')
  {
    $this->db->select($details)->from('courses')->where('title',$title);
    $q = $this->db->get();
    $course = $q->row_array();
    return ($q->num_rows() > 0) ? $course : null;
  }

  /**
   * Get course by number
   *
   * @access  public
   * @param   string number
   * @return  string
   */
  public function get_course_by_number($number, $details='*')
  {
    $this->db->select($details)->from('courses')->where('number',$number);
    $q = $this->db->get();
    $course = $q->row_array();
    return ($q->num_rows() > 0) ? $course : null;
  }

  /**
   * Get instructor of a course by course id
   *  mbleed 08/29/08
   * @access  public
   * @param   string number
   * @return  string
   */
  public function get_course_users_by_cid($cid, $type)
  {
    if (!$cid || $type == '') {
      $this->ocw_utils->log_to_apache('error', __FUNCTION__.": called with bad parameters!");
      return '';
    }

    $sql = "SELECT ocw_users.name AS iname
                                FROM ocw_courses, ocw_acl, ocw_users
                                WHERE ocw_courses.id = ocw_acl.course_id
                                AND ocw_acl.user_id = ocw_users.id
                                AND ocw_acl.role = '$type'
                                AND ocw_courses.id = $cid
                                ORDER BY ocw_users.name ASC";
    $q = $this->db->query($sql);
    if ($q->num_rows() > 0) {
      $course_users = array();
      foreach($q->result_array() as $row) {
	$course_users[] = $row['iname'];
      }
    }
    return ($q->num_rows() > 0) ? implode("<br>", $course_users) : '';
  }

  /**
   * Get array of course user ids by type and course id
   *  mbleed faceted search
   * @access  public
   * @param   string number
   * @return  array
   */
  public function get_course_user_array_by_cid($cid, $type)
  {
    if (!$cid || $type == '') {
      $this->ocw_utils->log_to_apache('error', __FUNCTION__.": called with bad parameters!");
      return '';
    }

    $sql = "SELECT ocw_users.id AS uid
                                FROM ocw_courses, ocw_acl, ocw_users
                                WHERE ocw_courses.id = ocw_acl.course_id
                                AND ocw_acl.user_id = ocw_users.id
                                AND ocw_acl.role = '$type'
                                AND ocw_courses.id = $cid
                                ORDER BY ocw_users.name ASC";
    $q = $this->db->query($sql);
    $course_users = array();
    if ($q->num_rows() > 0) {

      foreach($q->result_array() as $row) {
	$course_users[] = $row['uid'];
      }
    }
    return $course_users;
  }

  /**
   * Get all courses
   *
   * @access  public
   * @return  array
   */
  // public function get_courses()
  // OERDEV-173  bdr - should only have a single get_courses routine
  public function get_courses($uid, $urole = NULL)
  {
    $courses = array();
    if ($urole == 'admin') {
      $uid = NULL;
      $role = 'admin';
    }

    if ($uid == NULL) {
      $sql = 'SELECT ocw_courses. *, ocw_curriculums.name AS cname,
                        ocw_schools.name AS sname,
                        ocw_courses.id AS cid
                        FROM ocw_courses, ocw_curriculums, ocw_schools
                        WHERE ocw_curriculums.id = ocw_courses.curriculum_id
                        AND ocw_schools.id = ocw_curriculums.school_id
                        ORDER BY ocw_courses.start_date DESC';
    } else {
      $sql = "SELECT ocw_courses.*,
                ocw_curriculums.name AS cname,
                ocw_schools.name AS sname,
                ocw_courses.id AS cid
                FROM ocw_courses, ocw_curriculums, ocw_schools, ocw_acl
                WHERE ocw_curriculums.id = ocw_courses.curriculum_id
                AND ocw_schools.id = ocw_curriculums.school_id
                AND ocw_acl.course_id = ocw_courses.id
                AND ocw_acl.user_id = '$uid'
                ORDER BY start_date DESC";
    }
    $q = $this->db->query($sql);

    if ($q->num_rows() > 0) {
      foreach($q->result_array() as $row) {
	$row['instructors'] = $this->get_course_users_by_cid($row['cid'], 'instructor');
	$row['dscribe1s'] = $this->get_course_users_by_cid($row['cid'], 'dscribe1');
	$row['dscribe2s'] = $this->get_course_users_by_cid($row['cid'], 'dscribe2');
	$courses[$row['sname']][$row['cname']][] = $row;
      }
    }

    // get the courses that have NULL curriculum ids
    if ($uid == NULL) {
      $sql_no_curr_id = "SELECT ocw_courses.*,
                                ocw_courses.curriculum_id AS cname,
                                ocw_courses.curriculum_id AS sname,
                                ocw_courses.id AS cid
                                FROM ocw_courses
                                WHERE ocw_courses.curriculum_id IS NULL
                                ORDER BY ocw_courses.start_date DESC";
    } else {
      $sql_no_curr_id = "SELECT ocw_courses.*,
                        ocw_courses.curriculum_id AS cname,
                        ocw_courses.curriculum_id AS sname,
                        ocw_courses.id AS cid
                        FROM ocw_courses, ocw_acl
                        WHERE ocw_courses.curriculum_id = NULL
                        AND ocw_acl.course_id = ocw_courses.id
                        AND ocw_acl.user_id = '$uid'
                        ORDER BY start_date DESC";
    }

    $q_no_curr_id = $this->db->query($sql_no_curr_id);

    if ($q_no_curr_id->num_rows() > 0) {
      foreach ($q_no_curr_id->result_array() as $row) {
	$row['instructors'] = $this->get_course_users_by_cid($row['cid'], 'instructor');
	$row['dscribe1s'] = $this->get_course_users_by_cid($row['cid'], 'dscribe1');
	$row['dscribe2s'] = $this->get_course_users_by_cid($row['cid'], 'dscribe2');
	$courses['No School Specified']['No Curriculum Specified'][] = $row;
      }
    }

    //exit($this->ocw_utils->dump($courses));
    return (sizeof($courses) > 0) ? $courses : null;
  }

  /**
   * Get  courses for faceted search - mbleed
   *
   * @access  public
   * @return  array
   */
  public function faceted_search_get_courses($uid, $school=0, $year=0, $dscribe2=0, $dscribe1=0)
  {
    $courses = array();
    //$uid = getUserProperty('id');
    $urole = getUserPropertyFromId($uid, 'role');

    //turn filters into where clauses
    $where = "";
    $where2 = "";
    if ($school > 0) {
      $where .= " AND (";
      $schools = explode("z", $school);
      foreach ($schools as $s) $schoolwheres[] = "ocw_schools.id = $s";
      $where .= implode(" OR ", $schoolwheres);
      $where .= ")";
    }
    if ($year > 0) {
      $where .= " AND (";
      $where2 .= " AND (";
      $years = explode("z", $year);
      foreach ($years as $y) $yearwheres[] = "ocw_courses.year = $y";
      $where .= implode(" OR ", $yearwheres);
      $where2 .= implode(" OR ", $yearwheres);
      $where .= ")";
      $where2 .= ")";
    }
    if ($dscribe1 > 0) {
      $where .= " AND (";
      $where2 .= " AND (";
      $dscribe1s = explode("z", $dscribe1);
      foreach ($dscribe1s as $d1) $dscribe1wheres[] = "ocw_acl.role = 'dscribe1' AND  ocw_acl.user_id =  $d1";
      $where .= implode(" OR ", $dscribe1wheres);
      $where2 .= implode(" OR ", $dscribe1wheres);
      $where .= ")";
      $where2 .= ")";
    }
    if ($dscribe2 > 0) {
      $where .= " AND (";
      $where2 .= " AND (";
      $dscribe2s = explode("z", $dscribe2);
      foreach ($dscribe2s as $d2) $dscribe2wheres[] = "ocw_acl.role = 'dscribe2' AND  ocw_acl.user_id =  $d2";
      $where .= implode(" OR ", $dscribe2wheres);
      $where2 .= implode(" OR ", $dscribe2wheres);
      $where .= ")";
      $where2 .= ")";
    }

    //course, user, role ref table
    $course_users = $this->get_course_user_roles();

    $sql = "SELECT ocw_courses.*,
                ocw_curriculums.name AS cname,
                ocw_schools.name AS sname,
                ocw_courses.id AS cid
                FROM ocw_courses, ocw_curriculums, ocw_schools, ocw_acl
                WHERE ocw_curriculums.id = ocw_courses.curriculum_id
                AND ocw_acl.user_id = $uid
                AND ocw_schools.id = ocw_curriculums.school_id
                AND ocw_acl.course_id = ocw_courses.id
                $where
                GROUP BY ocw_courses.id
                ORDER BY start_date DESC";
    $q = $this->db->query($sql);
    if ($q->num_rows() > 0) {
      foreach($q->result_array() as $row) {
	$instructors_html = '';
	foreach ($course_users[$row['cid']] as $cu) if ($cu['role'] == 'instructor') $instructors_html .= $cu['name']."<br>";
	$row['instructors'] = $instructors_html;
	/*
	  if ($dscribe2 > 0) {
	  if (in_array($dscribe2, $course_users[$row['cid']], 'dscribe2'))) $showrowdscribe2 = true;
	  else $showrowdscribe2 = false;
	  }
	  if ($dscribe > 0) {
	  if (in_array($dscribe, $this->get_course_user_array_by_cid($row['cid'], 'dscribe1'))) $showrowdscribe = true;
	  else $showrowdscribe = false;
	  }
	*/
	$dscribe1s_html = '';
	foreach ($course_users[$row['cid']] as $cu) if ($cu['role'] == 'dscribe1') $dscribe1s_html .= $cu['name']."<br>";
	$row['dscribe1s'] = $dscribe1s_html;
	$dscribe2s_html = '';
	foreach ($course_users[$row['cid']] as $cu) if ($cu['role'] == 'dscribe2') $dscribe2s_html .= $cu['name']."<br>";
	$row['dscribe2s'] = $dscribe2s_html;

	// bdr OERDEV-173 - count everything like materials list counts
	$materials =  $this->material->materials($row['cid'],'',true,true);
	$row['total'] = 0;
	$row['done']  = 0;
	$row['ask']   = 0;
	$row['rem']   = 0;
	if ($materials != NULL) {
	  foreach($materials as $category => $cmaterial) {
	    foreach($cmaterial as $material) {
	      $row['rem'] += $material['mrem'];
	      $row['ask'] += $material['mask'];
	      $row['done'] += $material['mdone'];
	      //if ($material['mtotal'] != 1000000)                              //OERDEV-181 mbleed: removed hardcoded total=1000000 logic
	      $row['total'] += $material['mtotal'];
	    }
	  }

	  $row['statcount'] = $row['total'].'/'.$row['done'].'/'.$row['ask'].'/'.$row['rem'];
	  $row['notdone'] = $row['rem'];
	  // $this->ocw_utils->dump($row);
	}
	$courses[$row['sname']][$row['cname']][] = $row;
      }
    }

    // get the courses that have NULL curriculum ids
    $sql_no_curr_id = "SELECT ocw_courses.*,
                        ocw_courses.curriculum_id AS cname,
                        ocw_courses.curriculum_id AS sname,
                        ocw_courses.id AS cid
                        FROM ocw_courses, ocw_acl
                        WHERE ocw_courses.curriculum_id = NULL
                        AND ocw_acl.course_id = ocw_courses.id
                        $where2
                        GROUP BY ocw_courses.id
                        ORDER BY start_date DESC";


    $q_no_curr_id = $this->db->query($sql_no_curr_id);

    if ($q_no_curr_id->num_rows() > 0) {
      foreach ($q_no_curr_id->result_array() as $row) {
	$row['instructors'] = $this->get_course_users_by_cid($row['cid'], 'instructor');
	$row['dscribe1s'] = $this->get_course_users_by_cid($row['cid'], 'dscribe1');
	$row['dscribe2s'] = $this->get_course_users_by_cid($row['cid'], 'dscribe2');
	// bdr OERDEV-140 (which looks similiar to OERDEV-118
	$uprop = getUserProperty('role');
	// if (($uprop != 'dscribe1')) { // && ($row['cid'] == 35))
	// bdr OERDEV-173 - count everything like materials list counts

	$materials =  $this->material->materials($row['cid'],'',true,true);
	$row['total'] = 0;
	$row['done']  = 0;
	$row['ask']   = 0;
	$row['rem']   = 0;
	foreach($materials as $category => $cmaterial) {
	  foreach($cmaterial as $material) {
	    $row['rem'] += $material['mrem'];
	    $row['ask'] += $material['mask'];
	    $row['done'] += $material['mdone'];
	    if ($material['mtotal'] != 1000000)
	      $row['total'] += $material['mtotal'];
	  }
	}

	$row['statcount'] = $row['total'].'/'.$row['done'].'/'.$row['ask'].'/'.$row['rem'];
	$row['notdone'] = $row['rem'];
	// $this->ocw_utils->dump($row);

	$courses['No School Specified']['No Curriculum Specified'][] = $row;
      }
    }
    //exit($this->ocw_utils->dump($courses));
    return (sizeof($courses) > 0) ? $courses : null;
  }


  /**
   * Get course title
   *
   * @access  public
   * @param   int     cid course id
   * @return  string
   */
  public function course_title($cid)
  {
    $course = $this->get_course($cid,'number,title');
    $title = $course['number'].' '.$course['title'];
    return ($course==null) ? null : trim($title);
  }

  /**
   * Update course
   *
   * @access  public
   * @param   int     cid course id
   * @param   array data
   * @return  void
   */
  public function update_course($cid, $data)
  {
    $this->db->update('courses',$data,"id=$cid");
  }

  /**
   * check to see if a user has access to a course
   *
   * @access  public
   * @param   int     uid user id
   * @param   int     cid course id
   * @return  boolean
   */
  public function has_access($uid, $cid)
  {
    $where = array('user_id'=>$uid, 'course_id'=>$cid);
    $this->db->select('*')->from('acl')->where($where);
    $q = $this->db->get();
    $course = $q->row_array();
    return ($q->num_rows() > 0 || sizeof($course) > 0) ? $course : null;
  }


  private function course_name_exists($name)
  {
    $this->db->select('filename')->from('course_files')->where("filename='$name'");
    $q = $this->db->get();
    return ($q->num_rows() > 0) ? true : false;
  }

  public function generate_course_name($uniqstr)
  {
    $digest = '';
    do {
      $digest = $this->oer_filename->random_name($uniqstr);
      $uniqstr = $digest;
    } while ($this->course_name_exists($digest));

    return $digest;
  }

  /**
   * Return path for course information
   *
   * @access  public
   * @param   int       cid course id
   * @return  string path name
   */
  public function course_path($cid)
  {
    # get course directory name
    $path = property('app_uploads_path');
    $this->db->select('filename')->from('course_files')->where("course_id=$cid")->order_by('created_on desc')->limit(1);
    $q = $this->db->get();
    $r = $q->row();
    $path .= 'cdir_'.$r->filename;
    return $path;
  }

  /**
   * Return years listed in courses
   *
   * @access  public
   * @return array years
   * mbleed - faceted search 5/2009
   */
  public function get_years_for_all_courses()
  {
    $this->db->select('year')->from('courses')->order_by('year desc');
    $q = $this->db->get();
    foreach ($q->result() as $row) {
      if ($row->year > 0)
	$year_array[$row->year] = $row->year;
    }

    //return array_values(array_unique($year_array));
    return array_unique($year_array);
  }

  public function get_school_list()
  {
    $school_list = NULL;
    $this->db->select('id, name')->order_by('name');
    $query = $this->db->get('schools');

    foreach ($query->result() as $row) {
      $school_list[$row->id] = $row->name;
    }

    return $school_list;
  }

  /**
   * Return users listed in all courses by role
   *
   * @access  public
   * @return array users
   * mbleed - faceted search 5/2009
   */
  public function get_users_for_all_courses($role) {
    $users = array();
    $this->db->select('users.id, users.name, acl.user_id')->from('acl')->join('users', 'users.id = acl.user_id', 'left')->where("acl.role = '$role'")->group_by('acl.user_id');
    $q = $this->db->get();
    if ($q->num_rows() > 0) {
      foreach($q->result_array() as $row) {
	$users[$row['id']] = $row['name'];
      }
    }
    return ($q->num_rows() > 0) ? $users : null;
  }

  /*
   * Return users listed in all courses all roles - this is a data ref table to be used in replace of some queries for optimization
   *
   * @access  public
   * @return array users
   * mbleed - faceted search 5/2009
   */
  public function get_course_user_roles() {
    $course_users = array();
    $this->db->select('users.id, users.name, users.role, acl.course_id')->from('acl')->join('users', 'users.id = acl.user_id', 'inner');
    $q = $this->db->get();
    if ($q->num_rows() > 0) {
      foreach($q->result_array() as $row) {
	$course_users[$row['course_id']][] = array(
						   'user_id'=>$row['id'],
						   'name'=>$row['name'],
						   'role'=>$row['role']
						   );
      }
    }
    return ($q->num_rows() > 0) ? $course_users : null;
  }


  /**
   * Get all the information related to all courses for a user.
   *
   * This function is very greedy about the information it gathers.
   * it gets all other users, and their roles for all the courses to which
   * a particular user is assigned. It is intended to replace many many
   * embedded model calls that result in massive numbers of DB queries.
   *
   * The result set includes the dscribe1s, dscribe2s, instructors
   * for courses as well as the clearance status counts for the content
   * objects within each course. Other course metadata present in the
   * ocw_courses table is also returned.
   *
   * It provides output in similar format to $this->get_courses and adds
   * values that are used by faceted search.
   *
   * @access public
   * @param int the numeric userid
   * @return detailed information on all the courses a user may access.
   *
   */
  public function new_get_courses($uid, $role = NULL)
  {
    $course_ids = NULL;

    if (!$role) {
      $role = getUserPropertyFromId($uid, 'role');
    }

    $sql = "SELECT
    ocw_schools.id AS school_id,
    ocw_schools.name AS school_name,

    ocw_curriculums.id AS curriculum_id,
    ocw_curriculums.name AS curriculum_name,

    ocw_courses.id AS course_id,
    ocw_courses.number AS course_number,
    ocw_courses.title As course_title,
    ocw_courses.start_date,
    ocw_courses.end_date,
    ocw_courses.director,
    ocw_courses.creator,
    ocw_courses.level,
    ocw_courses.length,
    ocw_courses.term,
    ocw_courses.year,
    ocw_courses.language,

    ocw_materials.id AS material_id,
    ocw_materials.category AS material_category,
    ocw_materials.name AS material_name,

    ocw_objects.id AS object_id,
    ocw_objects.ask AS object_ask,
    ocw_objects.ask_status AS object_ask_status,
    ocw_objects.ask_dscribe2 AS object_ask_dscribe2,
    ocw_objects.ask_dscribe2_status AS object_ask_dscribe2_status,
    ocw_objects.action_type AS object_action_type,
    ocw_objects.action_taken AS object_action_taken,
    ocw_objects.done AS object_done

    FROM
    ocw_courses

    INNER JOIN ocw_schools ON
    (ocw_schools.id = ocw_courses.school_id)

    INNER JOIN ocw_curriculums ON
    (ocw_curriculums.id = ocw_courses.curriculum_id)

    LEFT OUTER JOIN ocw_materials ON
    (ocw_courses.id = ocw_materials.course_id)

    LEFT OUTER JOIN ocw_objects ON
    (ocw_materials.id = ocw_objects.material_id)";

    if ($role == "admin") {
      $this->__get_all_courses_users_roles();
    } else {
      $this->__get_courses_users_roles($uid);
      if (count($this->courses_users_roles) > 0) {
        // get the ids of all the courses the user may access
        $course_ids = array_keys($this->courses_users_roles);
        /* construct the 'WHERE' clause of the query
         * from the list of course_ids grabbed from
         * $this->courses_users_roles
         * the loop adds all but the last course id and
         * the line after the loop does the rest
         */
        $sql .= "
        WHERE (";
        for ($i=0; $i < (count($course_ids) - 1); $i++) {
          $sql .= "ocw_courses.id = ? OR ";
        }
	$sql .= "ocw_courses.id = ?)";
      } else {
        return NULL;
      }
    }

    if ($course_ids) {
      $course_info = $this->db->query($sql, $course_ids);
    } else {
      // Admin case. No where clause so no parameter array supplied for query.
      $course_info = $this->db->query($sql);
    }

    if ($course_info->num_rows() > 0) {
      $this->__process_courses_qresult($course_info);
    }

    // Free memory used by the DB query.
    $course_info->free_result();
    //exit($this->ocw_utils->dump($this->courses));
    return (sizeof($this->courses) > 0) ? $this->courses : NULL;
  }


  /**
   * Get all the courses, the other users that have access to that
   * course, and those users roles given a particular user id.
   *
   * @access public
   * @param int user id
   * @return Nothing. The instance variable courses_users_roles is populated
   *         with the results.
   */
  private function __get_courses_users_roles($user_id)
  {
    $sql = "SELECT
    ocw_acl_2.course_id,
    ocw_acl.user_id,
    ocw_users_2.user_name,
    ocw_users_2.name,
    ocw_acl.role

    FROM
    ocw_acl

    INNER JOIN ocw_acl AS ocw_acl_2 ON
    (ocw_acl.course_id = ocw_acl_2.course_id)

    INNER JOIN ocw_users ON
    (ocw_acl_2.user_id = ocw_users.id)

    INNER JOIN ocw_users AS ocw_users_2 ON
    (ocw_acl.user_id = ocw_users_2.id)

    WHERE
    ocw_users.id = ?";
    $query = $this->db->query($sql, array($user_id));
    if ($query->num_rows() > 0) {
      $this->__build_courses_users_roles($query);
    }

    // Free memory used by the query.
    $query->free_result();

    return;
  }


  /**
   * Get all course ids along with the users assigned to those courses and
   * those users names, user ids and their roles. This function is intended
   * for the admin user who needs to have the display of all users and
   * courses and has no entry in the ocw_acls table and so cannot use
   * get_courses_users_roles($user_id).
   *
   * @access public
   * @return Nothing. The instance variable courses_users_roles is populated
   *         with the results.
   */
  private function __get_all_courses_users_roles()
  {
    $sql = "SELECT
    ocw_acl.course_id,
    ocw_acl.user_id,
    ocw_users.user_name,
    ocw_users.name,
    ocw_acl.role

    FROM
    ocw_acl

    INNER JOIN ocw_users ON
    (ocw_acl.user_id = ocw_users.id)";
    $query = $this->db->query($sql);
    if ($query->num_rows() > 0) {
      $this->__build_courses_users_roles($query);
    }

    // Free memory used by the query.
    $query->free_result();

    return;
  }


  /**
   * Process the result set returned by new_get_courses.
   *
   * @access private
   * @param query object from new_get_courses query
   * @return void
   */
  private function __process_courses_qresult($course_info)
  {
    foreach ($course_info->result_array() as $row) {
      if (!array_key_exists($row['school_name'], $this->courses)) {
        $this->courses[$row['school_name']] = array();
      }

      if (!array_key_exists($row['curriculum_name'],
			    $this->courses[$row['school_name']])) {
        $this->courses[$row['school_name']][$row['curriculum_name']] =
          array();
      }

      if (!array_key_exists($row['course_id'],
			    $this->courses[$row['school_name']][$row['curriculum_name']])) {
        /* Copy values over from the query of the acl table if the course has
         * defined acl entries. Otherwise put in values with empty placeholder
         * arrays for the acl fields. */
        // TODO: should we put in empty arrays or check in the view?
        if (array_key_exists($row['course_id'], $this->courses_users_roles)) {
          $this->courses[$row['school_name']]
	    [$row['curriculum_name']]
	    [$row['course_id']] = $this->courses_users_roles[$row['course_id']];
        } else {
          $this->courses[$row['school_name']]
	    [$row['curriculum_name']]
	    [$row['course_id']] = array(
					'id' => $row['course_id'],
					'cid' => $row['course_id'],
					'instructors' => array(),
					'dscribe1s' => array(),
					'dscribe2s' => array()
					 );
        }

        $additional_vals = array(
				 'number' => $row['course_number'],
				 'title' => $row['course_title'],
				 'start_date' => $row['start_date'],
				 'creator' => $row['creator'],
				 'director' => $row['director'],
				 'level' => $row['level'],
				 'year' => $row['year'],
				 'length' => $row['length'],
				 'term' => $row['term'],
				 'end_date' => $row['end_date'],
				 'school_id' => $row['school_id'],
				 'school_name' => $row['school_name'],
				 'curriculum_id' => $row['curriculum_id'],
				 'curriculum_name' => $row['curriculum_name'],
				 'material_id' => $row['material_id'],
				 'material_name' => $row['material_name'],
				 'material_category' => $row['material_category']
				 );

        // add additional attributes to current array element
        $this->courses[$row['school_name']]
          [$row['curriculum_name']]
          [$row['course_id']] = array_merge(
					    $this->courses[$row['school_name']]
					    [$row['curriculum_name']]
					    [$row['course_id']],
					    $additional_vals);
      }

      /* Add counts to each array element if counts aren't already
       * present.
       */
      $co_count_keys = array(
			     "total",
			     "done",
			     "ask",
			     "rem",
			     "notdone"
			     );

      $initial_counts = array(
			      'total' => 0,
			      'done' => 0,
			      'ask' => 0,
			      'rem' => 0,
			      'notdone' => 0
			      );

      $curr_array_keys = array_keys($this->courses[$row['school_name']]
				    [$row['curriculum_name']]
				    [$row['course_id']]);

      if ((count(array_intersect($co_count_keys, $curr_array_keys))) == 0) {
        $this->courses[$row['school_name']]
          [$row['curriculum_name']]
          [$row['course_id']] = array_merge(
					    $this->courses[$row['school_name']]
					    [$row['curriculum_name']]
					    [$row['course_id']],
					    $initial_counts);
      }

      // Do the counts for defined content objects.
      if ($row['object_id']) {
        if ($row['object_done'] == '1' && !empty($row['object_action_taken'])) {
          $this->courses[$row['school_name']]
            [$row['curriculum_name']]
            [$row['course_id']]
            ['done']++;
        } elseif (!empty($row['object_action_type']) ||
		  !empty($row['object_action_taken']) ||
		  (!empty($row['object_ask']) && $row['object_ask_status'] != 'new') ||
		  (!empty($row['object_ask_dscribe2']) && $row['object_ask_dscribe2_status'] != 'new')) {
          $this->courses[$row['school_name']]
            [$row['curriculum_name']]
            [$row['course_id']]
            ['ask']++;
        } else {
          $this->courses[$row['school_name']]
            [$row['curriculum_name']]
            [$row['course_id']]
            ['notdone']++;
        }
        $this->courses[$row['school_name']]
          [$row['curriculum_name']]
          [$row['course_id']]
          ['total']++;

        $this->courses[$row['school_name']]
          [$row['curriculum_name']]
          [$row['course_id']]
          ['rem'] =
          $this->courses[$row['school_name']]
	  [$row['curriculum_name']]
	  [$row['course_id']]
	  ['notdone'];
      }
    }
    return;
  }


  /**
   * Parse the query results for queries getting courses and users
   * and build an array that organizes the users and their roles by
   * course id.
   *
   * @access private
   * @param row of course info data (passed by reference)
   * @return void
   */
  private function __build_courses_users_roles($query_object)
  {
    foreach ($query_object->result_array() as $row) {
      /* If a course isn't already in $this->courses_users_roles, add it to
       * the array. Initialize the arrays that track the users assigned to
       * different roles for the course as well. */
      if (!array_key_exists($row['course_id'], $this->courses_users_roles)) {
        $this->courses_users_roles[$row['course_id']] =
          array( 'id' => $row['course_id'],
		 'cid' => $row['course_id'],
		 'instructors' => array(),
		 'dscribe1s' => array(),
		 'dscribe2s' => array()
		 );
      }

      /* Check to see if the entry for the role in the current course
       * $this->courses_users_roles has the current user id. If not add
       * the user id as an array key and add the username, and user id as
       * values of the array as well */
      if (!array_key_exists ($row['user_id'],
			     $this->courses_users_roles[$row['course_id']]["${row['role']}s"])) {
        $this->courses_users_roles
          [$row['course_id']]["${row['role']}s"][$row['user_id']]['user_id'] =
          $row['user_id'];
        $this->courses_users_roles
          [$row['course_id']]["${row['role']}s"][$row['user_id']]['name'] =
          $row['name'];
      }
    }
    return;
  }
}
?>
