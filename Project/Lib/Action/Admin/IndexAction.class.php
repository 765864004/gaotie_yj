<?php
import ('WebImpl.ServiceFactory', WEBSITE_DISK_PATH);
class IndexAction extends Action
{
	private $_adminService;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_adminService = ServiceFactory::getAdminUserService();
	}
	 
	/**
	 * 后台用户登录
	 * */
	public function showAdminLogin()
	{
		if(!$_SESSION['manager'])
		{
			$this->display('Index:showAdminLogin');
		}
		else 
		{
			$this->redirect('Index/adminDisplay');
		}
	}
	 
	/**
	 * 后台用户登录检测
	 * */
	public function ajaxCheckAdminLogin()
	{
		$username = $_REQUEST['account'];
		$password = $_REQUEST['password'];
		$verify = $_REQUEST['verify'];

		//if ($_SESSION['verify'] != $_SESSION['verify'])
		if (md5($verify) != $_SESSION['verify'])
		{
			$msg = "验证码错误,请重新输入";
			$status = 0;
		}
		else
		{
			try
			{
				$this->_adminService->login($username, $password);
				$status = 1;
			}catch (Exception $e)
			{
				$msg = "用户名或密码错误，请重试";
				$msg = $e->getMessage();
				$status = 0;
			}
		}
		$this->ajaxReturn($msg, "", $status);
	}
	
	/**
	 * 用户退出
	 * */
	public function checkUserLogout()
	{
		$this->checkUserSession();
		
		unset($_SESSION['manager']);
		session_destroy();
		$this->redirect('Admin/Index/showAdminLogin');
		exit;
	}
	
	 
	/**
	 * 显示验证码
	 * */
	public function verify()
	{
		// 生成验证码
 		import("ORG.Util.Image");
 		Image::buildImageVerify();
	}
	
	/**
	 * 首先显示后台的页面，这个页面包括顶部导航栏、左侧导航栏、右侧内容页的方法
	 * */
	public function adminDisplay()
	{
		$this->checkUserSession();
	   	
		$this->assignNavMenu();
	   	$this->assignSidebar();   
	   
	  	$this->display("Index:adminDisplay");
	}
	
	/**
	 * 动态的得到导航条的问题
	 * Top表示是顶部，Nav表示是导航条
	 * */
	public function assignNavMenu()
	{
		$this->checkUserSession();

		$result = $this->_adminService->returnGroupList();
		
		//dump($result);
	  	    
		$navMenu  =$result;//$TopNavItemList是顶部导航栏的项目列表
	  	
		$this->assign('navMenu',$navMenu);    
	 }
	 
	
	/**
	 * 动态的得到左侧的导航栏
	 * */
	public function assignSidebar()
	{
	  	    
		$this->checkUserSession();

    	$groupId   = $_GET['groupId'];
    	
    	$ActionList = $this->_adminService->returnActionList($groupId);

		$sidebar     = $ActionList;
		

		$this->assign('sidebar',$sidebar);
	}
	
	/**
	 * 改变左侧的导航栏，一个导航栏默认显示一个分组的方法列表
	 * */
	public function changeSidebar()
	{

		$this->checkUserSession();
	  		
		$this->assignSidebar();
		
		$this->display("Index:sidebar");   
	}
	
	/**
	 * 检测用户是否登录
	 * */
	private function checkUserSession()
	{
		if((!$_SESSION['manager']['id']) || (!$_SESSION['manager']['roleId']))
		{
			$this->redirect("Admin/index/showAdminLogin");
		}
	}
}

?>