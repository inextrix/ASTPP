<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * User Tracking for CodeIgniter
 *
 * A library for tracking user activity in CodeIgniter applications.
 * Requires PHP v.5.1 or higher.
 *
 * @package		Usertracking for CodeIgniter
 * @author		Casey McLaughlin
 * @copyright	Open Source BSD License
 * @license		http://caseyamcl.com/resources/userTrackingForCI#license
 * @link		  http://caseyamcl.com/resources/userTrackingForCI#license
 * @since		 Version 1.2
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Usertracking library
 *
 * @package		  Usertracking for CodeIgniter
 * @subpackage	Libraries
 * @author		  Casey McLaughlin
 * @link		    http://caseyamcl.com/resources/userTrackingForCI#license
 */
#[\AllowDynamicProperties]
class Usertracking
{
  private $CI;
  private $configuration;

  private $needed_fields = array(array('name' => 'id', 'type' => 'int', 'primary_key' => 1, 'forge_type' => 'int', 'forge_auto_increment' => TRUE),
								array('name' => 'session_id', 'type' => 'string', 'forge_type' => 'varchar', 'forge_constraint' => '100'),
								array('name' => 'user_identifier', 'type' => 'string', 'forge_type' => 'varchar', 'forge_constraint' => '255'),
								array('name' => 'request_uri', 'type' => 'string', 'forge_type' => 'text'),
								array('name' => 'timestamp', 'type' => 'string', 'forge_type' => 'varchar', 'forge_constraint' => '20'),
								array('name' => 'client_ip', 'type' => 'string', 'forge_type' => 'varchar', 'forge_constraint' => '50'),
								array('name' => 'client_user_agent', 'type' => 'string', 'forge_type' => 'text'),
								array('name' => 'referer_page', 'type' => 'string', 'forge_type' => 'text'));

  /**
   * Constructor
   *
   * Does nothing but run the initialization
   * method.
   *
   * @access public
   * @return void|boolean Will return FALSE if the intialization did not succeed.
   */
  public function __construct()
  {
    $result = $this->initialize();

    if ($result === FALSE)
      return FALSE;
  }


	// --------------------------------------------------------------------

  /**
   * Initialization script
   *
   * Checks the environment to ensure that the library
   * will run.
   *
   * @access public
   * @return boolean The result of the intialization process
   */
  public function initialize()
  {
    //connect to CodeIgnitor
    if ( ! $this->CI =& get_instance())
    {
      echo "The UserTracking library is built for CodeIgnitor 1.6.3 and cannot be used outside of CI.";
      exit();
    }

    //if php is not new enough, show error and die.
    if (phpversion() < 5.1)
    {
      show_error('The Usertracking plugin is supported only on PHP v5.1 and above!');
      return FALSE;
    }

    //check for the configuration file
    /*if ( ! $this->CI->config->load('usertracking_config') OR $this->CI->config->item('usertracking') === FALSE)
    {
      show_error("Missing the configuration for UserTracking.  Ensure you have installed UserTracking correctly.");
      return FALSE;
    }*/

    //Load the configuration
    $this->configuration = $this->CI->config->item('usertracking');

    //check the database for the table
    if ( ! $this->check_database())
    {
      show_error("The database is not setup correctly for UserTracking.  Check to ensure proper database setup, or check the config settings for usertracking.");
      return FALSE;
    }

    //if made it here
    return TRUE;
  }


  // --------------------------------------------------------------------

  /**
   * Automatically Track User Events
   *
   * Auto-track is meant to be run only from a CodeIgniter hook.  It
   * checks to ensure that autotracking is enabled, checks any filters
   * that may have been configured in the configuration file, and then
   * runs the {@link trackThis()} method.
   *
   * @return boolean The result of the tracking action (whether a db record was added or not)
   */
  public function auto_track()
  {
    //check the conditions
    //if ($this->configuration['auto_track'] == TRUE)
    {
      $proceed = $this->check_filters();

      if ($proceed === TRUE)
        return $this->track_this();
    }
  }


  // --------------------------------------------------------------------

