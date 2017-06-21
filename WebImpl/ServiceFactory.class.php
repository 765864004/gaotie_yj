<?php
/**
 * 业务服务工厂类--为外部提供服务
 */
final class ServiceFactory
{
	public static function getAdminUserService()
	{
		import ( "WebImpl.Admin.AdminServiceImpl", WEBSITE_DISK_PATH );
		return new AdminServiceImpl ();
	}
}
?>