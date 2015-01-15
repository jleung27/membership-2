<?php

class MS_View_Membership_Overview extends MS_View {

	public function to_html() {
		$membership = $this->data['membership'];
		if ( empty( $this->data['child_membership'] ) ) {
			$child_membership = $membership;
			$container_class = 'no-tabs';
		} else {
			$child_membership = $this->data['child_membership'];
			$container_class = '';
		}

		$toggle = array(
			'id' => 'ms-toggle-' . $membership->id,
			'type' => MS_Helper_Html::INPUT_TYPE_RADIO_SLIDER,
			'value' => $membership->active,
			'class' => '',
			'data_ms' => array(
				'action' => MS_Controller_Membership::AJAX_ACTION_TOGGLE_MEMBERSHIP,
				'field' => 'active',
				'membership_id' => $membership->id,
			),
		);

		$status_class = '';
		if ( $membership->active ) {
			$status_class = 'ms-active';
		}

		$edit_args = array(
			'membership_id' => $membership->id,
		);

		ob_start();
		// We just want to make sure that the JS files for the editor are loaded
		// so we can use the wp_editor() in the edit popup later
		wp_editor( '', 'not-used' );

		// Discard the editor - at this point the JS files are enqueued already!
		ob_clean();
		?>

		<div class="wrap ms-wrap ms-membership-overview <?php echo esc_attr( $container_class ); ?>">
			<div class="ms-wrap-top ms-group">
				<div class="ms-membership-status-wrapper">
					<?php MS_Helper_Html::html_element( $toggle ); ?>
					<div id="ms-membership-status" class="ms-membership-status <?php echo esc_attr( $status_class ); ?>">
						<?php
							printf(
								'<div class="ms-active"><span>%s </span><span id="ms-membership-status-text" class="ms-ok">%s</span></div>',
								__( 'Membership is', MS_TEXT_DOMAIN ),
								__( 'Active', MS_TEXT_DOMAIN )
							);
						?>
						<?php
							printf(
								'<div><span>%s </span><span id="ms-membership-status-text" class="ms-nok">%s</span></div>',
								__( 'Membership is', MS_TEXT_DOMAIN ),
								__( 'Disabled', MS_TEXT_DOMAIN )
							);
						?>
					</div>
				</div>
				<div class="ms-membership-edit-wrapper">
					<a href="#" class="button" data-ms-dialog="View_Membership_Edit_Dialog" data-ms-data=<?php echo json_encode( $edit_args )?>>
						<i class="wpmui-fa wpmui-fa-pencil handlediv"></i>
						<?php _e( 'Edit', MS_TEXT_DOMAIN ); ?>
					</a>
				</div>
				<?php
				MS_Helper_Html::settings_header(
					array(
						'title' => sprintf( __( '%s Overview', MS_TEXT_DOMAIN ), $membership->name ),
						'desc' => __( 'Here you find a summary of this membership, and alter any of its details.', MS_TEXT_DOMAIN ),
						'title_icon_class' => 'wpmui-fa wpmui-fa-dashboard',
						'bread_crumbs' => $this->data['bread_crumbs'],
					)
				);
				?>
				<div class="clear"></div>
			</div>
			<?php $this->available_content_panel(); ?>
			<div class="clear"></div>
		</div>

		<?php
		$html = ob_get_clean();

		return $html;
	}

