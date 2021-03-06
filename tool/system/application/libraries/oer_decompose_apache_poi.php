<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * OER_decompose_apache_poi class
 * Invokes apache-poi java code to extract Content Objects
 * (Images) from a material file
 * 
 * @package			OERca
 * @subpackage	Libraries
 * @category		Utility, Archiving
 * @author			Kevin Coffman <kwc@umich.edu>
 */

class OER_decompose_apache_poi
{
  
	private $CI = NULL;
	private $staging_dir = NULL;
	private $java_path = NULL;
	private $poi_jar_path = NULL;
	private $jar_options = "";
  
	/**
	 * Constructor
	 *
	 * @access  public
	 * @return  an instance of the class
	 */
	public function __construct()
	{
		static $path_set = 0;
		
		$this->CI =& get_instance();
		$this->CI->load->library('ocw_utils');
    
		//$this->CI->ocw_utils->log_to_apache('debug', "+++++ OER_decompose_apache_poi Class Constructor +++++");

		$this->java_path = property('app_java_path');
		$this->poi_jar_path = property('app_poi_jar_path');

		return $this;
	}
  
  public function __destruct()
	{
		//$this->CI->ocw_utils->log_to_apache('debug', "----- OER_decompose_apache_poi Class Destructor -----");
	}


  /**
   * Set the directory in which content objects (COs) are held
	 * while metadata is collected and COs are added 
   *
   * @access  public
   * @param   string Directory in which COs are spewed
   * @return  void
   */
  public function set_staging_dir($passed_dir)
  {
		//$this->CI->ocw_utils->log_to_apache('debug', "poi::set_staging_dir: entered with: '{$passed_dir}'");
		$this->staging_dir = $passed_dir;
  }
  
  
  /**
   * Get the directory in which COs are spewed
   *
   * @access  public
   * @return  name of the directory in which COs are spewed
   */
  public function get_staging_dir()
  {
		//$this->CI->ocw_utils->log_to_apache('debug', "poi::get_staging_dir: returning with: '{$this->staging_dir}'");
		return $this->staging_dir;
  }
  
  
  /**
   * Clean up the temporary directory in which COs are spewed
   *
   * @access  public
   * @return  boolean
   */
  public function rm_staging_dir()
  {
		//$this->CI->ocw_utils->log_to_apache('debug', "poi::rm_staging_dir: removing directory '{$this->staging_dir}'");
		if (is_dir($this->staging_dir)) {
			$this->CI->ocw_utils->remove_dir($this->staging_dir);
			$this->staging_dir = NULL;
			return TRUE;
		} else {
			//$this->CI->ocw_utils->log_to_apache('debug', "poi::rm_staging_dir: '{$this->staging_dir}' is not a directory!");
			return FALSE;
		}
  }
  
  
  /**
   * Call the apache_poi java application to extract images from
	 * the materials file.
	 *
	 * NOTE: See http://stackoverflow.com/questions/278868/calling-java-from-php-exec
	 * Setting the DYLD_LIBRARY_PATH="" should only be necessary when running
	 * the web server on a Mac under MAMP.  Hopefully, it does not affect things when
	 * running on a Linux web server.
   *
   * @access   public
   * @param    full path to the materials file to be decomposed
	 * @return   the return code from poi invocation
   */
  public function do_decomp($materials_file)
  {
		$this->CI->ocw_utils->log_to_apache('debug', "poi::do_decomp: '{$materials_file}'");
		
		if ($this->staging_dir == NULL) {
			$subdir = "decomp_" . sha1(time() . rand(1,10000000000));
			$this->staging_dir = property('app_mat_decompose_dir') . $subdir;
			//$this->CI->ocw_utils->log_to_apache('debug', "poi::do_decomp: defaulted staging_dir to '{$this->staging_dir}'");
		}

		//$this->CI->ocw_utils->log_to_apache('debug', "poi::do_decomp: java path is '{$this->java_path}'");
		//$this->CI->ocw_utils->log_to_apache('debug', "poi::do_decomp: poi jar file is '{$this->poi_jar_path}'");
		//$this->CI->ocw_utils->log_to_apache('debug', "poi::do_decomp: staging dir is '{$this->staging_dir}'");

		$poicode = -1;
		$poiout = array();
		// Redirect stderr to /dev/null to eliminate "random" messages from the apache log file
		// Local MAMP server needs some extra parameters
		$SET_DYLD_PATH = "";
		if ($this->CI->config->item('is_local_mamp_server')) {
			//$this->CI->ocw_utils->log_to_apache('debug', __FUNCTION__.": *** Using LOCAL Settings ***");
			$SET_DYLD_PATH .= "export DYLD_LIBRARY_PATH=\"\";";
		}

		exec("$SET_DYLD_PATH $this->java_path -Xmx1G -jar $this->poi_jar_path $materials_file $this->staging_dir &> /dev/null", $poiout, $poicode);
		unset($poiout);
		$this->CI->ocw_utils->log_to_apache('debug', "poi::do_decomp: returning '{$poicode}'");

		return $poicode;
	}

}

?>
