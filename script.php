<?php

/**
 * SubmitMailer Task Plugin
 *
 * @copyright  Copyright (C) 2016 Tobias Zulauf All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License Version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Database\ParameterType;

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since  3-5
 */
class plgContentSubmitMailerInstallerScript extends InstallerScript
{
	/**
	 * Extension script constructor.
	 *
	 * @since  3-5
	 */
	public function __construct()
	{
		// Define the minumum versions to be supported.
		$this->minimumJoomla = '4.0';
		$this->minimumPhp    = '8.1';
	}

	/**
     * Called after any type of action
     *
     * @param   string     $action     Which action is happening (install|uninstall|discover_install|update)
     * @param   Installer  $installer  The class calling this method
     *
     * @return  boolean  True on success
     *
     * @since   3-6
     */
    public function postflight($action, $installer)
    {
		// Delete the old JSM Update Servers
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__update_sites'))
			->where($db->quoteName('type') . ' = ' . $db->quote('collection'))
			->where($db->quoteName('location') . ' = ' . $db->quote('http://www.jah-tz.de/downloads/jsm/update.xml'));
		$db->setQuery($query);
		$db->execute();

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__update_sites'))
			->where($db->quoteName('type') . ' = ' . $db->quote('extension'))
			->where($db->quoteName('location') . ' = ' . $db->quote('http://www.jah-tz.de/downloads/jsm/sts/update.xml'));
		$db->setQuery($query);
		$db->execute();
	}
}
