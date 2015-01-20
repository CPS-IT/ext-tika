<?php
namespace ApacheSolrForTypo3\Tika\Backend\SolrModule;

/***************************************************************
*  Copyright notice
*
*  (c) 2015 Ingo Renner <ingo@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

use ApacheSolrForTypo3\Solr\Backend\SolrModule\AbstractModuleController;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * Tika Control Panel
 *
 * @package ApacheTikaForTypo3\Tika\Backend\Module
 * @author Ingo Renner <ingo@typo3.org>
 */
class TikaControlPanelModuleController extends AbstractModuleController {

	/**
	 * Module name, used to identify a module f.e. in URL parameters.
	 *
	 * @var string
	 */
	protected $moduleName = 'TikaControlPanel';

	/**
	 * Module title, shows up in the module menu.
	 *
	 * @var string
	 */
	protected $moduleTitle = 'Tika';

	/**
	 * Tika configuration
	 *
	 * @var array
	 */
	protected $tikaConfiguration = array();


	/**
	 * Initializes resources commonly needed for several actions.
	 *
	 * @return void
	 */
	protected function initializeAction() {
		parent::initializeAction();

		$this->tikaConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tika']);
	}

	/**
	 * Index action
	 *
	 * @throws \Exception
	 */
	public function indexAction() {
		$this->view->assign('configuration', $this->tikaConfiguration);
		$this->view->assign('extractor',     ucfirst($this->tikaConfiguration['extractor']));

		$this->view->assign(
			'server',
			array(
				'jarAvailable'   => $this->isTikaServerJarAvailable(),
				'isRunning'      => $this->isTikaServerRunning(),
				'isControllable' => $this->isTikaServerControllable(),
				'pid'            => $this->getTikaServerPid()
			)
		);
	}

	/**
	 * Starts the Tika server
	 *
	 */
	public function startServerAction() {
		$command = CommandUtility::getCommand('java')
			. ' -jar ' . escapeshellarg(
				GeneralUtility::getFileAbsFileName(
					$this->tikaConfiguration['tikaServerPath'],
					FALSE
				)
			)
			. ' -p ' . escapeshellarg($this->tikaConfiguration['tikaServerPort']);

		$process = GeneralUtility::makeInstance(
			'ApacheSolrForTypo3\\Tika\\Process',
			$command
		);
		$pid = $process->getPid();

		$registry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
		$registry->set('tx_tika', 'server.pid', $pid);

		// wait for Tika to start so that when we return to indexAction
		// it show Tika running
		sleep(2);

		$this->forwardToIndex();
	}

	/**
	 * Stops the Tika server
	 *
	 */
	public function stopServerAction() {
		$registry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
		$pid = $registry->get('tx_tika', 'server.pid');

		$process = GeneralUtility::makeInstance('ApacheSolrForTypo3\\Tika\\Process');
		$process->setPid($pid);
		$process->stop();

		// unset pid in registry
		$registry->remove('tx_tika', 'server.pid');

		$this->forwardToIndex();
	}

	/**
	 * Constructs the Tika server URL.
	 *
	 * @return string Tika server URL
	 */
	protected function getTikaServerUrl() {
		$tikaUrl = $this->tikaConfiguration['tikaServerScheme']
			. '://'
			. $this->tikaConfiguration['tikaServerHost']
			. ':' . $this->tikaConfiguration['tikaServerPort'];

		return $tikaUrl;
	}

	/**
	 * Tries to connect to Tika server
	 *
	 * @return bool TRUE if the Tika server responds, FALSE otherwise.
	 * @throws \Exception
	 */
	protected function isTikaServerRunning() {
		$tikaUrl = $this->getTikaServerUrl();
		$tikaRunning = FALSE;

		try {
			$tikaPing    = file_get_contents($tikaUrl . '/tika');
			$tikaRunning = GeneralUtility::isFirstPartOfStr($tikaPing, 'This is Tika Server.');
		} catch (\Exception $e) {
			$message = $e->getMessage();
			if (strpos($message, 'Connection refused') === FALSE &&
				strpos($message, 'HTTP request failed') === FALSE) {
				// If the server is simply not available ti would say Connection refused
				// since that is not the case something else went wrong
				throw $e;
			}
		}

		return $tikaRunning;
	}

	/**
	 * Returns the pid if the Tika server has been started through the backend
	 * module.
	 *
	 * @return integer|null Tika Server pid or null if not found
	 */
	protected function getTikaServerPid() {
		$registry = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Registry');
		$serverPid = $registry->get('tx_tika', 'server.pid');

		return $serverPid;
	}

	/**
	 * Checks whether the server jar has been configured properly.
	 *
	 * @return bool TRUE if a path for the jar has been configure and the path exists
	 */
	protected function isTikaServerJarAvailable() {
		$serverJarSet    = !empty($this->tikaConfiguration['tikaServerPath']);
		$serverJarExists = file_exists($this->tikaConfiguration['tikaServerPath']);

		return ($serverJarSet && $serverJarExists);
	}

	/**
	 * Checks whether Tika server can be controlled (started/stopped)
	 *
	 * @return bool TRUE if Tika server can be started/stopped
	 * @throws \Exception
	 */
	protected function isTikaServerControllable() {
		$jarAvailable = $this->isTikaServerJarAvailable();
		$running      = $this->isTikaServerRunning();
		$pid          = $this->getTikaServerPid();

		$controllable = FALSE;
		if ($running && $jarAvailable && !is_null($pid)) {
			$controllable = TRUE;
		} elseif (!$running && $jarAvailable) {
			$controllable = TRUE;
		}

		return $controllable;
	}

}
