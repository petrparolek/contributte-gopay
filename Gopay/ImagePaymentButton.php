<?php

/**
 * Gopay Helper with Happy API
 * 
 * @author Vojtech Dobes
 */

namespace Gopay;

use Nette\Forms\Controls\ImageButton;

/**
 * Payment button
 *
 * @property-read   $channel
 */
class ImagePaymentButton extends ImageButton
{
	
	/** @var string */
	private $channel;

	public function __construct($channel, $src = NULL, $alt = NULL)
	{
		parent::__construct($src, $alt);
		$this->channel = $channel;
	}
	
	public function getChannel()
	{
		return $this->channel;
	}


}
