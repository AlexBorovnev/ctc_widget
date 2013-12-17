<?php
class initBase
{
    const BASE_TMP_NAME = 'base_tmp.xml';
    const BASE_NAME = 'base_db.xml';
    const BACKUP_FOLDER = 'backup/';
    const PROJECT_DIR = '/home/developer/dev/projects/test.loc';
    const MAX_BACKUP_FILE = '20';
    const CONFIG_PATH = 'base_url.ini';

    private $backupCreate = false;
    private $backupName = '';
    private $dbh = null;
    private $config = array();

    public function __construct()
    {
        chdir(self::PROJECT_DIR);
        $this->config = parse_ini_file(self::CONFIG_PATH, true);
    }

    public function updateBase()
    {
        foreach ($this->getBaseUrl() as $shopName => $baseUrl) {
            $this->downloadBase($baseUrl, $shopName);
        }
    }

    private function getBaseUrl()
    {
        return $this->config['base_url'];
    }

    private function downloadBase($baseUrl, $shopName)
    {
        if ($this->fileExists($baseUrl) && @copy($baseUrl, self::BASE_TMP_NAME)) {
            rename(self::BASE_TMP_NAME, self::BASE_NAME);
        } else {
            return false;
        }
        $this->makeBackup($shopName);
        return true;
    }

    private function fileExists($baseUrl){
        $file_headers = @get_headers($baseUrl);
        if(strpos($file_headers[0], 'HTTP/1.1 200 OK') === false) {
            return false;
        }
        else {
            return true;
        }
    }
    private function setupBackup($shopName)
    {
        if ($backupName = $this->getLastBackup($this->prependBackupFolder($shopName))) {
            copy(self::PROJECT_DIR . '/' . $this->config['backup']['folder'] . $shopName . '/' . $backupName, self::BASE_NAME);
        }
    }

    private function getLastBackup($filesList)
    {
        if ($filesList) {
            return array_pop($filesList);
        }
        return false;
    }

    private function prependBackupFolder($shopName)
    {
        chdir(self::PROJECT_DIR . '/' . $this->config['backup']['folder'] . $shopName);
        $filesList = glob('*.xml');
        if (count($filesList) > $this->config['backup']['max_backup_file']) {
            foreach (array_slice($filesList, $this->config['backup']['max_backup_file']) as $fileName) {
                unlink($fileName);
            }
        }
        chdir(self::PROJECT_DIR);
        return $filesList;
    }

    private function makeBackup($shopName)
    {
        $this->backupName = $this->config['backup']['folder'] . $shopName . '/' . date('YmdHi') . '.xml';
        if (!file_exists(self::PROJECT_DIR . '/' . $this->config['backup']['folder'] . $shopName)) {
            mkdir(self::PROJECT_DIR . '/' . $this->config['backup']['folder'] . $shopName, 0777);
        }
        $this->prependBackupFolder($shopName);
        if (@copy(self::BASE_NAME, $this->backupName)) {
            chmod($this->backupName, 0777);
        }
    }

    private function removeTmp()
    {
        unlink(self::BASE_NAME);
    }
}

$db = new initBase();
$db->updateBase();