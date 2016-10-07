<?php

namespace threewp_broadcast\maintenance\checks\view_post_info;

use \threewp_broadcast\BroadcastData;

/**
	@brief		View information about a post.
	@since		2016-04-01 22:17:58
**/
class check
	extends \threewp_broadcast\maintenance\checks\check
{
	public function get_description()
	{
		return 'View post information including metadata.';
	}

	public function get_name()
	{
		return 'View post info';
	}

	public function step_start()
	{
		$o = new \stdClass;
		$o->inputs = new \stdClass;
		$o->form = $this->broadcast()->form2();
		$o->r = ThreeWP_Broadcast()->p( 'Use the form below to look up the broadcast data (linking) either by specifying the ID of the row in the database or the combination of blog ID and post ID. Leave the row input empty to look up using blog and post IDs.' );

		$o->inputs->post_id = $o->form->number( 'post_id' )
			->description( 'The ID of the post to view' )
			->label( 'Post ID' )
			->value( 0 );

		$button = $o->form->primary_button( 'dump' )
			->value( 'Find and display the post info' );

		if ( $o->form->is_posting() )
		{
			$o->form->post()->use_post_value();
			$this->view_post_info( $o );
		}

		$o->r .= $o->form->open_tag();
		$o->r .= $o->form->display_form_table();
		$o->r .= $o->form->close_tag();
		return $o->r;
	}

	public function view_post_info( $o )
	{
		$post_id = $o->inputs->post_id->get_value();

		$post = get_post( $post_id );

		if ( ! $post )
		{
			$o->r .= $this->broadcast()->message( sprintf( 'Post %s does not exist.', $post_id ) );
			return;
		}

		$text = sprintf( '<pre>%s</pre>', var_export( $post, true ) );
		$o->r .= $this->broadcast()->message( $text );

		$metas = get_post_meta( $post_id );
		foreach( $metas as $key => $value )
		{
			$value = reset( $value );
			$value = maybe_unserialize( $value );
			$metas [ $key ] = $value;
		}

		$text = sprintf( '<pre>%s</pre>', var_export( $metas, true ) );
		$o->r .= $this->broadcast()->message( $text );
	}
}
