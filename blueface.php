<?php
class PoxnoraAPI {
//    const URL = 'https://portal.blueface.com/ie/login.aspx';
      const URL = 'https://portal.blueface.com/ie/callmanagement.aspx';  
  protected $cookieFile;

    public function __construct() {
        // create a new cookie file
        $this->getCookieFile();
    }

    // Creates new cookie file in system temp dir
    protected function getCookieFile() {
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'CDL');
    }

    // Gets form key from login page
    protected function getFormKey() {
        $ch = curl_init();
        $this->setCurlOpts($ch);
        $result = curl_exec($ch);
        curl_close($ch);

        $key = $this->matchFormKey($result);
	
	try {
 		 if(!$key){
  		//If the exception is thrown, this text will not be shown
  		return $key;
		}
	}
	//catch exception
	catch(Exception $e) {
  		echo 'Message: ' .$e->getMessage();
	}

        /*if (!$key) {
            throw new Exception("Unable to get key from form");
        }*/

        return $key;
    }

    protected function matchFormKey($result) {
        preg_match_all('<input type="hidden" name="lt" value="(.*)">', $result, $matches);

        return isset($matches[1][0]) ? $matches[1][0] : false;
    }

    // Uses username, password, and form key to login
    public function login($username, $password) {
        $key = $this->getFormKey();

        $ch = curl_init();
        $this->setCurlOpts($ch);

        $data = "lt=$key&_eventId=submit&username=$username&password=$password";
        $this->setCurlPost($ch, $data);
        $result = curl_exec($ch);
	curl_close($ch);
	// https://portal.blueface.com/ie/callmanagement.aspx
	//$this->download_page($result);
	// check if there's a form key. If there's a form key then we're on
        // the login page again
        //$key = $this->matchFormKey($result);

        //return !$key;
	return $this->cookieFile;
    }

    // Add post data to curl
    protected function setCurlPost($ch, $postData) {
        curl_setopt($ch, CURLOPT_POST,       true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }

    // Add default curl params
    protected function setCurlOpts($ch) {
        curl_setopt($ch, CURLOPT_COOKIEJAR,      $this->cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE,     $this->cookieFile);
        curl_setopt($ch, CURLOPT_TIMEOUT,        40000);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,	 30000);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,            self::URL);
        curl_setopt($ch, CURLOPT_USERAGENT,      $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_REFERER,        self::URL);
    }

}

$api = new PoxnoraAPI();
$cookie=$api->login('journal', '8DFG10AGnr4L');
//$ch = curl_init("https://portal.blueface.com/ie/pbxgui/provisioning.aspx?method=getsip&customerid=28beb3b7-cee0-4f5e-b88f-17950d9b6fd9");
//$ch = curl_init("https://portal.blueface.com/ie/pbxgui/pbxguixml.aspx?method=getvoicemail&simulate=false");
$ch = curl_init("https://portal.blueface.com/ie/pbxgui/pbxguixml.aspx?method=getnumbers&simulate=false");
curl_setopt($ch, CURLOPT_COOKIEJAR,      $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE,     $cookie);
$voicemail = curl_exec($ch);
$xml=simplexml_load_file($voicemail);
echo $xml->sipaccount;
?>
