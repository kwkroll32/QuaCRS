<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 2.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Session Class
 *
 * The user interface defined by EllisLabs, now with puggable drivers to manage different storage mechanisms.
 * By default, the cookie session driver will load, but the 'sess_driver' config/param item (see above) can be
 * used to specify the 'native' driver, or any other you might create.
 * Once loaded, this driver setup is a drop-in replacement for the former CI_Session library, taking its place as the
 * 'session' member of the global controller framework (e.g.: $CI->session or $this->session).
 * In keeping with the CI_Driver methodology, multiple drivers may be loaded, although this might be a bit confusing.
 * The CI_Session library class keeps track of the most recently loaded driver as "current" to call for driver methods.
 * Ideally, one driver is loaded and all calls go directly through the main library interface. However, any methods
 * called through the specific driver will switch the "current" driver to itself before invoking the library method
 * (which will then call back into the driver for low-level operations). So, alternation between two drivers can be
 * achieved by specifying which driver to use for each call (e.g.: $this->session->native->set_userdata('foo', 'bar');
 * $this->session->cookie->userdata('foo'); $this->session->native->unset_userdata('foo');). Notice in the previous
 * example that the _native_ userdata value 'foo' would be set to 'bar', which would NOT be returned by the call for
 * the _cookie_ userdata 'foo', nor would the _cookie_ value be unset by the call to unset the _native_ 'foo' value.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Sessions
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/sessions.html
 */

class CI_Driver_Library {

	/**
	 * Array of drivers that are available to use with the driver class
	 *
	 * @var array
	 */
	protected $valid_drivers = array();

	/**
	 * Name of the current class - usually the driver class
	 *
	 * @var string
	 */
	protected $lib_name;

	/**
	 * Get magic method
	 *
	 * The first time a child is used it won't exist, so we instantiate it
	 * subsequents calls will go straight to the proper child.
	 *
	 * @param	string	Child class name
	 * @return	object	Child class
	 */
	public function __get($child)
	{
		// Try to load the driver
		return $this->load_driver($child);
	}

	/**
	 * Load driver
	 *
	 * Separate load_driver call to support explicit driver load by library or user
	 *
	 * @param	string	Driver name (w/o parent prefix)
	 * @return	object	Child class
	 */
	public function load_driver($child)
	{
		// Get CodeIgniter instance and subclass prefix
		$prefix = config_item('subclass_prefix');

		if ( ! isset($this->lib_name))
		{
			// Get library name without any prefix
			$this->lib_name = str_replace(array('CI_', $prefix), '', get_class($this));
		}

		// The child will be prefixed with the parent lib
		$child_name = $this->lib_name.'_'.$child;

		// See if requested child is a valid driver
		if ( ! in_array($child, $this->valid_drivers))
		{
			// The requested driver isn't valid!
			$msg = 'Invalid driver requested: '.$child_name;
			log_message('error', $msg);
			show_error($msg);
		}

		// Get package paths and filename case variations to search
		$CI = get_instance();
		$paths = $CI->load->get_package_paths(TRUE);

		// Is there an extension?
		$class_name = $prefix.$child_name;
		$found = class_exists($class_name, FALSE);
		if ( ! $found)
		{
			// Check for subclass file
			foreach ($paths as $path)
			{
				// Does the file exist?
				$file = $path.'libraries/'.$this->lib_name.'/drivers/'.$prefix.$child_name.'.php';
				if (file_exists($file))
				{
					// Yes - require base class from BASEPATH
					$basepath = BASEPATH.'libraries/'.$this->lib_name.'/drivers/'.$child_name.'.php';
					if ( ! file_exists($basepath))
					{
						$msg = 'Unable to load the requested class: CI_'.$child_name;
						log_message('error', $msg);
						show_error($msg);
					}

					// Include both sources and mark found
					include_once($basepath);
					include_once($file);
					$found = TRUE;
					break;
				}
			}
		}

		// Do we need to search for the class?
		if ( ! $found)
		{
			// Use standard class name
			$class_name = 'CI_'.$child_name;
			if ( ! class_exists($class_name, FALSE))
			{
				// Check package paths
				foreach ($paths as $path)
				{
					// Does the file exist?
					$file = $path.'libraries/'.$this->lib_name.'/drivers/'.$child_name.'.php';
					if (file_exists($file))
					{
						// Include source
						include_once($file);
						break;
					}
				}
			}
		}

		// Did we finally find the class?
		if ( ! class_exists($class_name, FALSE))
		{
			if (class_exists($child_name, FALSE))
			{
				$class_name = $child_name;
			}
			else
			{
				$msg = 'Unable to load the requested driver: '.$class_name;
				log_message('error', $msg);
				show_error($msg);
			}
		}

		// Instantiate, decorate and add child
		$obj = new $class_name();
		$obj->decorate($this);
		$this->$child = $obj;
		return $this->$child;
	}

}

