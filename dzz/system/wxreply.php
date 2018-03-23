<?php
/*
 * @copyright   Leyun internet Technology(Shanghai)Co.,Ltd
 * @license     http://www.dzzoffice.com/licenses/license.txt
 * @package     DzzOffice
 * @link        http://www.dzzoffice.com
 * @author      zyx(zyx@dzz.cc)
 */

$weObj=new qyWechat(array('token'=>getglobal('setting/token_0'),'appid'=>getglobal('setting/CorpID'),'appsecret'=>getglobal('setting/CorpSecret'),'agentid'=>0,'encodingaeskey'=>getglobal('setting/encodingaeskey_0'),'debug'=>false));

$weObj->valid(); //注意, 企业号与普通公众号不同，必须打开验证，不要注释掉
$type = $weObj->getRev()->getRevType();
switch($type) {
    case qyWechat::MSGTYPE_TEXT://文本消息
            /*$weObj->text($weObj->getRev()->getRevContent())->reply();*/
            break;
	case qyWechat::MSGTYPE_IMAGE://图片消息
			/*$imageinfo=$weObj->getRev()->getRevPic();
	 		$weObj->image($imageinfo['mediaid'])->reply();*/
			break;
	case qyWechat::MSGTYPE_VOICE://语音消息
			/*$imageinfo=$weObj->getRev()->getRevPic();
	 		$weObj->image($imageinfo['mediaid'])->reply();*/
			break;
	case qyWechat::MSGTYPE_VIDEO://视频消息
			/*$voice=$weObj->getRev()->getRevVoice();
	 		$weObj->image($voice['mediaid'])->reply();*/
			break;
	case qyWechat::MSGTYPE_LOCATION://地理位置消息
			//$location=$weObj->getRev()->getRevGeo();
	 		//$weObj->text('X:'.$location['x'].'Y:'.$location['y'].'label:'.$location['label'])->reply();
			break;
				
    case qyWechat::MSGTYPE_EVENT:
			$data=$weObj->getRev()->getRevData();//{"ToUserName":"wx735f8743226a8656","FromUserName":"dzz-1","CreateTime":"1413865073","MsgType":"event","AgentID":"0","Event":"unsubscribe","EventKey":{}
			if($data['Event']=='unsubscribe'){
				DB::update('user',array('wechat_status'=>4),"wechat_userid='{$data[FromUserName]}'");
			}elseif($data['Event']=='subscribe'){
				DB::update('user',array('wechat_status'=>1),"wechat_userid='{$data[FromUserName]}'");
				//发送关注成功消息
				 $weObj->text($_G['setting']['sitename'].lang('news_platform_send_here'))->reply();
			}elseif($data['Event']=='view'){
				
			}
            break;
    
    default:
           /* $weObj->text("help info")->reply();*/
}
exit();
?>