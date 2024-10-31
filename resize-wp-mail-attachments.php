<?php
/**
 * Plugin Name: Resize WP Mail Attachments
 * Description: Resizes image attachments being sent to fit within the PostMark 10mb limit. Should actually work with any wp_mail() implementation.
 * Version:     1.2
 * Author:      Patabugen
 * Author URI:  https://www.mha.systems
 * License:     GPL2
 */


/**
 * Takes an array of file names and a maximum size and reduces any files it can handle
 * (currently only images) until the total size is less than the given size.
 *
 * The function has an attempt_limit and a reduction_amount - the higher each number the
 * higher your server load/load time but the better the quanity of the end result. Lower
 * each number for faster processing time - but potentially doing more reduction than
 * is essential.
 *
 * @param array   $attachments An array of tile names.
 * @param integer $mb_limit    The maximum size - in megabytes.
 * @param integer $attempt     The resize attempt. The default limit is 5 attempts after
 *                             which the function will stop trying to avoid infinite loops.
 *                             You can override the attempt limit with the resize_wp_mail_attachments_fit_max_attempts
 *                             filter.
 *
 * @return array Returns a list of file names - changing only those where the files have been modified.
 */
function resize_wp_mail_attachments_to_size_limit( $attachments, $mb_limit = 10, $attempt = 1 ) {
	// Add a simple filter to let people override the mb_limit.
	$mb_limit = apply_filters( 'resize_wp_mail_attachments_max_total_size', $mb_limit );
	// How many times to try to reduce.
	$attempt_limit = apply_filters( 'resize_wp_mail_attachments_fit_max_attempts', 10 );
	// How much to reduce by (as a % of the width/height).
	$reduction_amount = apply_filters( 'resize_wp_mail_attachments_fit_reduction_amount', 0.98 );
	// Don't keep trying forever.
	if ( $attempt > $attempt_limit ) {
		return $attachments;
	}

	// Turn the "mb" limit into bytes.
	$byte_limit = $mb_limit * 1000000;

	// Aim for 5% less than 10mb to play it safe.
	$byte_limit = $byte_limit * 0.95;

	// Work out the total size of the given attachments.
	$total_size = 0;
	foreach ( $attachments as $attachment ) {
		$total_size += filesize( $attachment );
	}

	// If we're within the limit - don't do anything.
	if ( $total_size < $byte_limit ) {
		return $attachments;
	}

	// If we're too big - reduce each image by a small amount.
	$resized_attachments = [];
	foreach ( $attachments as $attachment ) {
		$image_editor = wp_get_image_editor( $attachment );
		if ( is_wp_error( $image_editor ) ) {
			// We can't resize this kind of file - so do nothing.
			$resized_attachments[] = $attachment;
		} else {
			// Get a temporary file in which to save our resized image.
			$temp_filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename( $attachment );
			if ( file_exists( $temp_filename ) ) {
				// If that temp file already exists, add a random number. This is
				// jsut a basic check to try to not overwrite other files.
				$temp_filename = get_temp_dir() . rand( 100, 999 ) . '-' . basename( $attachment );
			}
			// Get the current size - we'll just resize to a little less than it.
			$image_size = $image_editor->get_size();
			// 98% seems to work well. I think any resize also reduces quality and
			// has a huge file-size different.
			$image_editor->resize(
				round( $image_size['width'] * 0.98 ),
				round( $image_size['height'] * 0.98 ),
				false
			);
			$image_editor->save( $temp_filename );
			$resized_attachments[] = $temp_filename;
		}
	}

	/** Recalculate the sizes of the new files*/
	$total_size = 0;
	foreach ( $resized_attachments as $attachment ) {
		$total_size += filesize( $attachment );
	}
	// If we're now within the limits - return.
	if ( $total_size < $byte_limit ) {
		return $resized_attachments;
	} else {
		// If we're still too big - go for round 2 and reduce by another step.
		return resize_wp_mail_attachments_to_size_limit( $resized_attachments, $mb_limit, $attempt++ );
	}
}

/**
 * Simple filter function to pass the 'attachment' field into the resizig function.
 *
 * @param array $args Array of args passed to wp_mail.
 *  
 * @return array
 */
function resize_wp_mail_attachments_filter( $args ) {
	if ( isset( $args['attachments'] ) ) {
		$args['attachments'] = resize_wp_mail_attachments_to_size_limit( $args['attachments'] );
	}
	return $args;
}
add_filter( 'wp_mail', 'resize_wp_mail_attachments_filter' );
