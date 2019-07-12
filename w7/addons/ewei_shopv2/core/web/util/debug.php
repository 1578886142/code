<?php

if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Debug_EweiShopV2Page extends WebPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		$orderid = intval($_GPC['orderid']);
		dump(p('commission')->calculate($orderid));
	}
}


?>