// --------------------------------------------------------------------------

/**
 * CodeIgniter Driver Class
 *
 * This class enables you to create drivers for a Library based on the Driver Library.
 * It handles the drivers' access to the parent library
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		EllisLab Dev Team
 * @link
 */
class CI_Driver {

	/**
	 * Instance of the parent class
	 *
	 * @var object
	 */
	protected $_parent;

	/**
	 * List of methods in the parent class
	 *
	 * @var array
	 */
	protected $_methods = array();

	/**
	 * List of properties in the parent class
	 *
	 * @var array
	 */
	protected $_properties = array();

	/**
	 * Array of methods and properties for the parent class(es)
	 *
	 * @static
	 * @var	array
	 */
	protected static $_reflections = array();

	/**
	 * Decorate
	 *
	 * Decorates the child with the parent driver lib's methods and properties
	 *
	 * @param	object
	 * @return	void
	 */
	public function decorate($parent)
	{
		$this->_parent = $parent;

		// Lock down attributes to what is defined in the class
		// and speed up references in magic methods

		$class_name = get_class($parent);

		if ( ! isset(self::$_reflections[$class_name]))
		{
			$r = new ReflectionObject($parent);

			foreach ($r->getMethods() as $method)
			{
				if ($method->isPublic())
				{
					$this->_methods[] = $method->getName();
				}
			}

			foreach ($r->getProperties() as $prop)
			{
				if ($prop->isPublic())
				{
					$this->_properties[] = $prop->getName();
				}
			}

			self::$_reflections[$class_name] = array($this->_methods, $this->_properties);
		}
		else
		{
			list($this->_methods, $this->_properties) = self::$_reflections[$class_name];
		}
	}

	// --------------------------------------------------------------------

	/**
	 * __call magic method
	 *
	 * Handles access to the parent driver library's methods
	 *
	 * @param	string
	 * @param	array
	 * @return	mixed
	 */
	public function __call($method, $args = array())
	{
		if (in_array($method, $this->_methods))
		{
			return call_user_func_array(array($this->_parent, $method), $args);
		}

		$trace = debug_backtrace();
		_exception_handler(E_ERROR, "No such method '{$method}'", $trace[1]['file'], $trace[1]['line']);
		exit(6); // EXIT_UNKNOWN_METHOD
	}

	// --------------------------------------------------------------------

