<?php
/**
 * Email Builder
 */
?>
<div id="pgi-email_templates-form" class="pgi-settings-form">
    <form class="parlay-settings-form" action="" method="post">
        <h1 class="pgi-settings-form-header">
            <?php _e( 'Email Templates', 'parlay-api' ); ?>
        </h1>
        <p>
            <label><?php _e( 'Transactional Email', 'parlay-api' ); ?></label>
            <select id="template-select" class="regular-text">
                <option value=""><?php _e( 'Select Template', 'parlay-api' ); ?></option>
                <?php foreach ($email_templates as $id => $template) : ?>
                    <option value="<?php echo esc_attr( $id ); ?>">
                        <?php echo esc_html( $template->title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <div class="wrap-email-template">
            <h3 class="pgi-settings-form-header">
                <?php _e( 'Email Settings', 'parlay-api' ); ?>
            </h3>
            <p>
                <label><?php _e( 'From', 'parlay-api' ); ?></label>
                <input type="email" id="email-from" class="regular-text" placeholder="<?php echo get_option( 'admin_email' ); ?>">
            </p>
            <p>
                <label><?php _e( 'Subject', 'parlay-api' ); ?></label>
                <input type="text" id="email-subject" class="regular-text">
            </p>
            
            <div class="wrap-builder">
                <h3 class="pgi-settings-form-header">
                    <?php _e( 'Email Template Builder', 'parlay-api' ); ?>
                </h3>
                    
                <?php 
                foreach ( $email_templates as $id => $template ) : 
                    echo '<div class="email-tags" data-template-id="' . $id . '">';
                    echo '<label>' . __( 'Template tags (click to copy)', 'parlay-api' ) . ': </label>';
                    foreach ( $template->tags as $tag => $value ) : 
                        echo '<pre class="shortcode" data-clipboard-text="' . $tag . '">'. $tag .'</pre>';
                    endforeach;
                    echo '</div>';
                    
                endforeach; 
                ?>
             
                <div id="pgi-builder" style="height: 700px; border: 1px solid #ccc;"></div>

                <div class="templates-form-actions">
                    <button id="save-email-template" type="button" class="button button-primary">
                        <?php _e( 'Save Template', 'parlay-api' ); ?>
                    </button>
                    <button id="send-test-email" type="button" class="button button-primary">
                        <?php _e( 'Send Test', 'parlay-api' ); ?>
                    </button>
                    
                </div>
                <div id="send-test-form">
                    <input type="email" id="email-to" class="regular-text" placeholder="<?php echo get_option( 'admin_email' ); ?>">
                    <button id="submit-test-email" type="button" class="button button-primary">
                        <?php _e( 'Send', 'parlay-api' ); ?>
                    </button>
                    <button id="cancel-test" type="button" class="button button-primary">
                        <?php _e( 'Cancel', 'parlay-api' ); ?>
                    </button>
                </div>
       
                <?php wp_nonce_field( 'email-template', 'pgi-email-nonce' ); ?>
            </div> <!-- .wrap-builder -->
        </div> <!-- .wrap-email-template -->
    </form>
</div> <!-- #pgi-email_templates-form -->