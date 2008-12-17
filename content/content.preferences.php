<?php
	require_once(TOOLKIT . '/class.administrationpage.php');
	@require_once(EXTENSIONS . "/ysboss/lib/languages.php");

	Class contentExtensionYSBOSSPreferences extends AdministrationPage{

		public $languages;

		private $_driver;

		function __construct(&$parent){
			parent::__construct($parent);

			$this->languages = libYSBOSSLanguages::getList();
		}

		function view(){
			$fields = $_POST['fields'];

			$this->setPageType('form');
			$this->setTitle('Symphony &ndash; Yahoo! Search BOSS');
			$this->appendSubheading('Yahoo! Search BOSS');
			$this->addScriptToHead(URL . '/extensions/ysboss/assets/admin.js', 500);

			$link = new XMLElement('link');
			$link->setAttributeArray(array('rel' => 'stylesheet', 'type' => 'text/css', 'media' => 'screen', 'href' => URL . '/extensions/ysboss/assets/admin.css'));
			$this->addElementToHead($link, 500);

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->setAttribute('id', 'help');
			$fieldset->appendChild(new XMLElement('legend', 'Information'));
			$fieldset->appendChild(new XMLElement('p', '<a href="http://developer.yahoo.com/search/boss/boss_guide/" title="Read Yahoo! Search BOSS documentation" class="ysboss">Documentation</a>', array('class' => 'help')));
			$content = <<<END
			<p>With <a href="http://developer.yahoo.com/search/boss/" title="Read more">Yahoo! Search BOSS</a> you can add search functionality to your Symphony orchestrated site.</p>
			<p>To do that you have to add "Yahoo! Search BOSS" data source to page where you want to get results. Data source needs query parameter, which you can pass through URL schema or GET variables. It also handles page parameter which tells it which page of search results it should provide.</p>
			<p>For example, after configuring URL Parameters of page to "q/p" and using default settings on YSBOSS page, you can put this in XSLT source of page:</p>
			<p><code>
&lt;xsl:template match="data"&gt;<br />
&lt;form action="{\$root}/{\$current-page}" method="GET"&gt;<br />
&lt;input name="q" value="{ysboss/query}" /&gt;<br />
&lt;input type="submit" value="Search" /&gt;<br />
&lt;/form&gt;<br />
&lt;ul class="entryList"&gt;<br />
&lt;xsl:apply-templates select="ysboss//result" /&gt;<br />
&lt;/ul&gt;<br />
&lt;/xsl:template&gt;<br /><br />
&lt;xsl:template match="result"&gt;<br />
&lt;li&gt;&lt;dl&gt;<br />
&lt;dt&gt;&lt;a href="{clickurl}" target="_blank"&gt;&lt;xsl:value-of select="title" disable-output-escaping="yes"/&gt;&lt;/a&gt;&lt;/dt&gt;<br />
&lt;dd&gt;&lt;xsl:value-of select="abstract" disable-output-escaping="yes"/&gt;&lt;/dd&gt;<br />
&lt;/dl&gt;&lt;/li&gt;<br />
&lt;/xsl:template&gt;<br />
			</code></p>
			<p><b>NOTE:</b> There is a requirement to include the clickurl in anchor link of your search results.</p>
			<p>That will allow users to enter search query, click "Search" button and get results, just like on <a href="http://yahoo.com">Yahoo.com</a> page.</p>
END;
			$fieldset->appendChild(new XMLElement('div', $content));
			$this->Form->appendChild($fieldset);


			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Essentials'));
			$fieldset->appendChild(new XMLElement('p', 'Use <code>{$param}</code> syntax to search by page parameters.', array('class' => 'help')));

			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');

			$label = Widget::Label('Search for');
			$label->appendChild(new XMLElement('i', 'Default is "{$q:$url-q}"'));
			if (!($temp = $this->_Parent->Configuration->get('qname', 'ysboss'))) $temp = '{$q:$url-q}';
			$label->appendChild(Widget::Input('fields[qname]', $temp));
			$div->appendChild($label);

			$label = Widget::Label('BOSS Application ID');
			$label->appendChild(new XMLElement('i', 'This required argument supplies your <a href="https://developer.yahoo.com/wsregapp/">BOSS APPID</a>'));
			$label->appendChild(Widget::Input('fields[appid]', $this->_Parent->Configuration->get('appid', 'ysboss')));
			if(isset($this->_errors['appid'])) $div->appendChild(Widget::wrapFormElementWithError($label, $this->_errors['appid']));
			else $div->appendChild($label);

			$fieldset->appendChild($div);
			$this->Form->appendChild($fieldset);

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Filter results'));
			$fieldset->appendChild(new XMLElement('p', 'Use <code>{$param}</code> syntax to filter by page parameters.', array('class' => 'help')));

			$label = Widget::Label('Sites');
			$label->appendChild(new XMLElement('i', 'Optionally restrict search results to a list of comma separated sites, e.g., "abc.com,cnn.com"'));
			$label->appendChild(Widget::Input('fields[sites]', $this->_Parent->Configuration->get('sites', 'ysboss')));
			$fieldset->appendChild($label);

			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');

			$label = Widget::Label('Language and region');
			$label->appendChild(new XMLElement('i', 'Search data in selected language'));
			$options = array();
			$temp = $this->_Parent->Configuration->get('lang', 'ysboss');
			foreach ($this->languages as $name => $code) {
				$options[] = array($name, ($name==$temp), $name);
			}
			$label->appendChild(Widget::Select('fields[lang]', $options));
			$div->appendChild($label);

			$label = Widget::Label('Filter');
			$label->appendChild(new XMLElement('i', 'Additional filtering of search results'));
			$vars = array('Porn' => '-porn', 'Hate (English only)' => '-hate');
			$options = array();
			$temp = explode(',', $this->_Parent->Configuration->get('filters', 'ysboss'));
			foreach ($vars as $name => $code) {
				$options[] = array($code, in_array($code, $temp), $name);
			}
			$label->appendChild(Widget::Select('fields[filters][]', $options, array('multiple' => 'multiple')));
			$div->appendChild($label);

			$fieldset->appendChild($div);
			$this->Form->appendChild($fieldset);

			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Limiting'));
			$fieldset->appendChild(new XMLElement('p', 'Use <code>{$param}</code> syntax to limit by page parameters.', array('class' => 'help')));

			$div = new XMLElement('div');
			$div->setAttribute('class', 'group');

			$label = Widget::Label();
			$input = Widget::Input('fields[count]', $this->_Parent->Configuration->get('count', 'ysboss'), NULL, array('size' => 6));
			$label->setValue('Show maximum of ' . $input->generate(false) . ' results');
			$div->appendChild($label);

			$label = Widget::Label();
			if (!($temp = $this->_Parent->Configuration->get('pname', 'ysboss'))) $temp = '{$p:$url-p}';
			$input = Widget::Input('fields[pname]', $temp, NULL, array('size' => 6));
			$label->setValue('Show page ' . $input->generate(false) . ' of results');
			$div->appendChild($label);

			$fieldset->appendChild($div);
			$this->Form->appendChild($fieldset);

			$div = new XMLElement('div');
			$div->setAttribute('class', 'actions');
			$div->appendChild(Widget::Input('action[save]', 'Save Changes', 'submit', array('accesskey' => 's')));

			$this->Form->appendChild($div);
		}

		function action() {
			if (array_key_exists('save', $_POST['action'])) $this->save();
		}

		function save() {
			$fields = $_POST['fields'];

			if (!trim($fields['appid'])) $this->_errors['appid'] = 'Application ID is required';

			if(!empty($this->_errors)) return false;

			$this->_Parent->Configuration->set('qname', $fields['qname'], 'ysboss');
			$this->_Parent->Configuration->set('pname', $fields['pname'], 'ysboss');

			$this->_Parent->Configuration->set('count', $fields['count'], 'ysboss');

			$this->_Parent->Configuration->set('appid', trim($fields['appid']), 'ysboss');

			$this->_Parent->Configuration->set('sites', trim($fields['sites']), 'ysboss');

			if (in_array($fields['lang'], $this->languages)) $this->_Parent->Configuration->set('lang', trim($fields['lang']), 'ysboss');

			if (is_array($fields['filters']) && count($fields['filters']) > 0) {
				$this->_Parent->Configuration->set('filters', implode(',', $fields['filters']), 'ysboss');
			}
			else $this->_Parent->Configuration->set('filters', '', 'ysboss');

			return $this->_Parent->saveConfig();
		}
	}

?>