  /**
   * Track the current pageview
   *
   * Retrieves information from the session, user agent, and server
   * fields, and then adds a record to the tracking database.
   *
   * @access public
   * @return boolean The result of the tracking action (whether a db record was added or not)
   */
  public function track_this()
  {
    //load necessary libraries
    $this->CI->load->database();
    $this->CI->load->library('user_agent');
    $this->CI->load->library('session');

    if ($this->CI->input->server('REQUEST_URI') == '/login/get_language_text/' || strstr($this->CI->agent->referrer(),"asset") || $this->CI->input->server('REQUEST_URI') == '/' || $this->CI->input->server('REMOTE_ADDR') == '::1')
    {
      return;
    }
    //get the data
    $user = common_model::$global_config['system_config']['audit_log'];
    if($user == '0'){
      $input_data = array();
      $input_data['session_id'] = $this->CI->session->userdata('session_id');
      $input_data['request_uri'] = $this->CI->input->server('REQUEST_URI');
      $input_data['timestamp'] = gmdate("Y-m-d H:i:s");
      $input_data['client_ip'] = $this->CI->input->server('REMOTE_ADDR');
      $input_data['client_user_agent'] = $this->CI->agent->agent_string()."..".$this->CI->db->last_query();
      $input_data['referer_page'] = $this->CI->agent->referrer();

      $accountinfo = $this->CI->session->userdata ( 'accountinfo' ) ;
      $input_data['user_identifier'] = ($accountinfo['id']=='')?'0':$accountinfo['id'];
      //Add it to the database
      $this->CI->load->database();
      $result = $this->CI->db->insert('usertracking', $input_data);
  
      if ($result === FALSE)
        show_error("Could not write to the usertracking table in the database while trying to add a tracking record.  Double-check configureation and datbase setup for Usertracking library!");

        //Return the database write result
      // return $result;
    }
    
    return true;

    //Get the user identifier, if set
    /*if ($this->configuration['user_identifier'] !== null && is_array($this->configuration['user_identifier']))
    {
      if (count($this->configuration['user_identifier']) == 3)
      {
        list($class_type, $class_name, $function_name) = $this->configuration['user_identifier'];
        $the_args = array();
      }
      elseif (count($this->configuration['user_identifier']) == 4)
        list($class_type, $class_name, $function_name, $the_args) = $this->configuration['user_identifier'];

      if ( ! $this->CI->load->$class_type($class_name))
      {
        if ((($class_type !== 'helper') && !method_exists($this->CI->$class_name, $function_name)) OR ($class_type == 'helper' && !function_exists($function_name)))
          display_error("Could not load the $function_name in $class_name.  Check the userIdentifier configuration in userTracking config. User Identifier will not be tracked.");
        else //Do it!
        {
          if ($class_type == 'helper')
            $input_data['user_identifier'] = call_user_func_array($function_name, $the_args);
          else
            $input_data['user_identifier'] = call_user_func_array(array($this->CI->$class_name, $function_name), $the_args);
        }
      }
      else
        display_error("Could not load the $class_type: $class_name.  Check the userIdentifier configuration in userTracking config. User Identifier will not be tracked.");
    }*/
  }


  // --------------------------------------------------------------------

  /**
   * Check/Process filters
   *
   * This is run as part of the autoTrack() function.  It processes any
   * filters defined in the configuration file and returns either TRUE or FALSE.
   *
   * @access  private
   * @return boolean Whether or not the filter tests suceeded.
   */
  private function check_filters()
  {
    //Get the tracking filters from the config
    $tracking_filters = $this->configuration['tracking_filter'];

    //If there are filters, process them
    if (is_array($tracking_filters) && astpp_count($tracking_filters) > 0 && $tracking_filters[0] !== null)
    {
      $filter_results = array();

      //go through each tracking filter and make sure that they pass
      foreach($tracking_filters as $curr_filter)
      {
        //Check the filter for the right type
        if ( ! is_array($curr_filter) OR (astpp_count($curr_filter) != 4 && astpp_count($curr_filter) != 5))
        {
          show_error("The userTracking filter is malformed.  Check the userTracking configuration.  Filter: $curr_filter");
          continue;
        }

        if (astpp_count($curr_filter) == 4)
        {
          list($class_type, $class_name, $function_name, $expected_result) = $curr_filter;
          $the_args = array();  //This is empty
        }
        elseif (astpp_count($curr_filter) == 5)
          list($class_type, $class_name, $function_name, $expected_result, $the_args) = $curr_filter;
        else
        {
          show_error("The userTracking filter is malformed.  Check the userTracking configuration.  Filter: $curr_filter");
          continue;
        }

        //Run the filter
        if ( ! $this->CI->load->$class_type($class_name))
        {
          if ((($class_type !== 'helper') && !method_exists($this->CI->$class_name, $function_name)) OR ($class_type == 'helper' && !function_exists($function_name)))
            show_error("Could not load the $function_name in $class_name.  The filter will not be applied.");
          else //Do it!
          {
            if ($class_type == 'helper')
              $curr_filter_result = call_user_func_array($function_name, $the_args);
            else
              $curr_filter_result = call_user_func_array(array($this->CI->$class_name, $function_name), $the_args);
          }
        }
        else
          show_error("Could not load the $class_type: $class_name.  Check the userIdentifier configuration in userTracking config. User filter will not be applied.");

        //Analyze the results
        //If there is no result for the current filter, autofail!
        if ( ! isset($curr_filter_result))
        {
          $filter_results[] = 'fail';
        }
        else //if there is a result, test it
        {
        	//Do a strong type comparison if we are expecting true or false
        	if ($expected_result === TRUE OR $expected_result === FALSE)
        	{
	          if ($curr_filter_result === $expected_result)
	            $filter_results[] = 'pass';
	          else
	            $filter_results[] = 'fail';
        	}
        	else //Do a weak type comparison for everything else
        	{
	          if ($curr_filter_result == $expected_result)
	            $filter_results[] = 'pass';
	          else
	            $filter_results[] = 'fail';
        	}
        }
      }

      //check the results
      if (strtoupper($this->configuration['tracking_filter_logic']) == "AND") //AND Logic
      {
        if ( ! astpp_in_array("fail", $filter_results))
          return TRUE;
        else
          return FALSE;
      }
      else //OR Logic
      {
        if (astpp_in_array("pass", $filter_results))
          return TRUE;
        else
          return FALSE;
      }
    }
    else //no filters to check -- pass automatically.
      return TRUE;
  }


