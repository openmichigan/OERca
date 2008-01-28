<?php
/**
 * Provides access to course information 
 *
 * @package	OCW Tool		
 * @author David Hutchful <dkhutch@umich.edu>
 * @date 1 September 2007
 * @copyright Copyright (c) 2006, University of Michigan
 */

class Course extends Model 
{
	public function __construct()
	{
		parent::Model();
	}

	/**
     * Get course
     *
     * @access  public
     * @param   int	cid course id		
     * @param   string	details 
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
     * Get a user's courses 
     *
     * @access  public
     * @param   int user id
     * @return  array
     */
    public function get_courses()
    {
        $courses = array();
		$sql = 'SELECT ocw_courses. *, ocw_curriculums.name AS cname, ocw_schools.name AS sname
				  FROM ocw_courses, ocw_curriculums, ocw_schools
				 WHERE ocw_curriculums.id = ocw_courses.curriculum_id
				   AND ocw_schools.id = ocw_curriculums.school_id
				 ORDER BY start_date DESC';
		$q = $this->db->query($sql);

        if ($q->num_rows() > 0) {
            foreach($q->result_array() as $row) { 
					$courses[$row['sname']][$row['cname']][] = $row; 
			}
        }

        return (sizeof($courses) > 0) ? $courses : null;
    }

	/**
     * Get course title
     *
     * @access  public
     * @param   int	cid course id		
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
     * @param   int	cid course id		
     * @param   array data 
     * @return  void
     */
	public function update_course($cid, $data)
	{
		$this->db->update('courses',$data,"id=$cid");
	}
}
?>