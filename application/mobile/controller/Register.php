<?php
namespace app\mobile\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use app\mobile\model\Users;
use think\captcha\Captcha;
use think\Cookie;
use app\mobile\model\Transactions;
use app\mobile\model\Message;

class Register extends Controller
{
    public function index()
    {
		//开启SESSION
		session_start();
		//生成注册页面随机数作为token
		$register_token = random(6,1);
		$_SESSION['register_token'] = $register_token;
		
        $this->assign('header_type','register');
        $this->assign('register_token',$register_token);
        return $this->fetch();
    }
	
	public function chkUsername(){
		$username = Request::instance()->post('username');
		$map['username']=$username;
		//实例化模型后调用查询
		$user = new Users();
		// 查询单个数据
		return $user->chkUsername($username);
	}

	public function chkMobile(){
		$mobile = Request::instance()->post('mobile');
		$map['mobile']=$mobile;
		//实例化模型后调用查询
		$user = new Users();
		// 查询单个数据
		return $user->chkMobile($mobile);
	}
	
	public function chkImgcode(){
		$id = "";
		$imgcode = Request::instance()->post('imgcode');
		$captcha = new Captcha();
		$captcha->reset = false;
		if(!$captcha->check($imgcode, $id)){
			return "error";
		}else{
			return "ok";
		}
	}

	public function sendSms()
	{
		//接口类型：互亿无线触发短信接口，支持发送验证码短信、订单通知短信等。
		// 账户注册：请通过该地址开通账户http://sms.ihuyi.com/register.html
		// 注意事项：
		//（1）调试期间，请用默认的模板进行测试，默认模板详见接口文档；
		//（2）请使用 用户名(例如：cf_demo123)及 APIkey来调用接口，APIkey在会员中心可以获取；
		//（3）该代码仅供接入互亿无线短信接口参考使用，客户可根据实际需要自行编写；
		
		//开启SESSION
		session_start();
		
		header("Content-type:text/html; charset=UTF-8");
		
		//请求数据到短信接口，检查环境是否 开启 curl init。
		function Post($curlPost,$url){
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_NOBODY, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
			$return_str = curl_exec($curl);
			curl_close($curl);
			return $return_str;
		}
		
		//将 xml数据转换为数组格式。
		function xml_to_array($xml){
		$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches)){
			$count = count($matches[0]);
			for($i = 0; $i < $count; $i++){
			$subxml= $matches[2][$i];
			$key = $matches[1][$i];
				if(preg_match( $reg, $subxml )){
					$arr[$key] = xml_to_array( $subxml );
				}else{
					$arr[$key] = $subxml;
				}
			}
		}
		return $arr;
		}
		
		//短信接口地址
		$target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
		//获取手机号
		$mobile = Request::instance()->post('mobile');
		//获取token
		$register_token = Request::instance()->post('register_token');
		//生成的随机数
		$mobile_code = random(4,1);
		if(empty($mobile)){
		exit('手机号码不能为空');
		}
		//防用户恶意请求
		if(empty($_SESSION['register_token']) or $register_token != $_SESSION['register_token']){
			exit('请求超时，请刷新页面后重试');
		}
		
		$post_data = "account=C28970837&password=3a6891f7985c572c366d4d336765ab14&mobile=".$mobile."&content=".rawurlencode("您的验证码是：".$mobile_code."。请不要把验证码泄露给其他人。");
		//用户名是登录ihuyi.com账号名（例如：cf_demo123）
		//查看密码请登录用户中心->验证码、通知短信->帐户及签名设置->APIKEY
		$gets =  xml_to_array(Post($post_data, $target));
		if($gets['SubmitResult']['code']==2){
		$_SESSION['mobile'] = $mobile;
		$_SESSION['mobile_code'] = $mobile_code;
		}
		return $gets['SubmitResult']['msg'];
	}
	
	public function chkSmscode(){
		session_start();
		$smscode = Request::instance()->post('smscode');
		$mobile = Request::instance()->post('mobile');
		if($_SESSION['mobile_code'] && $_SESSION['mobile']){
			if($smscode == $_SESSION['mobile_code'] && $mobile == $_SESSION['mobile']){
				return "ok";
			}else{
				return "error";
			}
		}else{
			return "error";
		}
	}
	
	public function getRegister(){
		session_start();
		$username = Request::instance()->post('username');
		$recommend = Request::instance()->post('recommend');
		$mobile = Request::instance()->post('mobile');
		$password = Request::instance()->post('pwd');
		$password = password_hash($password,PASSWORD_BCRYPT);
		$imgcode = Request::instance()->post('imgcode');
		$register_token = Request::instance()->post('register_token');
		if(empty($_SESSION['register_token']) or $register_token != $_SESSION['register_token']){
			return '请求超时，请刷新页面后重试';
		}
		$id = "";
		$captcha = new Captcha();
		$captcha->reset = false;
		if(!$captcha->check($imgcode, $id)){
			return "验证码错误或者请求超时";
		}
		if($username!="" && $mobile!="" && $password!="" && $register_token!=""){
			$user = new Users;
			$result = $user->userRegister($username, $password, $mobile);
			if($result == "ok"){
			//if(true){
				Session::clear();
				Cookie::delete('userid');
				Cookie::delete('mobile');
				Cookie::delete('username');
				//session_start();
				$_SESSION['mobile_user_id'] = $mobile;
				$_SESSION['mobile_password'] = Request::instance()->post('pwd');

				if($recommend != ""){
					$user = new Users();
					$user->getLogin($mobile, Request::instance()->post('pwd'), "yes");


					$target = new Users;
					$target_userid = $target->getUserIdByUsername($recommend);
					if($target_userid != false){

						$user = new Users;
						$user->chkReminder(Cookie::get('userid'));
						$user_result = $user->updateRecommend(Cookie::get('userid'),$recommend);


						$transaction = new Transactions;
						$result_trans = $transaction->saveTransaction(Cookie::get('userid'), 50, 16);
						$message_text = "感谢您参与\"推荐好友获得额外比邻币\"的活动，赠送您25个比邻币，也欢迎您邀请您的好友加入比邻小镇！";
						$message = new Message;
						$message_result = $message->saveNewMessage(Cookie::get('userid'), $message_text);


						$transaction = new Transactions;
						$result_trans = $transaction->saveTransaction($target_userid, 50, 16);

						$message_text = "您的好友".$username."已经通过您的邀请加入比邻小镇，感谢您的邀请，额外赠送您25个比邻币，也欢迎您继续邀请您的好友";
						$message = new Message;
						$message_result = $message->saveNewMessage($target_userid, $message_text);

					}
				}

				return "ok";
			}else{
				return "注册失败";
			}
		}else{
			return "数据有误，请检查后重试";
		}
	}
}