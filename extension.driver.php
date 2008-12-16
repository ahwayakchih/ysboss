<?php
	Class extension_ysboss extends Extension{
	
		public function about(){
			return array('name' => 'Yahoo! Search BOSS',
						 'version' => '1.0',
						 'release-date' => '2008-12-16',
						 'author' => array('name' => 'Marcin Konicki',
										   'website' => 'http://ahwayakchih.neoni.net',
										   'email' => 'ahwayakchih@neoni.net'),
						 'description' => 'Use Yahoo! Search BOSS API as data source in Symphony.'
				 		);
		}

		function install(){
			$this->_Parent->Configuration->set('count', '10', 'ysboss');
			return $this->_Parent->saveConfig();
		}

		function uninstall(){
			$this->_Parent->Configuration->remove('ysboss');
			return $this->_Parent->saveConfig();
		}

		function enable(){
			if (!$this->_Parent->Configuration->get('count', 'ysboss'))
				return $this->install();
			return true;
		}

		public function fetchNavigation() {
			return array(
				array(
					'location'	=> 300,
					'name'		=> 'YSBOSS',
					'link'		=> '/preferences/',
				)
			);
		}

	}
?>