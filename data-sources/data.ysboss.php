<?php
	@require_once(EXTENSIONS . "/ysboss/lib/languages.php");

	Class datasourceYSBOSS extends Datasource{
		public $dsParamFILTERS = array(
			'ysboss-q' => '{$q:$url-q}',
			'ysboss-p' => '{$p:$url-p}'
		);

		function __construct(&$parent, $env=NULL, $process_params=true){
			global $settings;

			$this->dsParamFILTERS = array(
				'ysboss-a' => '{'.$settings['ysboss']['appid'].'}',
				'ysboss-q' => '{'.($settings['ysboss']['ysboss'] ? $settings['ysboss']['qname'] : '$q:$url-q').'}',
				'ysboss-p' => '{'.($settings['ysboss']['ysboss'] ? $settings['ysboss']['pname'] : '$p:$url-p').'}'
			);
			if (($temp = $settings['ysboss']['sites'])) $this->dsParamFILTERS['ysboss-s'] = '{'.$settings['ysboss']['sites'].'}';

			$this->languages = $this->languages = libYSBOSSLanguages::getList();

			parent::__construct($parent, $env, $process_params);
		}

		function example(){
			return '
<ysboss>
	<query>test</query>
	<pagination-info total-entries="124710399" total-pages="12471040" entries-per-page="10" current-page="2"></pagination-info>
	<ysearchresponse responsecode="200">

  <prevpage>/ysearch/web/v1/test?format=xml&amp;appid=x_lKE27V34GFL.17c6gUEkR.w4oBoHopPseojpP9G.dzOqdWGsDqk6fLLEKpbdXnE.k-&amp;count=10&amp;start=0</prevpage>

  <nextpage>/ysearch/web/v1/test?format=xml&amp;appid=x_lKE27V34GFL.17c6gUEkR.w4oBoHopPseojpP9G.dzOqdWGsDqk6fLLEKpbdXnE.k-&amp;count=10&amp;start=20</nextpage>
  <resultset_web count="10" start="10" totalhits="124710399" deephits="3720000000">
    <result>
      <abstract>YUI &lt;b&gt;Test&lt;/b&gt; is a testing framework for browser-based JavaScript solutions. &lt;b&gt;...&lt;/b&gt; &lt;b&gt;Test&lt;/b&gt; Reporting. YUI on Mobile Devices. Support &amp;amp; Community. Filing Bugs and Feature &lt;b&gt;...&lt;/b&gt;</abstract>

      <clickurl>http://lrd.yahooapis.com/_ylc=X3oDMTVjMjYxcTMzBF9TAzIwMjMxNTI3MDIEYXBwaWQDeF9sS0UyN1YzNEdGTC4xN2M2Z1VFa1IudzRvQm9Ib3BQc2VvanBQOUcuZHpPcWRXR3NEcWs2ZkxMRUtwYmRYbkUuay0EcG9zAzAEc2VydmljZQNZU2VhcmNoV2ViBHNsawN0aXRsZQRzcmNwdmlkA1E5aE42ODYuSWowSE5YTXhaSUpNRndSS1ZtX3hXRWxIcFhRQUJRZzM-/SIG=11cifnpsi/**http%3A//developer.yahoo.com/yui/yuitest/</clickurl>
      <date>2008/12/04</date>
      <dispurl>&lt;b&gt;developer.yahoo.com&lt;/b&gt;/yui/yui&lt;b&gt;test&lt;/b&gt;</dispurl>
      <size>81719</size>

      <title>Yahoo! UI Library: YUI &lt;b&gt;Test&lt;/b&gt;</title>
      <url>http://developer.yahoo.com/yui/yuitest/</url>
    </result>
    <result>
      <abstract>Searchable database &lt;b&gt;...&lt;/b&gt; In order to search the &lt;b&gt;Test&lt;/b&gt; Collection at ETS, you will need Microsoft Internet &lt;b&gt;...&lt;/b&gt; Search &lt;b&gt;Test&lt;/b&gt; Link Database. Skip "Corporate &lt;b&gt;...&lt;/b&gt;</abstract>

      <clickurl>http://lrd.yahooapis.com/_ylc=X3oDMTVjNXFiNzM3BF9TAzIwMjMxNTI3MDIEYXBwaWQDeF9sS0UyN1YzNEdGTC4xN2M2Z1VFa1IudzRvQm9Ib3BQc2VvanBQOUcuZHpPcWRXR3NEcWs2ZkxMRUtwYmRYbkUuay0EcG9zAzEEc2VydmljZQNZU2VhcmNoV2ViBHNsawN0aXRsZQRzcmNwdmlkA1E5aE42ODYuSWowSE5YTXhaSUpNRndSS1ZtX3hXRWxIcFhRQUJRZzM-/SIG=11b5hkqa9/**http%3A//www.ets.org/testcoll/index.html</clickurl>
      <date>2008/11/29</date>
      <dispurl>www.&lt;b&gt;ets.org&lt;/b&gt;/&lt;b&gt;test&lt;/b&gt;coll/index.html</dispurl>

      <size>14128</size>
      <title>&lt;b&gt;Test&lt;/b&gt; Link Overview</title>
      <url>http://www.ets.org/testcoll/index.html</url>
    </result>
 </resultset_web>
</ysearchresponse>
	</ysboss>
';
		}

		function about(){
			return array(
				"name" => "Yahoo! Search BOSS",
				"description" => "Calls Yahoo! Search BOSS API and returns results in XML.",
				"author" => array("name" => "Marcin Konicki",
					"website" => "http://ahwayakchih.neoni.net",
					"email" => "ahwayakchih@neoni.net"),
				"version" => "1.1",
				"release-date" => "2008-12-17",
				"recognised-url-param" => array('ysboss-q', 'ysboss-p'),
			);
		}

		function grab($param=array()){
			global $settings;

			$q = trim($this->dsParamFILTERS['ysboss-q']);
			if (!$q) return NULL;

			$p = '?format=xml';
			$p .= '&appid='.urlencode($this->dsParamFILTERS['ysboss-a']);

			$count = ($settings['ysboss']['count'] ? $settings['ysboss']['count'] : 10);
			if (!$count) $count = 10;
			else if ($count < 1) $count = 1;
			else if ($count > 50) $count = 50;

			$page = intval($this->dsParamFILTERS['ysboss-p']);
			$page -= 1; // Pagination counts from 1, not 0
			if (!$page || $page < 0) $page = 0;
			$p .= '&start='.($page*$count).'&count='.$count;

			if (trim($temp = $this->dsParamFILTERS['ysboss-s'])) $p .= '&sites='.urlencode($temp);

			if ($settings['ysboss']['lang'] && $settings['ysboss']['lang'] != 'Disabled') {
				$p .= '&region='.$this->languages[$settings['ysboss']['lang']][0];
				$p .= '&lang='.$this->languages[$settings['ysboss']['lang']][1];
			}

			if (trim($settings['ysboss']['filters'])) $p .= '&filter='.$settings['ysboss']['filters'];

			$q = preg_replace('/\\\\(["\'])/', '$1', urldecode($q)); // TODO: Symphony tries to do some magic behind the scenes so we have to change it back :( Find out cleaner solution.
			$yahooURL = 'http://boss.yahooapis.com/ysearch/web/v1/'.urlencode($q).$p;

			$xml = new XMLElement('ysboss');
			$xml->appendChild(new XMLElement('query', $q));

			if (!function_exists('curl_init')) {
				$error = new XMLElement('error', 'cURL not installed.');
				$xml->appendChild($error);
				return $xml;
			}

			$ch = curl_init();
			if (!$ch) {
				$error = new XMLElement('error', 'Cannot initialize cURL object.');
				$xml->appendChild($error);
				return $xml;
			}

			curl_setopt($ch, CURLOPT_URL, $yahooURL);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_REFERER, URL);
			$body = trim(curl_exec($ch));
			curl_close($ch);

			if (!$body) {
				$error = new XMLElement('error', 'No data received.');
				$xml->appendChild($error);
				return $xml;
			}

			$body = preg_replace('/<'.'\?xml[^>]*>/', '', $body);
			if (preg_match('/\<ysearchresponse[^>]*\>/i', $body, $response)) {
				$body = preg_replace('/\<\/?ysearchresponse[^>]*\>/', '', $body);

				$data = new XMLElement('ysearchresponse', $body);
				if (preg_match('/responsecode="([^"]+)"/i', $response[0], $code)) {
					$data->setAttribute('responsecode', $code[1]);

					if ($code[1] == '200' && preg_match('/\<resultset_web count="(\d+)" start="(\d+)" totalhits="(\d+)" deephits="(\d+)"/i', $body, $numbers)) {
						$pagination = new XMLElement('pagination-info');
						$pagination->setAttribute('total-entries', $numbers[3]);
						$pagination->setAttribute('total-pages', ceil($numbers[3] / $count));
						$pagination->setAttribute('entries-per-page', $count);
						$pagination->setAttribute('current-page', ceil($numbers[2] / $count) + 1);
						$xml->appendChild($pagination);
					}
				}

				$xml->appendChild($data);
			}

/*
			if($param['caching'] && $cache = $this->check_cache($hash_id, time() + (CACHE_LIFETIME - 3600))){ // keep cache of results for 1 hour
				return $cache;
				exit();
			}


			##Write To Cache
			if($param['caching']){
				$result = $xml->generate($param['indent'], $param['indent-depth']);
				$this->write_to_cache($hash_id, $result, $this->_cache_sections);
				return $result;
			}
*/
			return $xml;
		}
	}

?>