<nav class="navbar-inverse sidebar">
	<div class="navbar-inner">
	<?php
	use Cake\Cache\Cache;
	use Croogo\Croogo\CroogoNav;

	$cacheKey = 'adminnav_' . $this->Layout->getRoleId() . '_' . $this->request->url . '_' . md5(serialize($this->request->query));
//		$navItems = Cache::read($cacheKey, 'croogo_menus');
//		if ($navItems === false) {
	debug(CroogoNav::items());
			$navItems = $this->Croogo->adminMenus(CroogoNav::items(), array(
				'htmlAttributes' => array(
					'id' => 'sidebar-menu',
				),
			));
//			Cache::write($cacheKey, $navItems, 'croogo_menus');
//		}
		echo $navItems;
	?>
	</div>
</nav>