	public function news_panel() {
		?>
		<div class="ms-half ms-settings-box ms-fixed-height">
			<?php MS_Helper_Html::html_separator( 'vertical' ); ?>
			<h3><i class="ms-low wpmui-fa wpmui-fa-globe"></i> <?php _e( 'News', MS_TEXT_DOMAIN ); ?></h3>

			<div class="inside group">
				<?php if ( ! empty( $this->data['events'] ) ) : ?>
					<table class="ms-list-table limit-width">
						<thead>
							<tr>
								<th><?php _e( 'Date', MS_TEXT_DOMAIN ); ?></th>
								<th><?php _e( 'User', MS_TEXT_DOMAIN ); ?></th>
								<th><?php _e( 'Event', MS_TEXT_DOMAIN ); ?></th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ( $this->data['events'] as $event ) : ?>
							<tr>
								<td><?php echo esc_html(
									date_i18n( get_option( 'date_format' ), strtotime( $event->post_modified ) )
								); ?></td>
								<td><?php echo esc_html( MS_Model_Member::get_username( $event->user_id ) ); ?></td>
								<td><?php echo esc_html( $event->description ); ?></td>
							</tr>
						<?php endforeach;?>
						</tbody>
					</table>

					<div class="ms-news-view-wrapper">
						<?php
						MS_Helper_Html::html_element(
							array(
								'id' => 'view_news',
								'type' => MS_Helper_Html::TYPE_HTML_LINK,
								'value' => __( 'View More News', MS_TEXT_DOMAIN ),
								'url' => add_query_arg( array( 'step' => MS_Controller_Membership::STEP_NEWS ) ),
								'class' => 'wpmui-field-button button',
							)
						);
						?>
					</div>
				<?php else : ?>
					<p class="ms-italic">
					<?php _e( 'There will be some interesting news here when your site gets going.', MS_TEXT_DOMAIN ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public function members_panel() {
		$count = count( $this->data['members'] );
		?>
		<div class="ms-half ms-settings-box ms-fixed-height">
			<h3><i class="ms-low wpmui-fa wpmui-fa-user"></i> <?php printf( __( 'Members (%s)', MS_TEXT_DOMAIN ), $count ); ?></h3>

			<div class="inside group">
			<?php if ( $count > 0 ) : ?>
				<?php $this->members_panel_data( $this->data['members'] ); ?>

				<div class="ms-member-edit-wrapper">
					<?php
					MS_Helper_Html::html_element(
						array(
							'id' => 'edit_members',
							'type' => MS_Helper_Html::TYPE_HTML_LINK,
							'value' => __( 'Edit Members', MS_TEXT_DOMAIN ),
							'url' => admin_url( 'admin.php?page=' . MS_Controller_Plugin::MENU_SLUG . '-members' ),
							'class' => 'wpmui-field-button button',
						)
					);
					?>
				</div>
			<?php else : ?>
				<p class="ms-italic">
				<?php _e( 'No members yet.', MS_TEXT_DOMAIN ); ?>
				</p>
			<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Echo a member-list. This function can be overwritten by other views
	 * to customize the list (display tier-level or join-date, etc.)
	 *
	 * @since  1.0.0
	 *
	 * @param array $members List of members to display.
	 */
	protected function members_panel_data( $members ) {
		?>
		<div><?php _e( 'Active Members' ); ?></div>
		<ul>
		<?php foreach ( $this->data['members'] as $member ) : ?>
			<li class="ms-overview-member-name">
				<?php echo esc_html( $member->username ); ?>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php
	}

	public function available_content_panel() {
		$child_membership = $this->data['child_membership'];
		$membership = $this->data['membership'];

		$desc = $child_membership->description;
		$desc_empty_class = (empty( $desc ) ? '' : 'hidden');
		$container_class = (empty( $child_membership ) ? 'no-tabs' : '');
		$child_name = '';
		$child_desc = '';

		?>
		<div class="ms-overview-container <?php echo esc_attr( $container_class ); ?>">
			<?php
			if ( ! empty( $child_membership ) ) {
				$child_name = $child_membership->name;
				$child_desc = $child_membership->description;

				MS_Helper_Html::html_admin_vertical_tabs( $this->data['tabs'] );
			} else {
				$child_desc = $membership->description;
			}
			?>
			<div class="ms-settings">
				<div class="ms-overview-top">
					<?php
					if ( ! empty( $child_membership ) ) : ?>
						<div class="ms-subtitle">
							<?php echo esc_html( $child_name ); ?>
						</div>
					<?php endif; ?>

					<div class="ms-settings-desc ms-description membership-description">
						<?php echo $child_desc; ?>
					</div>
					<?php

					MS_Helper_Html::html_separator();
					$this->news_panel();
					$this->members_panel();
					?>
				<div class="clear"></div>
				</div>
				<div class="ms-overview-available-content-wrapper ms-overview-bottom">
					<h3><i class="ms-img-unlock"></i> <?php _e( 'Available Content', MS_TEXT_DOMAIN ); ?></h3>
					<div class="ms-description ms-indented-description">
					<?php printf(
						__( 'This is Protected Content which <span class="ms-bold">%s</span> members has access to.', MS_TEXT_DOMAIN ),
						esc_html( $this->data['membership']->name )
					); ?>
					</div>
					<div class="inside">
						<?php $this->available_content_panel_data(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	protected function available_content_panel_data() {
		$membership = $this->data['membership'];
		$protected_content = MS_Model_Membership::get_protected_content();
		$rule_types = MS_Model_Rule::get_rule_types();

		?>
		<div class="ms-settings ms-group">
			<?php
			foreach ( $rule_types as $rule_type ) {
				$has_rules = false;

				switch ( $rule_type ) {
					case MS_Model_Rule::RULE_TYPE_REPLACE_MENUS:
					case MS_Model_Rule::RULE_TYPE_REPLACE_MENULOCATIONS:
						$rule = $membership->get_rule( $rule_type );
						$has_rules = true;
						break;

					default:
						$rule = $protected_content->get_rule( $rule_type );
						$has_rules = $rule->has_rules();
						break;
				}

				if ( $has_rules ) {
					$this->content_box_tags( $membership->get_rule( $rule_type ), 4 );
				}
			}
			?>
		</div>
		<?php

		MS_Helper_Html::html_element(
			array(
				'id' => 'setup_payment',
				'type' => MS_Helper_Html::TYPE_HTML_LINK,
				'value' => __( 'Payment Options', MS_TEXT_DOMAIN ),
				'url' => add_query_arg(
					array(
						'step' => MS_Controller_Membership::STEP_SETUP_PAYMENT,
						'membership_id' => $this->data['membership']->id,
						'edit' => 1,
					)
				),
				'class' => 'wpmui-field-button button',
			)
		);
	}

	/**
	 * Echo a content list as tag-list.
	 * Used by Simple/Content-Type/Tier views.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $contents List of content items to display.
	 */
	protected function content_box_tags( $rule, $items_per_row = 3 ) {
		static $row_items = 0;

		$rule_titles = MS_Model_Rule::get_rule_type_titles();
		$title = $rule_titles[ $rule->rule_type ];
		$contents = $rule->get_contents( array( 'protected_content' => 1 ) );

		$membership_id = $this->data['membership']->id;

		if ( ! empty( $this->data['child_membership'] ) && $this->data['child_membership']->is_valid() ) {
			$membership_id = $this->data['child_membership']->id;
		}

		$row_items += 1;
		$new_row = ($row_items % $items_per_row === 0);
		$show_sep = (($row_items - 1) % $items_per_row === 0);

		if ( $show_sep && $row_items > 1 ) {
			MS_Helper_Html::html_separator();
		}
		?>
		<div class="ms-part-<?php echo esc_attr( $items_per_row ); ?> ms-min-height">
			<?php if ( ! $new_row ) { MS_Helper_Html::html_separator( 'vertical' ); } ?>
			<div class="ms-bold">
				<?php printf( '%s (%s):', $title, $rule->count_rules() ); ?>
			</div>

			<div class="inside">
				<ul class="ms-content-tag-list ms-group">
				<?php
				foreach ( $contents as $content ) {
					if ( $content->access ) {
						MS_Helper_Html::content_tag( $content );
					}
				}
				?>
				</ul>

				<div class="ms-protection-edit-wrapper">
					<?php
						MS_Helper_Html::html_element(
							array(
								'id' => 'edit_' . $rule->rule_type,
								'type' => MS_Helper_Html::TYPE_HTML_LINK,
								'title' => $title,
								'value' => sprintf( __( 'Edit %s Access', MS_TEXT_DOMAIN ), $title ),
								'url' => add_query_arg(
									array(
										'step' => MS_Controller_Membership::STEP_ACCESSIBLE_CONTENT,
										'tab' => $rule->rule_type,
										'membership_id' => $membership_id,
										'edit' => 1,
									)
								),
								'class' => 'wpmui-field-button button',
							)
						);
					?>
				</div>
			</div>
		</div>
		<?php
		if ( $new_row ) {
			echo '</div><div class="ms-group">';
		}
	}

	/**
	 * Echo a content list as 2-column table that show Content-Title and the
	 * Available date.
	 * Used by Dripped-Content view.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $contents List of content items to display.
	 */
	protected function content_box_date( $contents ) {
		?>
		<table class="ms-list-table limit-width ms-list-date">
			<thead>
				<tr>
					<th class="col-text"><?php _e( 'Post / Page Title', MS_TEXT_DOMAIN ); ?></th>
					<th class="col-date"><?php _e( 'Content Available', MS_TEXT_DOMAIN ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $contents as $id => $content ) : ?>
				<tr>
					<td class="col-text"><?php MS_Helper_Html::content_tag( $content, 'span' ); ?></td>
					<td class="col-date"><?php echo esc_html(
						date_i18n( get_option( 'date_format' ), strtotime( $content->avail_date ) )
					); ?></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
		<?php
	}
}