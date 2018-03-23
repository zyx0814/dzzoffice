<?php
/*
 * jQuery File Upload Plugin PHP Class 6.4.2
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
error_reporting(E_ERROR);
class UploadHandler
{
    protected $options;
    // PHP File Upload error message codes:
    // http://php.net/manual/en/features.file-upload.errors.php
    protected $error_messages = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload',
		9 =>'no_privilege',
		10=>'inadequate_capacity_space',
        'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
        'max_file_size' => 'File is too big',
        'min_file_size' => 'File is too small',
        'accept_file_types' => 'Filetype not allowed',
    );

    function __construct($options = null, $initialize = true, $error_messages = null) {
        $this->options = array(
            'script_url' => $this->get_full_url().'/',

            'mkdir_mode' => 0755,
            'param_name' => 'files',
            // Set the following option to 'POST', if your server does not support
            // DELETE requests. This is a parameter sent to the client:
            'access_control_allow_origin' => '*',
            'access_control_allow_credentials' => false,
            'access_control_allow_methods' => array(
                'OPTIONS',
                'HEAD',
                'GET',
                'POST',
                'PUT',
                'PATCH',
                'DELETE'
            ),
            'access_control_allow_headers' => array(
                'Content-Type',
                'Content-Range',
                'Content-Disposition'
            ),
            // Enable to provide file downloads via GET requests to the PHP script:
            // Defines which files (based on their names) are accepted for upload:
            'accept_file_types' => '/.+$/i',
            // The php.ini settings upload_max_filesize and post_max_size
            // take precedence over the following max_file_size setting:
            'max_file_size' => null,
            'min_file_size' => 0,
            // Set the following option to false to enable resumable uploads:
            'discard_aborted_uploads' => true,
           
        );
        if ($options) {
            $this->options = array_merge($this->options, $options);
        }
		
        if ($error_messages) {
            $this->error_messages = array_merge($this->error_messages, $error_messages);
        }
        if ($initialize) {
            $this->initialize();
        }
    }

    protected function initialize() {
        switch ($this->get_server_var('REQUEST_METHOD')) {
            case 'OPTIONS':
            case 'HEAD':
                $this->head();
                break;
            case 'GET':
                $this->post();
                break;
            case 'PATCH':
            case 'PUT':
            case 'POST':
                $this->post();
                break;
            case 'DELETE':
                $this->delete();
                break;
            default:
                $this->header('HTTP/1.1 405 Method Not Allowed');
        }
    }

    protected function get_full_url() {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        return
            ($https ? 'https://' : 'http://').
            (!empty($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
            ($https && $_SERVER['SERVER_PORT'] === 443 ||
            $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }

    protected function get_user_id() {
		global $_G;
     	 return $_G['uid'];
    }

  
    protected function get_upload_path($file_name = null, $version = null) {
        $file_name = $file_name ? $file_name : '';
        $version_path = empty($version) ? '' : $version.'/';
        return $this->options['upload_dir']
            .$version_path.$file_name;
    }
	

    protected function get_query_separator($url) {
        return strpos($url, '?') === false ? '?' : '&';
    }



    // Fix for overflowing signed 32 bit integers,
    // works for sizes up to 2^32-1 bytes (4 GiB - 1):
    protected function fix_integer_overflow($size) {
        if ($size < 0) {
            $size += 2.0 * (PHP_INT_MAX + 1);
        }
		
        return $size;
    }

    protected function get_file_size($file_path, $clear_stat_cache = false) {
        if ($clear_stat_cache) {
            clearstatcache(true, $file_path);
        }
		//exit($file_path.'_dddddd='.filesize($file_path));
        return $this->fix_integer_overflow(filesize($file_path));

    }

    protected function get_error_message($error) {
        return array_key_exists($error, $this->error_messages) ?
            $this->error_messages[$error] : $error;
    }

    function get_config_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $this->fix_integer_overflow($val);
    }

    protected function validate($uploaded_file,$file, $error,$content_range) {
		
		if(strpos($error,'multipart')!==false){
			$file->name=lang('loadError');
			$file->error = $this->get_error_message('post_max_size');
            return false;
		}
        if ($error) {
            $file->error = $this->get_error_message($error);
            return false;
        }
		
		/*$pfid=getFidByContainer($this->get_container());
		if(!$content_range && !perm_check::checkperm_Container($pfid,'upload')){
			$file->error = $this->get_error_message(9);
			return false;
		}*/
		/*$gid=getGidByContainer($this->get_container());
		if(!$content_range && !SpaceSize($size,$gid)){
			$file->error = $this->get_error_message(10);
			return false;
		}*/
		
        $content_length = $this->fix_integer_overflow(intval(
            $this->get_server_var('CONTENT_LENGTH')
        ));
        $post_max_size = $this->get_config_bytes(ini_get('post_max_size'));
        if ($post_max_size && ($content_length > $post_max_size)) {
            $file->error = $this->get_error_message('post_max_size');
            return false;
        }
		
        if (!$this->isToCloud() && !preg_match($this->options['accept_file_types'], $file->name)) {
            $file->error = $this->get_error_message('accept_file_types');
            return false;
        }
		if(is_array($content_range)){
			$file_size=$this->fix_integer_overflow(intval($content_range[3]));
		}else{
			if ($uploaded_file && is_uploaded_file($uploaded_file)) {
				$file_size = $this->get_file_size($uploaded_file);
			} else {
				$file_size = $content_length;
			}
		}
        
        if (!$this->isToCloud() && $this->options['max_file_size'] && (
                $file_size > $this->options['max_file_size'] ||
                $file->size > $this->options['max_file_size'])
            ) {
            $file->error = $this->get_error_message('max_file_size');
            return false;
        }
        if ($this->options['min_file_size'] &&
            $file_size < $this->options['min_file_size']) {
            $file->error = $this->get_error_message('min_file_size');
            return false;
        }
        
        return true;
    }

    protected function upcount_name_callback($matches) {
        $index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
        $ext = isset($matches[2]) ? $matches[2] : '';
        return ' ('.$index.')'.$ext;
    }

    protected function upcount_name($name) {
        return preg_replace_callback(
            '/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
            array($this, 'upcount_name_callback'),
            $name,
            1
        );
    }

    protected function get_unique_filename($name, $type, $index, $content_range) {
        while(is_dir($this->get_upload_path($name))) {
            $name = $this->upcount_name($name);
        }
        // Keep an existing filename if this is part of a chunked upload:
        $uploaded_bytes = $this->fix_integer_overflow(intval($content_range[1]));
        while(is_file($this->get_upload_path($name))) {
            if ($uploaded_bytes === $this->get_file_size(
                    $this->get_upload_path($name))) {
                break;
            }
            $name = $this->upcount_name($name);
        }
        return $name;
    }

    protected function trim_file_name($name, $type, $index, $content_range) {
        // Remove path information and dots around the filename, to prevent uploading
        // into different directories or replacing hidden system files.
        // Also remove control characters and spaces (\x00..\x20) around the filename:
        $name = trim(basename(stripslashes($name)), ".\x00..\x20");
        // Use a timestamp for empty filenames:
        if (!$name) {
            $name = str_replace('.', '-', microtime(true));
        }
        // Add missing file extension for known image types:
        if (strpos($name, '.') === false &&
            preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
            $name .= '.'.$matches[1];
        }
        return $name;
    }

    protected function get_file_name($name, $type, $index, $content_range) {
        return $this->get_unique_filename(
            $this->trim_file_name($name, $type, $index, $content_range),
            $type,
            $index,
            $content_range
        );
    }

    protected function handle_form_data($file, $index) {
        // Handle form data, e.g. $_REQUEST['description'][$index]
    }


   protected function get_container(){
	   global $_GET;
	   return trim($_GET['container']);
   }
   protected function isToCloud(){
	   $container=self::get_container();
	   $path=str_replace(array('icosContainer_folder_','icosContainer_body_'),'',$container);
	   if(is_numeric($path)){
		   return false;
	   }else{
		   return true;
	   }
   }

    protected function handle_file_upload($uploaded_file, $name, $size, $relativePath, $type, $error,
            $index = null, $content_range = null,$bz='',$container) {
        $file = new stdClass();
		$name=rawurldecode($name);
		$relativePath=rawurldecode($relativePath);
        $file->name = $name;//$this->get_file_name($name, $type, $index, $content_range);
        $file->size = $this->fix_integer_overflow(intval($size));
        $file->type = $type;
		$file->relativePath = $relativePath;

        if ($this->validate($uploaded_file, $file, $error,$content_range)) {
            $this->handle_form_data($file, $index);
        	$upload_dir = $this->get_upload_path();
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, $this->options['mkdir_mode'], true);
            }
            $filepath = $this->get_upload_path(md5($relativePath.$name).random(5));
            //$append_file = $content_range && is_file($file_path) &&
            //    $file->size > $this->get_file_size($file_path);
				
				
            if($uploaded_file && is_uploaded_file($uploaded_file)) {
                // multipart/formdata uploads (POST method uploads)
               /* if ($append_file) {
                    file_put_contents(
                        $file_path,
                        fopen($uploaded_file, 'r'),
                        FILE_APPEND
                    );

                } else {*/
                    if(@move_uploaded_file($uploaded_file, $filepath)){
						clearstatcache();
						$filesize=$this->get_file_size($filepath);
					}else{
						$file->error=lang('move_file_error').$filepath.lang('jurisdiction');
						@unlink($filepath);
						return $file;
					}
                //}
				//$filepath= $uploaded_file;

            } else {
                // Non-multipart uploads (PUT method support)
               /* file_put_contents(
                    $file_path,
                    fopen('php://input', 'r'),
                    $append_file ? FILE_APPEND : 0
                );*/
				$filepath='php://input';
				$filesize=$size;
            }
			if(!$filesize && $filesize!=$size){
				$file->error=lang('access_file_size_error');
				@unlink($filepath);
				return $file;
			}
			$file->size=is_array($content_range)?($content_range[1]+$filesize):$filesize;
			
			$path=str_replace(array('icosContainer_folder_','icosContainer_body_'),'',$container);
			if(is_numeric($path)){ //传到本地时
				//判断权限
				if(!perm_check::checkperm_Container($path,'upload') ) {
					 $file->error =lang('no_upload_permissions');
					 @unlink($filepath);
					  return $file;
				}

				//判断空间大小
				$gid=DB::result_first("select gid from ".DB::table('folder')." where fid='{$path}'");
				if(!SpaceSize($file->size,$gid)){
					 $file->error = lang('inadequate_capacity_space');
					 @unlink($filepath);
					 return $file;
				 }
			}
			try{
				if($return=IO::uploadStream($filepath,$name,$path,$relativePath,$content_range)){

					if(isset($return['error'])){
							$file->error = $return['error'];
					}elseif(is_array($return)){
						$file->data = $return;
					}
				}else{
					$file->error ='upload failure';
				}
			}catch(Exception $e){
				$file->error =$e->getMessage();
			}
        }
		@unlink($filepath);
        return $file;
    }

    protected function readfile($file_path) {
        return readfile($file_path);
    }

    protected function body($str) {
        echo $str;
    }
    
    protected function header($str) {
        header($str);
    }

    protected function get_server_var($id) {
        return isset($_SERVER[$id]) ? $_SERVER[$id] : '';
    }

    protected function generate_response($content, $print_response = true) {
        if ($print_response) {
            $json = json_encode($content);
            $redirect = isset($_REQUEST['redirect']) ?
                stripslashes($_REQUEST['redirect']) : null;
            if ($redirect) {
                $this->header('Location: '.sprintf($redirect, rawurlencode($json)));
                return;
            }
            $this->head();
            if ($this->get_server_var('HTTP_CONTENT_RANGE')) {
                $files = isset($content[$this->options['param_name']]) ?
                    $content[$this->options['param_name']] : null;
                if ($files && is_array($files) && is_object($files[0]) && $files[0]->size) {
                    $this->header('Range: 0-'.(
                        $files[0]->size-1
                    ));
                }
            }
            $this->body($json);
        }
		
        return $content;
    }

    protected function get_version_param() {
        return isset($_GET['version']) ? basename(stripslashes($_GET['version'])) : null;
    }

    protected function get_file_name_param() {
        return isset($_GET['file']) ? basename(stripslashes($_GET['file'])) : null;
    }

    protected function get_file_type($file_path) {
        switch (strtolower(pathinfo($file_path, PATHINFO_EXTENSION))) {
            case 'jpeg':
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            default:
                return '';
        }
    }


    protected function send_content_type_header() {
        $this->header('Vary: Accept');
        if (strpos($this->get_server_var('HTTP_ACCEPT'), 'application/json') !== false) {
            $this->header('Content-type: application/json');
        } else {
            $this->header('Content-type: text/plain');
        }
    }

    protected function send_access_control_headers() {
        $this->header('Access-Control-Allow-Origin: '.$this->options['access_control_allow_origin']);
        $this->header('Access-Control-Allow-Credentials: '
            .($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
        $this->header('Access-Control-Allow-Methods: '
            .implode(', ', $this->options['access_control_allow_methods']));
        $this->header('Access-Control-Allow-Headers: '
            .implode(', ', $this->options['access_control_allow_headers']));
    }

    public function head() {
        $this->header('Pragma: no-cache');
        $this->header('Cache-Control: no-store, no-cache, must-revalidate');
        $this->header('Content-Disposition: inline; filename="files.json"');
        // Prevent Internet Explorer from MIME-sniffing the content-type:
        $this->header('X-Content-Type-Options: nosniff');
        if ($this->options['access_control_allow_origin']) {
            $this->send_access_control_headers();
        }
        $this->send_content_type_header();
    }

    public function get($print_response = true) {
        if ($print_response && isset($_GET['download'])) {
            return $this->download();
        }
        $file_name = $this->get_file_name_param();
        if ($file_name) {
            $response = array(
                substr($this->options['param_name'], 0, -1) => $this->get_file_object($file_name)
            );
        } else {
            $response = array(
                $this->options['param_name'] => $this->get_file_objects()
            );
        }
        return $this->generate_response($response, $print_response);
    }

    public function post($print_response = true) { 
		if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
            return $this->delete($print_response);
        }
        $upload = isset($_FILES[$this->options['param_name']]) ?
            $_FILES[$this->options['param_name']] : null;
			 // Parse the Content-Disposition header, if available:
        $file_name = $this->get_server_var('HTTP_CONTENT_DISPOSITION') ?
            rawurldecode(preg_replace(
                '/(^[^"]+")|("$)/',
                '',
                $this->get_server_var('HTTP_CONTENT_DISPOSITION')
            )) : null;
        // Parse the Content-Range header, which has the following form:
        // Content-Range: bytes 0-524287/2000000
        $content_range = $this->get_server_var('HTTP_CONTENT_RANGE') ?
            preg_split('/[^0-9]+/', $this->get_server_var('HTTP_CONTENT_RANGE')) : null;
        $size =  $content_range ? $content_range[3] : null;
        $files = array();
       /* print_r($_GET);
        ECHO 'AAAA';
        die;*/
        if ($upload && is_array($upload['tmp_name'])) {
            // param_name is an array identifier like "files[]",
            // $_FILES is a multi-dimensional array:
            foreach ($upload['tmp_name'] as $index => $value) {
                $files[] = $this->handle_file_upload(
                    $upload['tmp_name'][$index],
                    $file_name ? $file_name : $upload['name'][$index],
                    $size ? $size : $upload['size'][$index],
					isset($_GET['relativePath'])?dirname($_GET['relativePath']):'',
                    $upload['type'][$index],
                    $upload['error'][$index],
                    $index,
                    $content_range,
					$_GET['bz'],
					$_GET['container']
                );
            }
        } else {
			
            // param_name is a single object identifier like "file",
            // $_FILES is a one-dimensional array:
			
				$files[] = $this->handle_file_upload(
					isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
					$file_name ? $file_name : (isset($upload['name']) ?
							$upload['name'] : null),
					$size ? $size : (isset($upload['size']) ?
							$upload['size'] : $this->get_server_var('CONTENT_LENGTH')),
					'',
					isset($_GET['relativePath'])?dirname($_GET['relativePath']):'',
					isset($upload['type']) ?
							$upload['type'] : $this->get_server_var('CONTENT_TYPE'),
					isset($upload['error']) ? $upload['error'] : null,
					null,
					$content_range,
					$_GET['bz'],
					$_GET['container']
				);
			 
        }
		
        return $this->generate_response(
            array($this->options['param_name'] => $files),
            $print_response
        );
    }

    public function delete($print_response = true) {
        $file_name = $this->get_file_name_param();
        $file_path = $this->get_upload_path($file_name);
        $success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
        if ($success) {
            foreach($this->options['image_versions'] as $version => $options) {
                if (!empty($version)) {
                    $file = $this->get_upload_path($file_name, $version);
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
        }
        return $this->generate_response(array('success' => $success), $print_response);
    }
	
}
?>