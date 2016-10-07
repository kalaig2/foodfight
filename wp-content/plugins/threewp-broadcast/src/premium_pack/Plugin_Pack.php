<?php

namespace threewp_broadcast\premium_pack;

/**
	@brief		A parent class for all official Broadcast plugin packs (Premium, 3rd Party, Control, Efficiency and Utilities).
	@details	Saves me from repeating myself regarding the construction and uninstall, among other things.
	@since		2015-10-29 12:17:13
**/
abstract class Plugin_Pack
	extends \plainview\sdk_broadcast\wordpress\base
{
	use \plainview\sdk_broadcast\wordpress\updater\edd;

	public function _construct()
	{
		$this->add_action( 'ThreeWP_Broadcast_Plugin_Pack_get_plugin_classes' );
		$this->add_action( 'threewp_broadcast_plugin_pack_uninstall' );
		$this->add_action( 'threewp_broadcast_plugin_pack_tabs' );
		$this->edd_init();
	}

	// --------------------------------------------------------------------------------------------
	// ----------------------------------------- EDD Updater
	// --------------------------------------------------------------------------------------------

	/**
		@brief		Show some text about the SSL workaround.
		@since		2016-04-14 14:01:01
	**/
	public function edd_admin_license_tab_text()
	{
		return $this->p( "If the pack is not activating as it should due to an SSL error, add this to your wp-config.php file: <code>define( 'BROADCAST_PP_SSL_WORKAROUND', true );</code>" );
	}

	/**
		@brief		edd_enable_ssl_workaround
		@since		2016-04-14 12:24:30
	**/
	public function edd_enable_ssl_workaround()
	{
		return defined( 'BROADCAST_PP_SSL_WORKAROUND' );
	}

	public abstract function edd_get_item_name();

	/**
		@brief		All official BC plugin packs have one EDD url.
		@since		2015-10-29 12:18:23
	**/
	public function edd_get_url()
	{
		return ThreeWP_Broadcast()->plugin_pack()->edd_get_url();
	}

	// --------------------------------------------------------------------------------------------
	// ----------------------------------------- Misc functions
	// --------------------------------------------------------------------------------------------

	public abstract function get_plugin_classes();

	/**
		@brief		Return an array of our site options.
		@since		2014-09-27 16:35:34
	**/
	public function site_options()
	{
		return array_merge( [
			'edd_updater_license_key' => '',
		], parent::site_options() );
	}

	/**
		@brief		Show our license in the tabs.
		@since		2015-10-28 15:10:14
	**/
	public abstract function threewp_broadcast_plugin_pack_tabs( $action );

	/**
		@brief		Put all of our plugins in the list.
		@since		2015-01-06 09:54:47
	**/
	public function ThreeWP_Broadcast_Plugin_Pack_get_plugin_classes( $action )
	{
		$action->add( $this->get_plugin_classes() );
	}

	/**
		@brief		Uninstall ourself.
		@since		2015-10-28 23:21:26
	**/
	public function ThreeWP_Broadcast_Plugin_Pack_uninstall( $action )
	{
		$this->uninstall_internal();
		$this->deactivate_me();
	}
}
