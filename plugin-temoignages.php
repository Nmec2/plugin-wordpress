<?php
/*
Plugin Name: Témoignages Clients
Description: Un plugin pour gérer et afficher des témoignages clients.
Version: 1.0
Author: ARMATOL Ilias
Author URI: /
*/

function registerTestimonials(){
    $args = array(
        'labels' => array(
            'name' => 'Témoignage',
            'singular_name' => 'Témoignage',
            'menu_name' => 'Témoignages',
            'all_items' => 'Tous les témoignages',
            'add_new' => 'Ajouter un nouveau témoignage',
            'add_new_item' => 'Ajouter un nouveau témoignage',
            'new_item' => 'Nouveau témoignage',
            'edit_item' => 'Modifier témoignage',
            'view_item' => 'Voir les témoignages',
            'not_found' => 'Aucun témoignage trouvée',
            'not_found_in_trash' => 'Aucun témoignage trouvée dans la corbeille'
        ),
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'menu_position' => 10,
        'menu_icon' => 'dashicons-testimonial'
    );
    register_post_type('testimonials', $args);
}

add_filter('manage_testimonials_posts_columns', 'customs_testimonials_columns');
function customs_testimonials_columns($columns) {
    return array(
        'cb' => '<input type="checkbox" />',
        'title' => 'Titre',
        'content' => 'Contenu',
        'thumbnail' => 'Image',
    );
}

add_action('manage_testimonials_posts_custom_column', 'customs_testimonials_column_content', 10, 2);
function customs_testimonials_column_content($column, $post_id) {
    if($column == 'content'){
        $content = get_post_field('post_content', $post_id);
        echo wp_trim_words($content, 15);
    }
    if($column == 'thumbnail'){
        echo get_the_post_thumbnail($post_id, array(60, 60));
    }
}



function shortCodeAdd( $atts ){

    extract(shortcode_atts(array(
        'number' => 3
    ), $atts));

    $number = get_option('temoignages_nombre', 3);

    $args = array(
        'post_type' => 'testimonials',
        'posts_per_page' => $number,
        'order' => 'DESC',
        'orderby' => 'meta-value',
        'meta-key' => 'note',
    );

    
    $couleur = get_option('temoignages_couleur', '#000000');
    $background_couleur = get_option('temoignages_background_couleur', '#5e5d5d');
    $borderRadiusImg = get_option('temoignages_border_radius', '0');
    $borderRadiusCard = get_option('temoignages_border_card', '0');

    $my_query = new WP_Query( $args );

    ob_start();

    if( $my_query->have_posts() ) {
        while ( $my_query->have_posts() ){
            $my_query->the_post();
            echo "<div class='temoignage' style='color: {$couleur}; background: linear-gradient(to bottom, {$background_couleur}, transparent); border-radius: {$borderRadiusCard}px;'>";
            the_post_thumbnail('thumbnail', array('style' => 'border-radius: ' . $borderRadiusImg . 'px' ));
            the_title(); 
            echo '<br>';
            the_field('poste');
            echo '<br>';
            the_field('note');
            echo '<br>';
            echo '<div class="content">';
            the_content();
            echo '</div>';
            echo '</div>';
        }
    }
    $html = ob_get_contents();
    ob_end_clean();

    wp_reset_postdata();
    return $html;
}


add_action('init', 'registerTestimonials');
// add_action('save_post', 'testimonials');
add_shortcode('temoignages', 'shortCodeAdd');

// add_shortcode()

add_action('admin_menu', 'temoignages_plugin_menu');

function temoignages_plugin_menu(){
    add_submenu_page(
        'edit.php?post_type=testimonials',
        'Réglages Témoignages',
        'Réglages',
        'manage_options',
        'temoignages-settings',
        'temoignages_settings_page'
    );
}

function temoignages_settings_page(){
    if (isset($_POST['temoignages_submit'])) {
        update_option('temoignages_nombre', sanitize_text_field($_POST['temoignages_nombre']));
        update_option('temoignages_couleur', sanitize_hex_color($_POST['temoignages_couleur']));
        update_option('temoignages_background_couleur', sanitize_hex_color($_POST['temoignages_background_couleur']));
        update_option('temoignages_border_radius', sanitize_text_field($_POST['temoignages_border_radius']));
        update_option('temoignages_border_card', sanitize_text_field($_POST['temoignages_border_card']));
        echo '<div class="updated"><p>Réglages enregistrés</p></div>';
    }

    $nombre = get_option('temoignages_nombre', 3);
    $couleur = get_option('temoignages_couleur', '#000000');
    $background_couleur = get_option('temoignages_background_couleur', '#5e5d5d');
    $borderRadiusImg = get_option('temoignages_border_radius', '0');
    $borderRadiusCard = get_option('temoignages_border_card', '0');

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Réglages témoignages</h1>
        <hr>
        <form method="POST">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">Nombre de témoignages à afficher</th>
                        <td><input type="number" name="temoignages_nombre" value="<?php echo esc_attr($nombre); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row">Couleur du texte</th>
                        <td><input type="color" name="temoignages_couleur" value="<?php echo esc_attr($couleur); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row">Couleur du background</th>
                        <td><input type="color" name="temoignages_background_couleur" value="<?php echo esc_attr($background_couleur); ?>"></td>
                    </tr>
                    <tr>
                        <th scope="row">Arrondis de la carte de témoignage</th>
                        <td><input type="number" name="temoignages_border_card" value="<?php echo esc_attr($borderRadiusCard); ?>"> px</td>
                    </tr>
                    <tr>
                        <th scope="row">Arrondis de l'image de profile</th>
                        <td><input type="number" name="temoignages_border_radius" value="<?php echo esc_attr($borderRadiusImg); ?>"> px</td>
                    </tr>
                </tbody>
            </table>
            <p><input type="submit" name="temoignages_submit" class="button-primary" value="Enregistrer"></p>
        </form>
    </div>
    <?php
}