  // --------------------------------------------------------------------


  /**
   * Check Database
   *
   * This checks and, if defined in the configuration file, builds the
   * necessary database tables for tracking using the CI database forge class.
   *
   * If it finds a malformed table, it will backup that table and create a new one.
   *
   * @access private
   * @return boolean Whether the database table exists or was setup succesfully.
   */
  private function check_database()
  {
    //load the ci database and db forge classes, or show error & return FALSE
    $this->CI->load->database();
    $this->CI->load->dbforge();

    //check to see if the table exists
    if ($this->CI->db->table_exists('usertracking'))
    {
      //if the table exists, check to see if the columns are setup correctly
      $fields = $this->CI->db->field_data('usertracking');

      //if the columns are setup correctly, return TRUE
      $num_matched = 0;
      foreach($this->needed_fields as $needed_field)
      {
        $nf_name = $needed_field['name'];
        $nf_type = $needed_field['type'];

        foreach($fields as $the_field)
        {
          if ($the_field->name == $nf_name && $the_field->type = $nf_type)
          {
            $num_matched++;
            break;
          }
        }
      }

      if ($num_matched < astpp_count($this->needed_fields) && $this->configuration['auto_fix_db'] === TRUE)
      {
        //if the columns are setup incorrectly and autofix_db is on, fix the db and return TRUE

        //rename the table
        global $CI;
        $db_prefix = $CI->db->dbprefix;
        $this->CI->dbforge->rename_table($db_prefix . 'usertracking', 'usertracking_backup_' . time());
        $this->CI->db->query("UNLOCK TABLES;");

        //rebuild the table
        $result = $this->build_database_table();

        //return TRUE
        return $result;
      }
      elseif ($num_matched < astpp_count($this->needed_fields) && $this->configuration['auto_fix_db'] !== TRUE)
      {
        //if the columns are setup incorrectly and autofix_db is off, show error return FALSE
        show_error('The database table exists, but is malformed and not setup correctly.');
        return FALSE;
      }
      else //everything is setup right
        return TRUE;
    }
    elseif ($this->configuration['auto_build_db'] === TRUE)
    {
      //if the table doesn't exist, and autoBuild_db is on, build the table and return TRUE
      $result = $this->build_database_table();
      return $result;
    }
    else
    {
      //if the table doesn't exist, and autoBuild_db is off, show error and return FALSE
      show_error("The usertracking database table does not exist.  Check your database installation.");
      return FALSE;
    }
  }

  // --------------------------------------------------------------------


  /**
   * Build Database Table
   *
   * Builds the database table.  If one already exists, it will overwrite.  This method
   * should only be called from the {@link checkDatabase} method to avoid potential
   * data loss.
   *
   * @access private
   * @return boolean Whether the table was built succesfully
   */
  private function build_database_table()
  {
    //load the ci database and db forge classes, or show error & return FALSE (if not already done)
    $this->CI->load->database();
    $this->CI->load->dbforge();

    //create a new table with the appropriate fields
    $new_fields = array();

    foreach($this->needed_fields as $curr_nf)
    {
      $name = $curr_nf['name'];
      $new_fields[$name] = array('type' => $curr_nf['forge_type']);
      if (isset($curr_nf['forge_constraint']))
        $new_fields[$name]['constraint'] = $curr_nf['forge_constraint'];
      if (isset($curr_nf['forge_auto_increment']))
        $new_fields[$name]['auto_increment'] = $curr_nf['forge_auto_increment'];

      if (isset($curr_nf['primary_key']) && $curr_nf['primary_key'] == 1)
        $this->CI->dbforge->add_key($name, TRUE);
    }

    $this->CI->dbforge->add_field($new_fields);
    $this->CI->dbforge->create_table('usertracking');
    return TRUE;
  }

}
