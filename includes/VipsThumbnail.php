<?php
/**
 * PHP wrapper class for VIPS under MediaWiki
 *
 * Copyright © Bryan Tong Minh, 2011
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 * @file
 */

namespace MediaWiki\Extension\VipsScaler;

use MediaWiki\Shell\Shell;

/**
 * A wrapper class around im_conv because that command expects a convolution
 * matrix file as its last argument
 */
class VipsThumbnail extends VipsCommand {

	/** @var string */
	protected $optimize;

	/**
	 * Constructor
	 *
	 * @param string $vips Path to binary
	 * @param array $args Array or arguments
	 * @param string $optimize Optimize arguments
	 */
	public function __construct( $vips, $args, $optimize ) {
		parent::__construct($vips, $args);
		$this->optimize = $optimize;
	}

	/**
	 * @return int
	 */
	public function execute() {
		# Build the command line
		$cmd = [
			$this->vips . 'thumbnail',
			$this->input,
			'-o',
			$this->output . $this->optimize
		];

		$cmd = array_merge( $cmd, $this->args );

		# Execute
		$result = Shell::command( $cmd )
			->environment( [ 'IM_CONCURRENCY' => '1' ] )
			->limits( [ 'filesize' => 409600 ] )
			->includeStderr()
			->execute();

		$this->err = $result->getStdout();
		$retval = $result->getExitCode();

		# Cleanup temp file
		if ( $retval != 0 && file_exists( $this->output ) ) {
			unlink( $this->output );
		}
		if ( $this->removeInput ) {
			unlink( $this->input );
		}

		return $retval;
	}
}
