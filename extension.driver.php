<?php
	Class extension_ysboss extends Extension{
	
		public function about(){
			return array('name' => 'Yahoo! Search BOSS',
						 'version' => '1.1',
						 'release-date' => '2008-12-17',
						 'author' => array('name' => 'Marcin Konicki',
										   'website' => 'http://ahwayakchih.neoni.net',
										   'email' => 'ahwayakchih@neoni.net'),
						 'description' => 'Use Yahoo! Search BOSS API as data source in Symphony.'
				 		);
		}

		function install(){
			$about = $this->about();

			$this->_Parent->Configuration->set('count', '10', 'ysboss');
			$this->_Parent->Configuration->set('version', $about['version'], 'ysboss');
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

		function update(){
			$lastVersion = floatval($this->_Parent->Configuration->get('version', 'ysboss'));
			if (!$lastVersion || $lastVersion < 1.1) {
				if (!($temp = $this->_Parent->Configuration->get('qname', 'ysboss'))) $temp = 'q';
				$this->_Parent->Configuration->set('qname', '$'.$temp.':$url-'.$temp, 'ysboss');

				if (!($temp = $this->_Parent->Configuration->get('pname', 'ysboss'))) $temp = 'p';
				$this->_Parent->Configuration->set('pname', '$'.$temp.':$url-'.$temp, 'ysboss');
			}
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