	/**
	 * __get magic method
	 *
	 * Handles reading of the parent driver library's properties
	 *
	 * @param	string
	 * @return	mixed
	 */
	public function __get($var)
	{
		if (in_array($var, $this->_properties))
		{
			return $this->_parent->$var;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * __set magic method
	 *
	 * Handles writing to the parent driver library's properties
	 *
	 * @param	string
	 * @param	array
	 * @return	mixed
	 */
	public function __set($var, $val)
	{
		if (in_array($var, $this->_properties))
		{
			$this->_parent->$var = $val;
		}
	}

}

class CI_Session extends CI_Driver_Library {

	/**
	 * Initialization parameters
	 *
	 * @var	array
	 */
	public $params = array();

	/**
	 * Valid drivers list
	 *
	 * @var	array
	 */
	public $valid_drivers = array('native',	'cookie');

	/**
	 * Current driver in use
	 *
	 * @var	string
	 */
	public $current = NULL;

	/**
	 * User data
	 *
	 * @var	array
	 */
	protected $userdata = array();

	// ------------------------------------------------------------------------

	const FLASHDATA_KEY = 'flash';
	const FLASHDATA_NEW = ':new:';
	const FLASHDATA_OLD = ':old:';
	const FLASHDATA_EXP = ':exp:';
	const EXPIRATION_KEY = '__expirations';
	const TEMP_EXP_DEF = 300;

	// ------------------------------------------------------------------------

	/**
	 * CI_Session constructor
	 *
	 * The constructor loads the configured driver ('sess_driver' in config.php or as a parameter), running
	 * routines in its constructor, and manages flashdata aging.
	 *
	 * @param	array	Configuration parameters
	 * @return	void
	 */
	public function __construct(array $params = array())
	{
		$_config =& get_instance()->config;

		// No sessions under CLI
		if (is_cli())
		{
			return;
		}

		log_message('debug', 'CI_Session Class Initialized');

		// Add possible extra entries to our valid drivers list
		$drivers = isset($params['sess_valid_drivers']) ? $params['sess_valid_drivers'] : $_config->item('sess_valid_drivers');
		if ( ! empty($drivers))
		{
			$drivers = array_map('strtolower', (array) $drivers);
			$this->valid_drivers = array_merge($this->valid_drivers, array_diff($drivers, $this->valid_drivers));
		}

		// Get driver to load
		$driver = isset($params['sess_driver']) ? $params['sess_driver'] : $_config->item('sess_driver');
		if ( ! $driver)
		{
			log_message('debug', "Session: No driver name is configured, defaulting to 'cookie'.");
			$driver = 'cookie';
		}

		if ( ! in_array($driver, $this->valid_drivers))
		{
			log_message('error', 'Session: Configured driver name is not valid, aborting.');
			return;
		}

		// Save a copy of parameters in case drivers need access
		$this->params = $params;

		// Load driver and get array reference
		$this->load_driver($driver);

		// Delete 'old' flashdata (from last request)
		$this->_flashdata_sweep();

		// Mark all new flashdata as old (data will be deleted before next request)
		$this->_flashdata_mark();

		// Delete expired tempdata
		$this->_tempdata_sweep();

		log_message('debug', 'CI_Session routines successfully run');
	}

	// ------------------------------------------------------------------------

	/**
	 * Loads session storage driver
	 *
	 * @param	string	Driver classname
	 * @return	object	Loaded driver object
	 */
	public function load_driver($driver)
	{
		// Save reference to most recently loaded driver as library default and sync userdata
		$this->current = parent::load_driver($driver);
		$this->userdata =& $this->current->get_userdata();
		return $this->current;
	}

	// ------------------------------------------------------------------------

	/**
	 * Select default session storage driver
	 *
	 * @param	string	Driver name
	 * @return	void
	 */
	public function select_driver($driver)
	{
		// Validate driver name
		$prefix = (string) get_instance()->config->item('subclass_prefix');
		$child = strtolower(str_replace(array('CI_', $prefix, $this->lib_name.'_'), '', $driver));
		if (in_array($child, array_map('strtolower', $this->valid_drivers)))
		{
			// See if driver is loaded
			if (isset($this->$child))
			{
				// See if driver is already current
				if ($this->$child !== $this->current)
				{
					// Make driver current and sync userdata
					$this->current = $this->$child;
					$this->userdata =& $this->current->get_userdata();
				}
			}
			else
			{
				// Load new driver
				$this->load_driver($child);
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Destroy the current session
	 *
	 * @return	void
	 */
	public function sess_destroy()
	{
		// Just call destroy on driver
		$this->current->sess_destroy();
	}

	// ------------------------------------------------------------------------

	/**
	 * Regenerate the current session
	 *
	 * @param	bool	Destroy session data flag (default: false)
	 * @return	void
	 */
	public function sess_regenerate($destroy = FALSE)
	{
		// Call regenerate on driver and resync userdata
		$this->current->sess_regenerate($destroy);
		$this->userdata =& $this->current->get_userdata();
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch a specific item from the session array
	 *
	 * @param	string	Item key
	 * @return	string	Item value or NULL if not found
	 */
	public function userdata($item = NULL)
	{
		if (isset($item))
		{
			return isset($this->userdata[$item]) ? $this->userdata[$item] : NULL;
		}

		return isset($this->userdata) ? $this->userdata : array();
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch all session data
	 *
	 * @deprecated	3.0.0	Use userdata() with no parameters instead
	 * @return	array	User data array
	 */
	public function all_userdata()
	{
		return isset($this->userdata) ? $this->userdata : array();
	}

	// ------------------------------------------------------------------------

	/**
	 * Add or change data in the "userdata" array
	 *
	 * @param	mixed	Item name or array of items
	 * @param	string	Item value or empty string
	 * @return	void
	 */
	public function set_userdata($newdata, $newval = '')
	{
		// Wrap params as array if singular
		if (is_string($newdata))
		{
			$newdata = array($newdata => $newval);
		}

		// Set each name/value pair
		if (count($newdata) > 0)
		{
			foreach ($newdata as $key => $val)
			{
				$this->userdata[$key] = $val;
			}
		}

		// Tell driver data changed
		$this->current->sess_save();
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete a session variable from the "userdata" array
	 *
	 * @param	mixed	Item name or array of item names
	 * @return	void
	 */
	public function unset_userdata($newdata)
	{
		// Wrap single name as array
		if (is_string($newdata))
		{
			$newdata = array($newdata => '');
		}

		// Unset each item name
		if (count($newdata) > 0)
		{
			foreach (array_keys($newdata) as $key)
			{
				unset($this->userdata[$key]);
			}
		}

		// Tell driver data changed
		$this->current->sess_save();
	}

	// ------------------------------------------------------------------------

	/**
	 * Determine if an item exists
	 *
	 * @param	string	Item name
	 * @return	bool
	 */
	public function has_userdata($item)
	{
		return isset($this->userdata[$item]);
	}

	// ------------------------------------------------------------------------

	/**
	 * Add or change flashdata, only available until the next request
	 *
	 * @param	mixed	Item name or array of items
	 * @param	string	Item value or empty string
	 * @return	void
	 */
	public function set_flashdata($newdata, $newval = '')
	{
		// Wrap item as array if singular
		if (is_string($newdata))
		{
			$newdata = array($newdata => $newval);
		}

		// Prepend each key name and set value
		if (count($newdata) > 0)
		{
			foreach ($newdata as $key => $val)
			{
				$flashdata_key = self::FLASHDATA_KEY.self::FLASHDATA_NEW.$key;
				$this->set_userdata($flashdata_key, $val);
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Keeps existing flashdata available to next request.
	 *
	 * @param	mixed	Item key(s)
	 * @return	void
	 */
	public function keep_flashdata($key)
	{

		if (is_array($key))
		{
			foreach ($key as $k)
			{
				$this->keep_flashdata($k);
			}

			return;
		}

		// 'old' flashdata gets removed. Here we mark all flashdata as 'new' to preserve it from _flashdata_sweep()
		// Note the function will return NULL if the $key provided cannot be found
		$old_flashdata_key = self::FLASHDATA_KEY.self::FLASHDATA_OLD.$key;
		$value = $this->userdata($old_flashdata_key);

		$new_flashdata_key = self::FLASHDATA_KEY.self::FLASHDATA_NEW.$key;
		$this->set_userdata($new_flashdata_key, $value);
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch a specific flashdata item from the session array
	 *
	 * @param	string	Item key
	 * @return	string
	 */
	public function flashdata($key = NULL)
	{
		if (isset($key))
		{
			return $this->userdata(self::FLASHDATA_KEY.self::FLASHDATA_OLD.$key);
		}

		// Get our flashdata items from userdata
		$out = array();
		foreach ($this->userdata() as $key => $val)
		{
			if (strpos($key, self::FLASHDATA_KEY.self::FLASHDATA_OLD) !== FALSE)
			{
				$key = str_replace(self::FLASHDATA_KEY.self::FLASHDATA_OLD, '', $key);
				$out[$key] = $val;
			}
		}

		return $out;
	}

	// ------------------------------------------------------------------------

	/**
	 * Add or change tempdata, only available until expiration
	 *
	 * @param	mixed	Item name or array of items
	 * @param	string	Item value or empty string
	 * @param	int	Item lifetime in seconds or 0 for default
	 * @return	void
	 */
	public function set_tempdata($newdata, $newval = '', $expire = 0)
	{
		// Set expiration time
		$expire = time() + ($expire ? $expire : self::TEMP_EXP_DEF);

		// Wrap item as array if singular
		if (is_string($newdata))
		{
			$newdata = array($newdata => $newval);
		}

		// Get or create expiration list
		$expirations = $this->userdata(self::EXPIRATION_KEY);
		if ( ! $expirations)
		{
			$expirations = array();
		}

		// Prepend each key name and set value
		if (count($newdata) > 0)
		{
			foreach ($newdata as $key => $val)
			{
				$tempdata_key = self::FLASHDATA_KEY.self::FLASHDATA_EXP.$key;
				$expirations[$tempdata_key] = $expire;
				$this->set_userdata($tempdata_key, $val);
			}
		}

		// Update expiration list
		$this->set_userdata(self::EXPIRATION_KEY, $expirations);
	}

	// ------------------------------------------------------------------------

	/**
	 * Delete a temporary session variable from the "userdata" array
	 *
	 * @param	mixed	Item name or array of item names
	 * @return	void
	 */
	public function unset_tempdata($newdata)
	{
		// Get expirations list
		$expirations = $this->userdata(self::EXPIRATION_KEY);
		if (empty($expirations))
		{
			// Nothing to do
			return;
		}

		// Wrap single name as array
		if (is_string($newdata))
		{
			$newdata = array($newdata => '');
		}

		// Prepend each item name and unset
		if (count($newdata) > 0)
		{
			foreach (array_keys($newdata) as $key)
			{
				$tempdata_key = self::FLASHDATA_KEY.self::FLASHDATA_EXP.$key;
				unset($expirations[$tempdata_key]);
				$this->unset_userdata($tempdata_key);
			}
		}

		// Update expiration list
		$this->set_userdata(self::EXPIRATION_KEY, $expirations);
	}

	// ------------------------------------------------------------------------

	/**
	 * Fetch a specific tempdata item from the session array
	 *
	 * @param	string	Item key
	 * @return	string
	 */
	public function tempdata($key = NULL)
	{
		if (isset($key))
		{
			return $this->userdata(self::FLASHDATA_KEY.self::FLASHDATA_EXP.$key);
		}

		// Get our tempdata items from userdata
		$out = array();
		foreach ($this->userdata() as $key => $val)
		{
			if (strpos($key, self::FLASHDATA_KEY.self::FLASHDATA_EXP) !== FALSE)
			{
				$key = str_replace(self::FLASHDATA_KEY.self::FLASHDATA_EXP, '', $key);
				$out[$key] = $val;
			}
		}

		return $out;
	}

	// ------------------------------------------------------------------------

	/**
	 * Identifies flashdata as 'old' for removal
	 * when _flashdata_sweep() runs.
	 *
	 * @return	void
	 */
	protected function _flashdata_mark()
	{
		foreach ($this->userdata() as $name => $value)
		{
			$parts = explode(self::FLASHDATA_NEW, $name);
			if (count($parts) === 2)
			{
				$this->set_userdata(self::FLASHDATA_KEY.self::FLASHDATA_OLD.$parts[1], $value);
				$this->unset_userdata($name);
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Removes all flashdata marked as 'old'
	 *
	 * @return	void
	 */
	protected function _flashdata_sweep()
	{
		$userdata = $this->userdata();
		foreach (array_keys($userdata) as $key)
		{
			if (strpos($key, self::FLASHDATA_OLD))
			{
				$this->unset_userdata($key);
			}
		}
	}

	// ------------------------------------------------------------------------

	/**
	 * Removes all expired tempdata
	 *
	 * @return	void
	 */
	protected function _tempdata_sweep()
	{
		// Get expirations list
		$expirations = $this->userdata(self::EXPIRATION_KEY);
		if (empty($expirations))
		{
			// Nothing to do
			return;
		}

		// Unset expired elements
		$now = time();
		$userdata = $this->userdata();
		foreach (array_keys($userdata) as $key)
		{
			if (strpos($key, self::FLASHDATA_EXP) && $expirations[$key] < $now)
			{
				unset($expirations[$key]);
				$this->unset_userdata($key);
			}
		}

		// Update expiration list
		$this->set_userdata(self::EXPIRATION_KEY, $expirations);
	}

}

// ------------------------------------------------------------------------

/**
 * CI_Session_driver Class
 *
 * Extend this class to make a new CI_Session driver.
 * A CI_Session driver basically manages an array of name/value pairs with some sort of storage mechanism.
 * To make a new driver, derive from (extend) CI_Session_driver. Overload the initialize method and read or create
 * session data. Then implement a save handler to write changed data to storage (sess_save), a destroy handler
 * to remove deleted data (sess_destroy), and an access handler to expose the data (get_userdata).
 * Put your driver in the libraries/Session/drivers folder anywhere in the loader paths. This includes the
 * application directory, the system directory, or any path you add with $CI->load->add_package_path().
 * Your driver must be named CI_Session_<name>, and your filename must be Session_<name>.php,
 * preferably also capitalized. (e.g.: CI_Session_foo in libraries/Session/drivers/Session_foo.php)
 * Then specify the driver by setting 'sess_driver' in your config file or as a parameter when loading the CI_Session
 * object. (e.g.: $config['sess_driver'] = 'foo'; OR $CI->load->driver('session', array('sess_driver' => 'foo')); )
 * Already provided are the Native driver, which manages the native PHP $_SESSION array, and
 * the Cookie driver, which manages the data in a browser cookie, with optional extra storage in a database table.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Sessions
 * @author		EllisLab Dev Team
 */
abstract class CI_Session_driver extends CI_Driver {

	/**
	 * CI Singleton
	 *
	 * @see	get_instance()
	 * @var	object
	 */
	protected $CI;

	// ------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * Gets the CI singleton, so that individual drivers
	 * don't have to do it separately.
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->CI =& get_instance();
	}

	// ------------------------------------------------------------------------

	/**
	 * Decorate
	 *
	 * Decorates the child with the parent driver lib's methods and properties
	 *
	 * @param	object	Parent library object
	 * @return	void
	 */
	public function decorate($parent)
	{
		// Call base class decorate first
		parent::decorate($parent);

		// Call initialize method now that driver has access to $this->_parent
		$this->initialize();
	}

	// ------------------------------------------------------------------------

	/**
	 * __call magic method
	 *
	 * Handles access to the parent driver library's methods
	 *
	 * @param	string	Library method name
	 * @param	array	Method arguments (default: none)
	 * @return	mixed
	 */
	public function __call($method, $args = array())
	{
		// Make sure the parent library uses this driver
		$this->_parent->select_driver(get_class($this));
		return parent::__call($method, $args);
	}

	// ------------------------------------------------------------------------

	/**
	 * Initialize driver
	 *
	 * @return	void
	 */
	protected function initialize()
	{
		// Overload this method to implement initialization
	}

	// ------------------------------------------------------------------------

	/**
	 * Save the session data
	 *
	 * Data in the array has changed - perform any storage synchronization
	 * necessary. The child class MUST implement this abstract method!
	 *
	 * @return	void
	 */
	abstract public function sess_save();

	// ------------------------------------------------------------------------

	/**
	 * Destroy the current session
	 *
	 * Clean up storage for this session - it has been terminated.
	 * The child class MUST implement this abstract method!
	 *
	 * @return	void
	 */
	abstract public function sess_destroy();

	// ------------------------------------------------------------------------

	/**
	 * Regenerate the current session
	 *
	 * Regenerate the session ID.
	 * The child class MUST implement this abstract method!
	 *
	 * @param	bool	Destroy session data flag (default: false)
	 * @return	void
	 */
	abstract public function sess_regenerate($destroy = FALSE);

	// ------------------------------------------------------------------------

	/**
	 * Get a reference to user data array
	 *
	 * Give array access to the main CI_Session object.
	 * The child class MUST implement this abstract method!
	 *
	 * @return	array	Reference to userdata
	 */
	abstract public function &get_userdata();

}

/* End of file Session.php */
/* Location: ./system/libraries/Session/Session.php */
