<?php

namespace AzozzALFiras\AAPanelAPI;

use CURLFile;

/**
 * Class aaPanelApiClient
 *
 * A PHP client for interacting with aapanel API.
 * documentation: https://github.com/AzozzALFiras/aapanel-api
 */
class aaPanelApiClient
{
    private $apiKey;
    private $baseUrl;

    /**
     * aaPanelApiClient constructor.
     *
     * @param string $apiKey API Key for authentication
     * @param string $baseUrl Base URL of the API With Port 
     */
    public function __construct($apiKey, $baseUrl)
    {
        $this->apiKey  = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Generate request token and time.
     *
     * @return array Request token and time
     */
    private function generateRequestData()
    {
        return [
            'request_token' => md5(time() . md5($this->apiKey)),
            'request_time' => time(),
        ];
    }

    /**
     * Perform HTTP POST request with cookie handling.
     *
     * @param string $url URL of the API endpoint
     * @param array $data Data to send with the request
     * @param int $timeout Timeout for the request
     * @return mixed Response from the API
     */
    private function httpPostWithCookie($url, $data, $timeout = 60)
    {
        $cookieFile = './' . md5($this->baseUrl) . '.cookie';
        if (!file_exists($cookieFile)) {
            $fp = fopen($cookieFile, 'w+');
            fclose($fp);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }

    /**
     * Fetch logs from the API.
     *
     * @return array Log data
     */
    public function fetchLogs()
    {
        $url = $this->baseUrl . '/data?action=getData';

        $requestData = $this->generateRequestData();
        $requestData['table'] = 'logs';
        $requestData['limit'] = 10;
        $requestData['tojs'] = 'test';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Add a new site.
     *
     * @param string $domain Domain name
     * @param string $path Path to site
     * @param string $description Description of the site
     * @param int $typeId Type ID
     * @param string $type Type (e.g., php)
     * @param string $phpVersion PHP version
     * @param string $port Port number
     * @param bool|null $ftp FTP required or not
     * @param string|null $ftpUsername FTP username
     * @param string|null $ftpPassword FTP password
     * @param bool|null $sql SQL database required or not
     * @param string|null $databaseUsername Database username
     * @param string|null $databasePassword Database password
     * @param int $setSsl Set SSL or not
     * @param int $forceSsl Force SSL or not
     * @return array Response from the API
     */
    public function addSite($domain, $path, $description, $typeId = 0, $type = 'php', $phpVersion = '73', $port = '80', $ftp = null, $ftpUsername = null, $ftpPassword = null, $sql = null, $databaseUsername = null, $databasePassword = null, $setSsl = 1, $forceSsl = 1)
    {
        $url = $this->baseUrl . '/site?action=AddSite';

        $jsonData = [
            'domain' => $domain,
            'domainlist' => [],
            'count' => 0,
        ];

        $requestData = $this->generateRequestData();
        $requestData['webname'] = json_encode($jsonData);
        $requestData['path'] = "/www/wwwroot/" . $path;
        $requestData['ps'] = $description;
        $requestData['type_id'] = $typeId;
        $requestData['type'] = $type;
        $requestData['version'] = $phpVersion;
        $requestData['port'] = $port;

        if ($ftp !== null) {
            $requestData['ftp'] = $ftp;
            $requestData['ftp_username'] = $ftpUsername;
            $requestData['ftp_password'] = $ftpPassword;
        }

        if ($sql !== null) {
            $requestData['sql'] = $sql;
            $requestData['datauser'] = $databaseUsername;
            $requestData['datapassword'] = $databasePassword;
        }

        $requestData['codeing'] = 'utf8';
        $requestData['set_ssl'] = $setSsl;
        $requestData['force_ssl'] = $forceSsl;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Add a subdomain.
     *
     * @param string $subdomain Subdomain name
     * @param string $mainDomain Main domain
     * @param string $ipTarget IP address or target for the subdomain
     * @return array Response from the API
     */
    public function addSubdomain($subdomain, $mainDomain, $ipTarget)
    {
        $url = $this->baseUrl . '/plugin?action=a&name=dns_manager&s=act_resolve';

        $requestData = $this->generateRequestData();
        $requestData['host'] = $subdomain;
        $requestData['value'] = $ipTarget;
        $requestData['domain'] = $mainDomain;
        $requestData['ttl'] = '600';
        $requestData['type'] = 'A';
        $requestData['act'] = 'add';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Delete a subdomain.
     *
     * @param string $subdomain Subdomain name
     * @param string $mainDomain Main domain
     * @param string $ipTarget IP address or target for the subdomain
     * @return array Response from the API
     */
    public function deleteSubdomain($subdomain, $mainDomain, $ipTarget)
    {
        $url = $this->baseUrl . '/plugin?action=a&name=dns_manager&s=act_resolve';

        $requestData = $this->generateRequestData();
        $requestData['host'] = $subdomain;
        $requestData['value'] = $ipTarget;
        $requestData['domain'] = $mainDomain;
        $requestData['ttl'] = '600';
        $requestData['type'] = 'A';
        $requestData['act'] = 'delete';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Fetch list of FTP accounts.
     *
     * @return array List of FTP accounts
     */
    public function fetchFtpAccounts($limit, $page, $search = null)
    {
        $url = $this->baseUrl . '/data?action=getData';

        $requestData = $this->generateRequestData();
        $requestData['table'] = 'ftps';
        $requestData['limit'] = $limit;
        $requestData['p'] = $page;
        $requestData['search'] = $search;
        $requestData['type'] = '-1';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Add a new FTP account.
     *
     * @param string $username FTP username
     * @param string $password FTP password
     * @return array Response from the API
     */
    public function addFtpAccount($username, $password,$path,$ps)
    {
        $url = $this->baseUrl . '/ftp?action=AddUser';

        $requestData = $this->generateRequestData();
        $requestData['ftp_username'] = $username;
        $requestData['ftp_password'] = $password;
        $requestData['path'] = $path;
        $requestData['ps'] = $ps;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Delete an FTP account.
     *
     * @param string $username FTP username
     * @return array Response from the API
     */
    public function deleteFtpAccount($username,$id)
    {
        $url = $this->baseUrl . '/ftp?action=DeleteUser';

        $requestData = $this->generateRequestData();
        $requestData['username'] = $username;
        $requestData['id'] = $id;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

 

  /**
     * Import SQL file into a database.
     *
     * @param string $file Path to the SQL file
     * @param string $databaseName Name of the database
     * @return array Response from the API
     */
    public function importSqlFile($file, $databaseName)
    {
        $url = $this->baseUrl . '/database?action=InputSql';

        $requestData = $this->generateRequestData();
        $requestData['file'] = $file;
        $requestData['name'] = $databaseName;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Save file content to a specified path.
     *
     * @param string $fileContent Content of the file
     * @param string $path Path where the file will be saved
     * @return array Response from the API
     */
    public function saveFile($fileContent, $path)
    {
        $url = $this->baseUrl . '/files?action=SaveFileBody';

        $requestData = $this->generateRequestData();
        $requestData['data'] = $fileContent;
        $requestData['path'] = $path;
        $requestData['encoding'] = 'utf-8';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Unzip a ZIP archive to a specified destination.
     *
     * @param string $sourceFile Path to the ZIP file
     * @param string $destination Path where the contents will be extracted
     * @param string|null $password Password for the ZIP file (optional)
     * @return array Response from the API
     */
    public function unzipFile($sourceFile, $destination, $password = null)
    {
        $url = $this->baseUrl . '/files?action=UnZip';

        $requestData = $this->generateRequestData();
        $requestData['sfile'] = $sourceFile;
        $requestData['dfile'] = $destination;
        $requestData['type'] = 'zip';
        $requestData['coding'] = 'UTF-8';
        $requestData['password'] = $password;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * ZIP archive 
     *
     * @param string $sourceFile Path to the ZIP file
     * @param string $destination Path where the contents will be extracted
     * @param string|null $password Password for the ZIP file (optional)
     * @return array Response from the API
     */
    public function zipFile($sourceFile, $destination, $z_type = 'zip')
    {
        $url = $this->baseUrl . '/files?action=Zip';

        $requestData = $this->generateRequestData();
        $requestData['sfile'] = basename($sourceFile);
        $requestData['dfile'] = $destination;
        $requestData['type'] = $z_type;
        $requestData['path'] = dirname($sourceFile);
        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Apply SSL certificate to a domain.
     *
     * @param string $domain Domain name
     * @param int $domainId Domain ID
     * @param int $autoWildcard Automatically apply wildcard SSL (0 or 1)
     * @return array Response from the API
     */
    public function applySslCertificate($domain, $domainId, $autoWildcard = 0)
    {
        $applyCertUrl = $this->baseUrl . '/acme?action=apply_cert_api';
        $setSslUrl = $this->baseUrl . '/site?action=SetSSL';

        // Apply certificate
        $applyCertData = $this->generateRequestData();
        $applyCertData['domains'] = '["' . $domain . '"]';
        $applyCertData['id'] = $domainId;
        $applyCertData['auth_to'] = $domainId;
        $applyCertData['auth_type'] = 'http';
        $applyCertData['auto_wildcard'] = $autoWildcard;

        $applyCertResult = $this->httpPostWithCookie($applyCertUrl, $applyCertData);
        $result = json_decode($applyCertResult, true);

        // Set SSL
        $setSslData = $this->generateRequestData();
        $setSslData['type'] = '1';
        $setSslData['siteName'] = $domain;
        $setSslData['key'] = $result['private_key'];
        $setSslData['csr'] = $result['cert'] . ' ' . $result['root'];

        $setSslResult = $this->httpPostWithCookie($setSslUrl, $setSslData);

        return json_decode($setSslResult, true);
    }

     /**
     * Renew SSL certificate for a domain.
     *
     * @param string $domain Domain name
     * @return array|null Renewal response as associative array, null on failure
     */
    public function renewCert($domain) {
        // Get index value for the domain
        $index = $this->getIndexValue($domain);

        // Check if index was retrieved successfully
        if (!$index) {
            return null; // Return null if index is not found
        }

        // API endpoint URL for certificate renewal
        $url = $this->baseUrl . '/acme?action=renew_cert';

        // Prepare request data
        $requestData = $this->generateRequestData();
        $requestData['index'] = $index;

        // Make POST request with cookie authentication
        $result = $this->httpPostWithCookie($url, $requestData);

        // Decode JSON response
        return json_decode($result, true);
    }

    /**
     * Get SSL details for a domain and return the 'index' value.
     *
     * @param string $domain Domain name
     * @return string|null 'index' value if found, null if not found or on error
     */
    public function getIndexValue($domain) {
        // API endpoint URL to fetch SSL details
        $url = $this->baseUrl . '/site?action=GetSSL';

        // Prepare request data
        $requestData = $this->generateRequestData();
        $requestData['siteName'] = $domain;

        // Make POST request with cookie authentication
        $result = $this->httpPostWithCookie($url, $requestData);

        // Decode JSON response
        $response = json_decode($result, true);

        // Check if response is valid and contains 'index' key
        if ($response && isset($response['index'])) {
            return $response['index']; // Return 'index' value
        }

        return null; // Return null if 'index' key is not found or on error
    }

    /**
     * Enable HTTPS redirection for a site.
     *
     * @param string $siteName Name of the site
     * @return array Response from the API
     */
    public function enableHttpsRedirection($siteName)
    {
        $url = $this->baseUrl . '/site?action=HttpToHttps';

        $requestData = $this->generateRequestData();
        $requestData['siteName'] = $siteName;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Disable a site.
     *
     * @param int $siteId ID of the site
     * @param string $siteName Name of the site
     * @return array Response from the API
     */
    public function disableSite($siteId, $siteName)
    {
        $url = $this->baseUrl . '/site?action=SiteStop';

        $requestData = $this->generateRequestData();
        $requestData['id'] = $siteId;
        $requestData['name'] = $siteName;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Enable a site.
     *
     * @param int $siteId ID of the site
     * @param string $siteName Name of the site
     * @return array Response from the API
     */
    public function enableSite($siteId, $siteName)
    {
        $url = $this->baseUrl . '/site?action=SiteStart';

        $requestData = $this->generateRequestData();
        $requestData['id'] = $siteId;
        $requestData['name'] = $siteName;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Retrieve details of a specific FTP account.
     *
     * @param string $username FTP username
     * @return array Response from the API
     */
    public function getFtpAccountDetails($username)
    {
        $url = $this->baseUrl . '/ftp?action=GetUser';

        $requestData = $this->generateRequestData();
        $requestData['user'] = $username;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Set server configuration parameters.
     *
     * @param array $configData Configuration data
     * @return array Response from the API
     */
    public function setServerConfig($configData)
    {
        $url = $this->baseUrl . '/server?action=setConfig';

        $requestData = $this->generateRequestData();
        $requestData['config'] = json_encode($configData);

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Get server configuration parameters.
     *
     * @return array Response from the API
     */
    public function getServerConfig()
    {
        $url = $this->baseUrl . '/server?action=getConfig';

        $requestData = $this->generateRequestData();

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }



    /**
     * Delete Site
     *
     * @return array Response from the API
     */
    public function deleteSite($site_id,$domain)
    {
        $url = $this->baseUrl . '/site?action=DeleteSite';

        $requestData = $this->generateRequestData();

        $requestData['webname'] = $domain;
        $requestData['ftp'] = "1";
        $requestData['database'] = "1";
        $requestData['path'] = "1";
        $requestData['id'] = $site_id;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Site List
     *
     * @return array Response from the API
     */
    public function fetchSites($limit, $page, $search = null)
    {
        $url = $this->baseUrl . '/data?action=getData';

        $requestData = $this->generateRequestData();
        $requestData['table'] = 'sites';
        $requestData['limit'] = $limit;
        $requestData['p'] = $page;
        $requestData['search'] = $search;
        $requestData['type'] = '-1';

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }

    /**
     * Fetch Dir
     *
     * @return array Response from the API
     */
    public function fetchDirectory($path, $page, $showRow = 100)
    {
        $url = $this->baseUrl . '/files?action=GetDir';

        $requestData = $this->generateRequestData();
        $requestData['path'] = $path;
        $requestData['p'] = $page;
        $requestData['showRow'] =  $showRow ;
        $requestData['is_operating'] =  true;

        $result = $this->httpPostWithCookie($url, $requestData);

        return json_decode($result, true);
    }


    /**
     * Download Remote File
     *
     * @return array Response from the API
     */
    public function downloadFile($remoteUrl,$path, $filename)
    {
        $url = $this->baseUrl . '/files?action=DownloadFile';

        $requestData = $this->generateRequestData();
        $requestData['url'] = $remoteUrl;
        $requestData['path'] = $path;
        $requestData['filename'] = $filename;
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }


    /**
     * Retrive File Content
     *
     * @return array Response from the API
     */
    public function getFileBody($path)
    {
        $url = $this->baseUrl . '/files?action=GetFileBody';

        $requestData = $this->generateRequestData();
        $requestData['path'] = $path;
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }

    /**
     * Upload File
     *
     * @return array Response from the API
     */
    public function uploadFile($localPath,$path, $filename)
    {
        $url = $this->baseUrl . '/files?action=upload';

        $filesize = filesize($localPath);

        $requestData = $this->generateRequestData();
        $requestData['f_path'] = $path;
        $requestData['f_name'] = $filename;
        $requestData['f_size'] = $filesize;
        $requestData['f_start'] = 0;
        $requestData['blob'] = new CURLFile($localPath, mime_content_type($localPath), $filename);

        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }


    /**
     * Create File
     *
     * @return array Response from the API
     */
    public function createFile($path)
    {
        $url = $this->baseUrl . '/files?action=CreateFile';
        $requestData = $this->generateRequestData();
        $requestData['path'] = $path;
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }

    /**
     * Delete File
     *
     * @return array Response from the API
     */
    public function deleteFile($path)
    {
        $url = $this->baseUrl . '/files?action=DeleteFile';
        $requestData = $this->generateRequestData();
        $requestData['path'] = $path;
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }


    /**
     * Get Site Run Path
     *
     * @return array Response from the API
     */
    public function getSiteRunPath($site_id)
    {
        $url = $this->baseUrl . '/data?action=getKey';
        $requestData = $this->generateRequestData();
        $requestData['id'] = $site_id;
        $requestData['key'] = 'path';
        $requestData['table'] = 'sites';
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }

    /**
     * Set Site Run Path
     *
     * @return array Response from the API
     */
    public function setSiteRunPath($site_id,$run_path)
    {
        $url = $this->baseUrl . '/site?action=SetSiteRunPath';
        $requestData = $this->generateRequestData();
        $requestData['id'] = $site_id;
        $requestData['runPath'] = $run_path;
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }



    /**
     * Add Database
     *
     * @return array Response from the API
     */
    public function addDatabase($db_name,$db_user,$db_password,$ps)
    {

        $url = $this->baseUrl . '/database?action=AddDatabase';
        $requestData = $this->generateRequestData();
        $requestData['sid'] = 0;
        $requestData['name'] = $db_name;
        $requestData['codeing'] = 'utf8';
        $requestData['db_user'] = $db_user;
        $requestData['password'] = $db_password;
        $requestData['dataAccess'] = '127.0.0.1';
        $requestData['address'] = '127.0.0.1';
        $requestData['active'] = 'false';
        $requestData['ssl'] = null;
        $requestData['ps'] = $ps;
        $requestData['dtype'] = 'MySQL';
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }


    /**
     * Delete Database
     *
     * @return array Response from the API
     */
    public function deleteDatabase($db_id,$db_name)
    {
        $url = $this->baseUrl . '/database?action=DeleteDatabase';
        $requestData = $this->generateRequestData();
        $requestData['id'] = $db_id;
        $requestData['name'] = $db_name;
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }




    /**
     * Create Custom Script
     *
     * @return array Response from the API
     */
    public function createCustomScript($name,$script,$return_type = "string",$is_args = "0",$args_title ="",$args_ps ="")
    {
        $url = $this->baseUrl . '/v2/crontab/script/create_script';
        $requestData = $this->generateRequestData();

        $requestData['name'] = $name;
        $requestData['return_type'] = $return_type;
        $requestData['is_args'] = $is_args;
        $requestData['ps'] = "";
        $requestData['script'] = $script;
        $requestData['type_id'] = "7";
        $requestData['args_title'] = $args_title;
        $requestData['args_ps'] = $args_ps;

        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }


    /**
     * Get Custom Script List
     *
     * @return array Response from the API
     */
    public function fetchCustomScripts($p = 1)
    {
        $url = $this->baseUrl . '/v2/crontab/script/get_script_list';
        $requestData = $this->generateRequestData();
        $requestData['p'] = $p;
        $requestData['rows'] = 50;
        $requestData['type_id'] = "7";
        $requestData['search'] = "";
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }

    /**
     * Execute script
     *
     * @return array Response from the API
     */
    public function runScript($script_id,$args = "")
    {
        $url = $this->baseUrl . '/v2/crontab/script/test_script';
        $requestData = $this->generateRequestData();
        $requestData['script_id'] = $script_id;
        $requestData['args'] = $args;
        $result = $this->httpPostWithCookie($url, $requestData);
        return json_decode($result, true);
    }



}
