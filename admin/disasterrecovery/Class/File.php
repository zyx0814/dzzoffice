<?php
/**
 * Filename: File.php
 * User: darkwind
 * Date: 18-8-4
 * Time: 下午2:36
 */

namespace DisasterRecovery;

/**
 * Class File
 * @package DisasterRecovery
 */
class File
{
    /**
     * @var $basicPath string 备份文件根目录
     */
    protected $basicPath;
    /**
     * @var $is_open string 是否开启容灾备份
     */
    protected $is_open;
    /**
     * @var $method string 存储方式
     */
    protected $method;

    /**
     * File constructor.
     */
    public function __construct()
    {
        global $_G;
        $this->basicPath = $_G['config']['DR']['basicPath'];
        $this->is_open = $_G['config']['DR']['is_open'];
        $this->method = $_G['config']['DR']['method'];
    }

    /**
     * 是否开启容灾备份
     * @return bool
     */
    public function needCopy()
    {
        return $this->is_open === 'yes' ? true : false;
    }

    /**
     * 备份文件内容
     * @param $filePath
     * @param $nfilename
     * @param $fileContent
     */
    public function storage($filePath, $nfilename, $fileContent)
    {
        switch ($this->method){
            case 'local':
                $this->storageInLocal($filePath, $nfilename, $fileContent);
                break;
            case 'ftp':
                $this->storageInLocal($filePath, $nfilename, $fileContent);
                break;
            default:
                $this->storageInLocal($filePath, $nfilename, $fileContent);
                break;
        }
    }

    private function storageInLocal($filePath, $nfilename, $fileContent)
    {
        global $_G;
//        获取文件夹信息
        $path = preg_replace('/dzz:(.+?):/', '', $filePath) ? preg_replace('/dzz:(.+?):/', '', $filePath) : '';
        $pathArray = array_filter(explode('/', $path));
        if(isset($pathArray[0]) and $pathArray[0] === '我的网盘'){
            $pathArray[0] = $_G['username'];
        }
//        创建文件目录
        $folderPath = $_G['config']['namespacelist']['root'].$this->basicPath.'/'.implode('/', $pathArray);
        $this->createDir($folderPath);

        $path= $folderPath . '/' . $nfilename;

//        win服务器兼容
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $path = iconv('UTF-8', 'GB2312//IGNORE', $path);
        }


        if (is_writable($folderPath) === false) {
//          todo error log
        }else{
//        验重 upload方法已经做了，为免重复，这里不再做验证
            file_put_contents($path, $fileContent);
        }
    }

    private function storageInFtp($filePath, $nfilename, $fileContent)
    {
        // todo
    }

    private function createDir($path){
        if(!file_exists($path)){
            $this->createDir(dirname($path));
            mkdir($path, 0755, true);
        }
    }
}