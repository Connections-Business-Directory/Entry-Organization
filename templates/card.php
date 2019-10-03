<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var cnOutput $entry
 */

?>
<div class="cn-entry">

	<div class="cn-left" style="width:49%; float:<?php echo is_rtl() ? 'right' : 'left'; ?>">
		<?php

		$entry->getImage(
			array(
				'fallback' => array(
					'type'   => 'none',
					'string' => '',
				),
				'permalink' => FALSE,
			)
		);

		?>
		<div style="clear:both;"></div>
		<div style="margin-bottom: 10px;">
			<div style="font-size:larger;font-variant: small-caps"><strong><?php echo $entry->getNameBlock( array( 'link' => FALSE ) ); ?></strong></div>
			<?php

			$entry->getTitleBlock();
			$entry->getOrgUnitBlock();
			$entry->getContactNameBlock();

			?>
		</div>

	</div>

	<div class="cn-right" style="text-align: right;">

		<?php
		$entry->getAddressBlock();
		$entry->getFamilyMemberBlock();
		$entry->getPhoneNumberBlock();
		$entry->getEmailAddressBlock();
		$entry->getImBlock();
		$entry->getDateBlock();
		$entry->getLinkBlock();
		$entry->getSocialMediaBlock();
		?>

	</div>

	<div style="clear:both"></div>

	<?php

	if ( 0 < strlen( $entry->getBio() ) ) {

		$entry->getBioBlock(
			array(
				'before' => '<h4>' . esc_html__( 'Biographical Info', 'connections' ) . '</h4>' . PHP_EOL,
				'after'  => '<div class="cn-clear"></div>',
			)
		);

	}

	if ( 0 < strlen( $entry->getNotes() ) ) {

		$entry->getNotesBlock(
			array(
				'before' => '<h4>' . esc_html__( 'Notes', 'connections' ) . '</h4>' . PHP_EOL,
				'after'  => '<div class="cn-clear"></div>',
			)
		);

	}

	?>

</div>
