<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

if (!defined('IN_DZZ')) {
    exit('Access Denied');
}

class table_process extends dzz_table {
    public function __construct() {

        $this->_table = 'process';
        $this->_pk = 'processid';

        parent::__construct();
    }

    public function delete_process($name, $time) {
        return DB::delete('process', [
            'where' => 'processid=%s OR expiry<%d',
            'arg' => [$name, $time],
        ]);
    }
}

